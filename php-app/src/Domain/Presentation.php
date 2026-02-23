<?php

declare(strict_types=1);

namespace App\Domain;

final class Presentation
{
    public const DISCLAIMER_PARAGRAPHS = [
        'Deze beoordeling is gebaseerd op wat zichtbaar is op de aangeleverde foto’s. Factoren die niet in beeld zijn, zoals (avond)verlichting, slipweerstand, de staat van de ondergrond/muur, de bevestigingsmogelijkheden en de mobiliteit van de gebruiker, kunnen het risico beïnvloeden.',
        'Laat wandbeugels en andere dragende voorzieningen bij voorkeur plaatsen of controleren door een vakpersoon, zodat de bevestiging veilig en geschikt is voor de situatie.',
        'De uitkomst is bedoeld als algemene informatie en vervangt geen medisch advies, diagnose of acute hulpverlening',
    ];

    /** @return array{label:string,className:string} */
    public static function riskLabel(int $score): array
    {
        if ($score < 35) {
            return ['label' => 'Laag risico', 'className' => 'label-low'];
        }

        if ($score < 70) {
            return ['label' => 'Middelgroot risico', 'className' => 'label-medium'];
        }

        return ['label' => 'Hoog risico', 'className' => 'label-high'];
    }

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            'tripping_hazard' => 'Struikelgevaar',
            'slip_hazard' => 'Glijgevaar',
            'support_hazard' => 'Onvoldoende houvast',
            'lighting_hazard' => 'Verlichting & oriëntatie',
            'accessibility_hazard' => 'Toegankelijkheid',
            default => 'Overig',
        };
    }

    /** @param array<int, array<string, mixed>> $hazards */
    public static function buildActionPlan(array $hazards): array
    {
        $today = [];
        $week = [];
        $months = [];

        foreach ($hazards as $hazard) {
            $severity = (int) ($hazard['severity_1_5'] ?? 0);
            $actions = $hazard['suggested_actions'] ?? [];

            if (!is_array($actions)) {
                continue;
            }

            foreach ($actions as $action) {
                if (!is_array($action) || !isset($action['action'], $action['effort'])) {
                    continue;
                }

                $label = (string) $action['action'];
                $effort = (string) $action['effort'];

                if ($severity >= 4 || $effort === 'laag') {
                    $today[$label] = true;
                } elseif ($effort === 'middel') {
                    $week[$label] = true;
                } else {
                    $months[$label] = true;
                }
            }
        }

        return [
            'today' => array_slice(array_keys($today), 0, 8),
            'week' => array_slice(array_keys($week), 0, 8),
            'months' => array_slice(array_keys($months), 0, 8),
        ];
    }
}
