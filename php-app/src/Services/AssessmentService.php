<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\AssessmentSchema;
use RuntimeException;

final class AssessmentService implements AssessmentAnalyzerInterface
{
    public function __construct(
        private readonly OpenAIClient $openAIClient
    ) {
    }

    /**
     * @param array<int, array<string,mixed>> $uploads
     * @return array<string,mixed>
     */
    public function analyze(string $roomType, array $uploads): array
    {
        $result = $this->openAIClient->analyze($roomType, $uploads);
        $validation = AssessmentSchema::validateAssessmentResult($result);

        if (!($validation['ok'] ?? false)) {
            $errors = $validation['errors'] ?? ['Onbekende validatiefout'];
            throw new RuntimeException('AI-output ongeldig: ' . implode(' | ', $errors));
        }

        /** @var array<string,mixed> $validated */
        $validated = $validation['data'];
        return $validated;
    }
}
