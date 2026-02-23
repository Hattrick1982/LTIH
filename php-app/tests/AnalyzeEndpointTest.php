<?php

declare(strict_types=1);

namespace Tests;

use App\Controllers\ApiAssessmentController;
use App\Core\Request;
use App\Domain\Presentation;
use App\Services\AssessmentAnalyzerInterface;
use App\Services\ImageService;
use App\Services\StorageService;
use PHPUnit\Framework\TestCase;

final class AnalyzeEndpointTest extends TestCase
{
    public function testAnalyzeEndpointUsesMockedAnalyzerAndReturnsAssessmentId(): void
    {
        $tempPath = sys_get_temp_dir() . '/ltih-analyze-test-' . bin2hex(random_bytes(4));
        $storage = new StorageService($tempPath, 24);

        $fileId = '2db4ac65-a9fb-4a09-91be-0085f507f4f5';
        $uploadPath = $storage->uploadImagePath($fileId, 'jpg');
        file_put_contents($uploadPath, 'dummy');

        $storage->saveUploadRecord([
            'id' => $fileId,
            'mime_type' => 'image/jpeg',
            'path' => $uploadPath,
            'size_bytes' => filesize($uploadPath) ?: 0,
            'width' => 100,
            'height' => 100,
            'created_at' => gmdate('c'),
        ]);

        $mockAnalyzer = new class implements AssessmentAnalyzerInterface {
            public function analyze(string $roomType, array $uploads): array
            {
                return [
                    'room_type' => $roomType,
                    'overall_risk_score_0_100' => 42,
                    'hazards' => [
                        [
                            'category' => 'tripping_hazard',
                            'severity_1_5' => 3,
                            'confidence_0_1' => 0.7,
                            'what_we_see' => 'Los kleedje in looproute.',
                            'why_it_matters' => 'Kan tot struikelen leiden.',
                            'suggested_actions' => [
                                ['action' => 'Gebruik antislip onderlaag.', 'effort' => 'laag', 'cost_band' => 'laag'],
                            ],
                            'needs_human_followup' => false,
                        ],
                    ],
                    'missing_info_questions' => [],
                    'disclaimer' => Presentation::DISCLAIMER_PARAGRAPHS[0],
                ];
            }
        };

        $controller = new ApiAssessmentController($storage, new ImageService(), $mockAnalyzer);

        $request = new Request(
            'POST',
            '/api/assessment/analyze',
            [],
            json_encode([
                'room_type' => 'bathroom',
                'file_ids' => [$fileId, $fileId],
            ], JSON_THROW_ON_ERROR),
            []
        );

        $response = $controller->analyze($request);

        self::assertSame(200, $response->status());
        $body = json_decode($response->body(), true);
        self::assertIsArray($body);
        self::assertArrayHasKey('assessment_id', $body);

        $storage->cleanupExpired();
        @unlink($uploadPath);
    }
}
