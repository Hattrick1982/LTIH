<?php

declare(strict_types=1);

namespace Tests;

use App\Domain\AssessmentSchema;
use PHPUnit\Framework\TestCase;

final class AssessmentSchemaValidationTest extends TestCase
{
    public function testValidAssessmentPayloadPassesValidation(): void
    {
        $payload = [
            'room_type' => 'bathroom',
            'overall_risk_score_0_100' => 68,
            'hazards' => [
                [
                    'category' => 'slip_hazard',
                    'severity_1_5' => 4,
                    'confidence_0_1' => 0.88,
                    'what_we_see' => 'Gladde tegelvloer bij douche-instap.',
                    'why_it_matters' => 'Natte tegels verhogen kans op uitglijden.',
                    'suggested_actions' => [
                        [
                            'action' => 'Plaats antislipmat in de douche.',
                            'effort' => 'laag',
                            'cost_band' => 'laag',
                        ],
                    ],
                    'needs_human_followup' => true,
                ],
            ],
            'missing_info_questions' => ['Is er een steunbeugel naast het toilet?'],
            'disclaimer' => 'Deze analyse is informatief en geen medisch advies.',
        ];

        $result = AssessmentSchema::validateAssessmentResult($payload);

        self::assertTrue($result['ok']);
    }

    public function testInvalidSeverityFailsValidation(): void
    {
        $payload = [
            'room_type' => 'bathroom',
            'overall_risk_score_0_100' => 68,
            'hazards' => [
                [
                    'category' => 'slip_hazard',
                    'severity_1_5' => 9,
                    'confidence_0_1' => 0.88,
                    'what_we_see' => 'x',
                    'why_it_matters' => 'y',
                    'suggested_actions' => [
                        [
                            'action' => 'z',
                            'effort' => 'laag',
                            'cost_band' => 'laag',
                        ],
                    ],
                    'needs_human_followup' => true,
                ],
            ],
            'missing_info_questions' => [],
            'disclaimer' => 'Deze analyse is informatief en geen medisch advies.',
        ];

        $result = AssessmentSchema::validateAssessmentResult($payload);

        self::assertFalse($result['ok']);
        self::assertNotEmpty($result['errors']);
    }
}
