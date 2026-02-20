<?php

declare(strict_types=1);

namespace App\Domain;

final class AssessmentSchema
{
    public const ROOM_TYPES = ['bathroom', 'stairs_hall', 'living_room', 'bedroom', 'kitchen'];
    public const HAZARD_CATEGORIES = [
        'tripping_hazard',
        'slip_hazard',
        'support_hazard',
        'lighting_hazard',
        'accessibility_hazard',
        'other',
    ];
    public const EFFORT_VALUES = ['laag', 'middel', 'hoog'];
    public const COST_VALUES = ['laag', 'middel', 'hoog'];

    /** @return array{ok:bool,data?:array<string,mixed>,message?:string,details?:array<int,string>} */
    public static function validateAnalyzeRequest(array $payload): array
    {
        $roomType = $payload['room_type'] ?? null;
        $fileIds = $payload['file_ids'] ?? null;

        if (!is_string($roomType) || !in_array($roomType, self::ROOM_TYPES, true)) {
            return [
                'ok' => false,
                'message' => 'Ongeldige input voor analyse.',
                'details' => ['room_type moet een geldige ruimte zijn.'],
            ];
        }

        if (!is_array($fileIds) || count($fileIds) < 2 || count($fileIds) > 5) {
            return [
                'ok' => false,
                'message' => 'Ongeldige input voor analyse.',
                'details' => ['file_ids moet 2 t/m 5 items bevatten.'],
            ];
        }

        foreach ($fileIds as $fileId) {
            if (!is_string($fileId) || !self::isUuid($fileId)) {
                return [
                    'ok' => false,
                    'message' => 'Ongeldige input voor analyse.',
                    'details' => ['Elk file_id moet een geldige UUID zijn.'],
                ];
            }
        }

        return [
            'ok' => true,
            'data' => [
                'room_type' => $roomType,
                'file_ids' => $fileIds,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{ok:bool,data?:array<string,mixed>,errors?:array<int,string>}
     */
    public static function validateAssessmentResult(array $payload): array
    {
        $errors = [];

        $roomType = $payload['room_type'] ?? null;
        if (!is_string($roomType) || !in_array($roomType, self::ROOM_TYPES, true)) {
            $errors[] = 'room_type is ongeldig.';
        }

        $score = $payload['overall_risk_score_0_100'] ?? null;
        if (!is_int($score) || $score < 0 || $score > 100) {
            $errors[] = 'overall_risk_score_0_100 moet int 0..100 zijn.';
        }

        $hazards = $payload['hazards'] ?? null;
        if (!is_array($hazards) || count($hazards) > 15) {
            $errors[] = 'hazards moet array zijn met max 15 items.';
        } else {
            foreach ($hazards as $index => $hazard) {
                if (!is_array($hazard)) {
                    $errors[] = "hazards[{$index}] moet object zijn.";
                    continue;
                }

                $category = $hazard['category'] ?? null;
                if (!is_string($category) || !in_array($category, self::HAZARD_CATEGORIES, true)) {
                    $errors[] = "hazards[{$index}].category is ongeldig.";
                }

                $severity = $hazard['severity_1_5'] ?? null;
                if (!is_int($severity) || $severity < 1 || $severity > 5) {
                    $errors[] = "hazards[{$index}].severity_1_5 moet int 1..5 zijn.";
                }

                $confidence = $hazard['confidence_0_1'] ?? null;
                if (!is_numeric($confidence) || (float) $confidence < 0 || (float) $confidence > 1) {
                    $errors[] = "hazards[{$index}].confidence_0_1 moet 0..1 zijn.";
                }

                $what = $hazard['what_we_see'] ?? null;
                if (!is_string($what) || trim($what) === '' || strlen($what) > 260) {
                    $errors[] = "hazards[{$index}].what_we_see is ongeldig.";
                }

                $why = $hazard['why_it_matters'] ?? null;
                if (!is_string($why) || trim($why) === '' || strlen($why) > 320) {
                    $errors[] = "hazards[{$index}].why_it_matters is ongeldig.";
                }

                $actions = $hazard['suggested_actions'] ?? null;
                if (!is_array($actions) || count($actions) < 1 || count($actions) > 5) {
                    $errors[] = "hazards[{$index}].suggested_actions moet 1..5 items bevatten.";
                } else {
                    foreach ($actions as $actionIndex => $action) {
                        if (!is_array($action)) {
                            $errors[] = "hazards[{$index}].suggested_actions[{$actionIndex}] moet object zijn.";
                            continue;
                        }

                        $actionText = $action['action'] ?? null;
                        if (!is_string($actionText) || trim($actionText) === '' || strlen($actionText) > 240) {
                            $errors[] = "hazards[{$index}].suggested_actions[{$actionIndex}].action is ongeldig.";
                        }

                        $effort = $action['effort'] ?? null;
                        if (!is_string($effort) || !in_array($effort, self::EFFORT_VALUES, true)) {
                            $errors[] = "hazards[{$index}].suggested_actions[{$actionIndex}].effort is ongeldig.";
                        }

                        $cost = $action['cost_band'] ?? null;
                        if (!is_string($cost) || !in_array($cost, self::COST_VALUES, true)) {
                            $errors[] = "hazards[{$index}].suggested_actions[{$actionIndex}].cost_band is ongeldig.";
                        }
                    }
                }

                if (!is_bool($hazard['needs_human_followup'] ?? null)) {
                    $errors[] = "hazards[{$index}].needs_human_followup moet boolean zijn.";
                }
            }
        }

        $questions = $payload['missing_info_questions'] ?? null;
        if (!is_array($questions) || count($questions) > 5) {
            $errors[] = 'missing_info_questions moet array zijn met max 5 items.';
        } else {
            foreach ($questions as $index => $question) {
                if (!is_string($question) || trim($question) === '' || strlen($question) > 220) {
                    $errors[] = "missing_info_questions[{$index}] is ongeldig.";
                }
            }
        }

        $disclaimer = $payload['disclaimer'] ?? null;
        if (!is_string($disclaimer) || trim($disclaimer) === '' || strlen($disclaimer) > 500) {
            $errors[] = 'disclaimer is ongeldig.';
        }

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        return ['ok' => true, 'data' => $payload];
    }

    /** @return array<string,mixed> */
    public static function jsonSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['room_type', 'overall_risk_score_0_100', 'hazards', 'missing_info_questions', 'disclaimer'],
            'properties' => [
                'room_type' => ['type' => 'string', 'enum' => self::ROOM_TYPES],
                'overall_risk_score_0_100' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                'hazards' => [
                    'type' => 'array',
                    'maxItems' => 15,
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => [
                            'category',
                            'severity_1_5',
                            'confidence_0_1',
                            'what_we_see',
                            'why_it_matters',
                            'suggested_actions',
                            'needs_human_followup',
                        ],
                        'properties' => [
                            'category' => ['type' => 'string', 'enum' => self::HAZARD_CATEGORIES],
                            'severity_1_5' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 5],
                            'confidence_0_1' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
                            'what_we_see' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 260],
                            'why_it_matters' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 320],
                            'suggested_actions' => [
                                'type' => 'array',
                                'minItems' => 1,
                                'maxItems' => 5,
                                'items' => [
                                    'type' => 'object',
                                    'additionalProperties' => false,
                                    'required' => ['action', 'effort', 'cost_band'],
                                    'properties' => [
                                        'action' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 240],
                                        'effort' => ['type' => 'string', 'enum' => self::EFFORT_VALUES],
                                        'cost_band' => ['type' => 'string', 'enum' => self::COST_VALUES],
                                    ],
                                ],
                            ],
                            'needs_human_followup' => ['type' => 'boolean'],
                        ],
                    ],
                ],
                'missing_info_questions' => [
                    'type' => 'array',
                    'maxItems' => 5,
                    'items' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 220],
                ],
                'disclaimer' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 500],
            ],
        ];
    }

    private static function isUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }
}
