<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\AssessmentSchema;
use RuntimeException;

final class OpenAIClient
{
    private const API_URL = 'https://api.openai.com/v1/responses';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'gpt-5.2',
        private readonly int $timeoutSeconds = 45,
        private readonly int $retries = 2
    ) {
    }

    /**
     * @param array<int, array<string,mixed>> $uploads
     * @return array<string,mixed>
     */
    public function analyze(string $roomType, array $uploads): array
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY ontbreekt.');
        }

        $content = [[
            'type' => 'input_text',
            'text' => $this->buildUserInstruction($roomType),
        ]];

        foreach ($uploads as $upload) {
            $path = (string) ($upload['path'] ?? '');
            $mime = (string) ($upload['mime_type'] ?? 'image/jpeg');

            if (!is_file($path)) {
                continue;
            }

            $base64 = base64_encode((string) file_get_contents($path));
            $content[] = [
                'type' => 'input_image',
                'image_url' => "data:{$mime};base64,{$base64}",
            ];
        }

        $payload = [
            'model' => $this->model,
            'input' => [
                [
                    'role' => 'system',
                    'content' => [[
                        'type' => 'input_text',
                        'text' => $this->systemPrompt(),
                    ]],
                ],
                [
                    'role' => 'user',
                    'content' => $content,
                ],
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'assessment_result',
                    'strict' => true,
                    'schema' => AssessmentSchema::jsonSchema(),
                ],
            ],
        ];

        $attempt = 0;
        $maxAttempts = max(1, $this->retries + 1);

        while ($attempt < $maxAttempts) {
            try {
                $response = $this->post($payload);
                return $this->parseStructuredResult($response);
            } catch (RuntimeException $error) {
                $attempt++;

                if ($attempt >= $maxAttempts) {
                    error_log('OpenAI analyze failed after retries: ' . $error->getMessage());
                    throw $error;
                }

                usleep((int) (200000 * (2 ** ($attempt - 1))));
            }
        }

        throw new RuntimeException('Onbekende analysefout.');
    }

    /** @param array<string,mixed> $payload */
    private function post(array $payload): array
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('Kon OpenAI payload niet serialiseren.');
        }

        $ch = curl_init(self::API_URL);

        if ($ch === false) {
            throw new RuntimeException('Kon cURL niet initialiseren.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
        ]);

        $raw = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('OpenAI request mislukt: ' . $curlError);
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('OpenAI gaf geen geldige JSON terug.');
        }

        if ($httpCode >= 400) {
            $errorMessage = (string) ($decoded['error']['message'] ?? 'OpenAI request faalde.');
            throw new RuntimeException($errorMessage);
        }

        return $decoded;
    }

    /** @param array<string,mixed> $response */
    private function parseStructuredResult(array $response): array
    {
        if (isset($response['output_parsed']) && is_array($response['output_parsed'])) {
            return $response['output_parsed'];
        }

        if (isset($response['output_text']) && is_string($response['output_text'])) {
            $decoded = json_decode($response['output_text'], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $jsonString = $this->extractTextFromOutput($response);
        $decoded = json_decode($jsonString, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('AI-output bevatte geen valide JSON.');
        }

        return $decoded;
    }

    /** @param array<string,mixed> $response */
    private function extractTextFromOutput(array $response): string
    {
        $output = $response['output'] ?? null;

        if (!is_array($output)) {
            throw new RuntimeException('Lege AI-response ontvangen.');
        }

        foreach ($output as $item) {
            if (!is_array($item) || !isset($item['content']) || !is_array($item['content'])) {
                continue;
            }

            foreach ($item['content'] as $content) {
                if (is_array($content) && isset($content['text']) && is_string($content['text'])) {
                    return $content['text'];
                }
            }
        }

        throw new RuntimeException('Lege AI-response ontvangen.');
    }

    private function systemPrompt(): string
    {
        return implode(' ', [
            'Je bent een expert in woonveiligheid voor ouderen.',
            'Focus op valrisico en concrete, praktische verbeteringen.',
            'Geef geen medische diagnoses of medische claims.',
            'Als iets niet duidelijk zichtbaar is, voeg een vraag toe in missing_info_questions.',
            'Output moet uitsluitend geldige JSON zijn volgens het schema. Nooit extra tekst buiten JSON.',
        ]);
    }

    private function buildUserInstruction(string $roomType): string
    {
        $label = match ($roomType) {
            'bathroom' => 'badkamer',
            'stairs_hall' => 'trap of hal',
            'living_room' => 'woonkamer',
            'bedroom' => 'slaapkamer',
            'kitchen' => 'keuken',
            default => 'ruimte',
        };

        return implode(' ', [
            "Analyseer deze foto's van een {$label} in huis.",
            'Doel: valpreventie en seniorvriendelijke aanpassingen.',
            'Gebruik duidelijke Nederlandse formuleringen.',
            'Als confidence laag is of severity hoog, zet needs_human_followup op true.',
            'Belangrijk: retourneer uitsluitend JSON.',
        ]);
    }
}
