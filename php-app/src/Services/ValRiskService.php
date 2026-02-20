<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\ExerciseLibrary;

final class ValRiskService
{
    public const RISK_LOW = 'low';
    public const RISK_MODERATE = 'moderate';
    public const RISK_HIGH = 'high';
    public const FEEDBACK_GOOD = 'good';
    public const FEEDBACK_HARD = 'hard';
    public const FEEDBACK_SYMPTOMS = 'symptoms';
    public const DIFFICULTY_STANDARD = 'standard';
    public const DIFFICULTY_EASIER = 'easier';
    public const STATUS_IDLE = 'idle';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PAUSED_DUE_TO_SYMPTOMS = 'paused_due_to_symptoms';

    /** @return array<string, mixed> */
    public function defaultExerciseSessionState(): array
    {
        return [
            'lastFeedback' => null,
            'consecutiveGoodCount' => 0,
            'difficultyMode' => self::DIFFICULTY_STANDARD,
            'status' => self::STATUS_IDLE,
            'completed' => false,
            'restSeconds' => 0,
            'params' => [
                'sit_to_stand' => [
                    'metric' => 'reps',
                    'value' => 6,
                    'tip' => '',
                ],
                'heel_raises' => [
                    'metric' => 'reps',
                    'value' => 8,
                    'tip' => '',
                ],
                'marching' => [
                    'metric' => 'seconds',
                    'value' => 45,
                    'tip' => '',
                ],
            ],
        ];
    }

    /** @return array<int, array<string, string>> */
    public function screeningQuestions(): array
    {
        return [
            [
                'key' => 'screening_fall_last12',
                'question' => 'Bent u de afgelopen 12 maanden gevallen?',
                'why' => 'Eerder vallen zegt iets over de kans om opnieuw te vallen.',
            ],
            [
                'key' => 'screening_fear_fall',
                'question' => 'Bent u bezorgd om te vallen?',
                'why' => 'Angst om te vallen kan invloed hebben op bewegen en zekerheid.',
            ],
            [
                'key' => 'screening_mobility_balance',
                'question' => 'Heeft u moeite met bewegen, lopen of uw balans houden?',
                'why' => 'Balans en kracht helpen om vallen te voorkomen.',
            ],
        ];
    }

    /** @return array<string, string> */
    public function followUpQuestion(): array
    {
        return [
            'key' => 'recent_fall_help',
            'question' => 'Heeft u onlangs een val gehad waarvoor u nu hulp zoekt?',
            'why' => 'Zo kunnen we beter bepalen welk advies nu het meest helpt.',
        ];
    }

    /** @return array<int, array<string, string>> */
    public function riskFactors(): array
    {
        return [
            [
                'key' => 'injury_after_fall',
                'label' => 'Ik had letsel door de val.',
                'why' => 'Letsel na een val kan betekenen dat extra ondersteuning verstandig is.',
            ],
            [
                'key' => 'two_or_more_falls',
                'label' => 'Ik ben twee of meer keer gevallen in het afgelopen jaar.',
                'why' => 'Meerdere valmomenten kunnen wijzen op een hoger risico.',
            ],
            [
                'key' => 'dizzy_or_blackout',
                'label' => 'Ik raakte even weg of werd plots duizelig bij de val.',
                'why' => 'Plots duizelig worden kan uw stabiliteit onverwacht verminderen.',
            ],
            [
                'key' => 'cannot_get_up',
                'label' => 'Ik kon niet zelfstandig overeind komen na de val.',
                'why' => 'Niet zelfstandig opstaan kan extra risico geven bij een volgende val.',
            ],
            [
                'key' => 'frailty',
                'label' => 'Ik ben kwetsbaar.',
                'why' => 'Bijvoorbeeld snel moe, minder kracht, onbedoeld afvallen, vaker hulp nodig.',
            ],
        ];
    }

    /**
     * @param array<string, string> $answers
     */
    public function allScreeningAnswersAreNo(array $answers): bool
    {
        foreach ($this->screeningQuestions() as $question) {
            $value = $answers[$question['key']] ?? null;

            if ($value !== 'no') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, bool> $riskFactors
     */
    public function determinePreliminaryRisk(array $riskFactors): string
    {
        foreach ($riskFactors as $isChecked) {
            if ($isChecked === true) {
                return self::RISK_HIGH;
            }
        }

        return self::RISK_MODERATE;
    }

    public function determineFinalRisk(string $preliminaryRisk, ?float $looptestSeconds, bool $looptestSkipped): string
    {
        if ($preliminaryRisk === self::RISK_HIGH) {
            return self::RISK_HIGH;
        }

        if ($preliminaryRisk === self::RISK_LOW) {
            return self::RISK_LOW;
        }

        if ($looptestSkipped || $looptestSeconds === null) {
            return self::RISK_MODERATE;
        }

        return $looptestSeconds < 4.0 ? self::RISK_LOW : self::RISK_MODERATE;
    }

    /** @return array<string, mixed> */
    public function resultContent(string $riskLevel): array
    {
        if ($riskLevel === self::RISK_HIGH) {
            return [
                'title' => 'Uw uitslag: Hoog valrisico',
                'badge' => 'Hoog',
                'class' => 'label-high',
                'intro' => 'Uw antwoorden laten zien dat u een verhoogde kans op vallen heeft. Dat is vervelend, maar u kunt hier vaak iets aan doen.',
                'meaning' => 'Dit betekent dat extra ondersteuning waarschijnlijk helpt om veiliger te bewegen.',
                'checklist' => [
                    'Neem contact op voor een valanalyse en plan op maat.',
                    'Doe alleen veilige, lichte oefeningen met steun.',
                    'Pak woningrisico’s meteen aan.',
                ],
                'today_actions' => [
                    'Zet vanavond extra licht aan in looproutes.',
                    'Haal losse kleedjes en kabels uit de weg.',
                    'Houd telefoon binnen handbereik.',
                ],
                'support_advice' => [
                    'Bij nieuwe val of plotselinge klachten: neem laagdrempelig contact op met huisarts of zorgverlener.',
                    'Bij acute ernstige klachten: neem direct contact op met een zorgverlener die u kent.',
                ],
                'housing_ctas' => [
                    ['label' => 'Start Badkamercheck', 'href' => '/assessment/new?room=bathroom', 'primary' => true],
                    ['label' => 'Start Trap en hal check', 'href' => '/assessment/new?room=stairs_hall', 'primary' => true],
                ],
            ];
        }

        if ($riskLevel === self::RISK_MODERATE) {
            return [
                'title' => 'Uw uitslag: Matig valrisico',
                'badge' => 'Matig',
                'class' => 'label-medium',
                'intro' => 'Uw antwoorden laten zien dat u een verhoogde kans op vallen heeft. Dat is vervelend, maar u kunt hier vaak iets aan doen.',
                'meaning' => 'Met kleine stappen kunt u vaak snel meer zekerheid en stabiliteit opbouwen.',
                'checklist' => [
                    'Start een rustig oefenprogramma.',
                    'Maak looproutes vrij en zorg voor goed licht.',
                    'Bespreek zorgen met huisarts of fysiotherapeut als u dat wilt.',
                ],
                'today_actions' => [],
                'support_advice' => [
                    'Bij aanhoudende onzekerheid of valangst kan een gesprek met huisarts of fysiotherapeut helpen.',
                    'Bij acute ernstige klachten: neem direct contact op met een zorgverlener die u kent.',
                ],
                'housing_ctas' => [
                    ['label' => 'Start Trap en hal check', 'href' => '/assessment/new?room=stairs_hall', 'primary' => true],
                    ['label' => 'Start Badkamercheck', 'href' => '/assessment/new?room=bathroom', 'primary' => false],
                ],
            ];
        }

        return [
            'title' => 'Uw uitslag: Laag valrisico',
            'badge' => 'Laag',
            'class' => 'label-low',
            'intro' => 'Uw antwoorden laten zien dat u op dit moment een lagere kans op vallen heeft. Dat is positief, en u kunt dit helpen behouden.',
            'meaning' => 'Blijf in beweging en houd uw huis overzichtelijk om dit niveau vast te houden.',
            'checklist' => [
                'Blijf regelmatig bewegen.',
                'Oefen balans en kracht 3 keer per week.',
                'Zorg voor goed licht in huis, vooral ’s avonds.',
            ],
            'today_actions' => [],
            'support_advice' => [
                'Als uw situatie verandert of u vaker onzeker loopt, doe de check opnieuw of bespreek dit met uw huisarts.',
                'Bij acute ernstige klachten: neem direct contact op met een zorgverlener die u kent.',
            ],
            'housing_ctas' => [
                ['label' => 'Start Trap en hal check', 'href' => '/assessment/new?room=stairs_hall', 'primary' => false],
            ],
        ];
    }

    /** @return array<int, array<string,mixed>> */
    public function starterSession(): array
    {
        $exercises = ExerciseLibrary::all();

        return [
            $exercises[0],
            $exercises[1],
            $exercises[4],
        ];
    }

    public function nextProgressAdvice(?string $feedback): string
    {
        $normalized = $this->normalizeSessionFeedback($feedback);

        return match ($normalized) {
            self::FEEDBACK_GOOD => 'Mooi. U kunt doorgaan. Bij twee keer achter elkaar goed te doen wordt de sessie rustig opgebouwd.',
            self::FEEDBACK_HARD => 'Dank u. U kunt kiezen voor een lichtere sessie met extra rust.',
            self::FEEDBACK_SYMPTOMS => 'Stop bij klachten en kies voor veilig advies voordat u verder oefent.',
            default => 'Kies na uw sessie hoe het ging. Dan krijgt u passend vervolgadvies.',
        };
    }

    public function normalizeSessionFeedback(?string $feedback): ?string
    {
        $normalized = strtolower(trim((string) $feedback));

        return match ($normalized) {
            self::FEEDBACK_GOOD, 'goed' => self::FEEDBACK_GOOD,
            self::FEEDBACK_HARD, 'moeilijk' => self::FEEDBACK_HARD,
            self::FEEDBACK_SYMPTOMS, 'klachten' => self::FEEDBACK_SYMPTOMS,
            default => null,
        };
    }

    /** @param array<string, mixed> $state
     *  @return array<string, mixed>
     */
    public function normalizeExerciseSessionState(array $state): array
    {
        $defaults = $this->defaultExerciseSessionState();
        $params = is_array($state['params'] ?? null) ? $state['params'] : [];
        $defaultParams = is_array($defaults['params']) ? $defaults['params'] : [];

        $normalizedParams = [];
        foreach ($defaultParams as $exerciseKey => $defaultParam) {
            $paramState = is_array($params[$exerciseKey] ?? null) ? $params[$exerciseKey] : [];
            $defaultValue = (int) ($defaultParam['value'] ?? 0);
            $value = (int) ($paramState['value'] ?? $defaultValue);
            $tip = is_string($paramState['tip'] ?? null) ? (string) $paramState['tip'] : '';
            $metric = is_string($defaultParam['metric'] ?? null) ? (string) $defaultParam['metric'] : 'reps';

            $normalizedParams[$exerciseKey] = [
                'metric' => $metric,
                'value' => $value,
                'tip' => $tip,
            ];
        }

        $lastFeedback = $this->normalizeSessionFeedback(isset($state['lastFeedback']) ? (string) $state['lastFeedback'] : null);
        $difficultyMode = (string) ($state['difficultyMode'] ?? self::DIFFICULTY_STANDARD);
        if (!in_array($difficultyMode, [self::DIFFICULTY_STANDARD, self::DIFFICULTY_EASIER], true)) {
            $difficultyMode = self::DIFFICULTY_STANDARD;
        }

        $status = (string) ($state['status'] ?? self::STATUS_IDLE);
        if (!in_array($status, [self::STATUS_IDLE, self::STATUS_COMPLETED, self::STATUS_PAUSED_DUE_TO_SYMPTOMS], true)) {
            $status = self::STATUS_IDLE;
        }

        return [
            'lastFeedback' => $lastFeedback,
            'consecutiveGoodCount' => max(0, (int) ($state['consecutiveGoodCount'] ?? 0)),
            'difficultyMode' => $difficultyMode,
            'status' => $status,
            'completed' => (bool) ($state['completed'] ?? false),
            'restSeconds' => max(0, (int) ($state['restSeconds'] ?? 0)),
            'params' => $normalizedParams,
        ];
    }

    /** @param array<string, mixed> $state
     *  @return array<string, mixed>
     */
    public function applyFeedback(array $state, string $feedback): array
    {
        $normalizedFeedback = $this->normalizeSessionFeedback($feedback);
        if ($normalizedFeedback === null) {
            return $this->normalizeExerciseSessionState($state);
        }

        $next = $this->normalizeExerciseSessionState($state);

        if ($normalizedFeedback === self::FEEDBACK_GOOD) {
            $previousFeedback = $this->normalizeSessionFeedback(isset($next['lastFeedback']) ? (string) $next['lastFeedback'] : null);
            $next['lastFeedback'] = self::FEEDBACK_GOOD;
            $next['status'] = self::STATUS_COMPLETED;
            $next['completed'] = true;
            $next['consecutiveGoodCount'] = $previousFeedback === self::FEEDBACK_GOOD
                ? ((int) $next['consecutiveGoodCount'] + 1)
                : 1;

            return $this->applyProgressionIfEligible($next);
        }

        if ($normalizedFeedback === self::FEEDBACK_HARD) {
            $next['lastFeedback'] = self::FEEDBACK_HARD;
            $next['status'] = self::STATUS_COMPLETED;
            $next['completed'] = true;
            $next['consecutiveGoodCount'] = 0;

            return $next;
        }

        $next['lastFeedback'] = self::FEEDBACK_SYMPTOMS;
        return $this->applySymptomsFlow($next);
    }

    /** @param array<string, mixed> $state
     *  @return array<string, mixed>
     */
    public function applyProgressionIfEligible(array $state): array
    {
        $next = $this->normalizeExerciseSessionState($state);
        $status = (string) ($next['status'] ?? self::STATUS_IDLE);

        if ($status === self::STATUS_PAUSED_DUE_TO_SYMPTOMS) {
            return $next;
        }

        $lastFeedback = $this->normalizeSessionFeedback(isset($next['lastFeedback']) ? (string) $next['lastFeedback'] : null);
        if ($lastFeedback !== self::FEEDBACK_GOOD) {
            return $next;
        }

        if ((int) ($next['consecutiveGoodCount'] ?? 0) < 2) {
            return $next;
        }

        $params = is_array($next['params'] ?? null) ? $next['params'] : [];
        $params['sit_to_stand']['value'] = min(12, (int) ($params['sit_to_stand']['value'] ?? 6) + 1);
        $params['heel_raises']['value'] = min(16, (int) ($params['heel_raises']['value'] ?? 8) + 2);
        $params['marching']['value'] = min(60, (int) ($params['marching']['value'] ?? 45) + 10);

        $next['params'] = $params;
        $next['consecutiveGoodCount'] = 0;

        return $next;
    }

    /** @param array<string, mixed> $state
     *  @return array<string, mixed>
     */
    public function applyEasierMode(array $state): array
    {
        $next = $this->normalizeExerciseSessionState($state);
        $params = is_array($next['params'] ?? null) ? $next['params'] : [];

        $params['sit_to_stand']['value'] = max(4, (int) ($params['sit_to_stand']['value'] ?? 6) - 3);
        $params['sit_to_stand']['tip'] = 'Kies een hogere stoel en gebruik gerust de armleuningen.';

        $params['heel_raises']['value'] = max(6, (int) ($params['heel_raises']['value'] ?? 8) - 4);
        $params['heel_raises']['tip'] = 'Houd met twee handen vast en kom een klein stukje omhoog.';

        $params['marching']['value'] = max(15, (int) ($params['marching']['value'] ?? 45) - 10);
        $params['marching']['tip'] = 'Rustig tempo en knieën laag is prima.';

        $next['params'] = $params;
        $next['restSeconds'] = 30;
        $next['difficultyMode'] = self::DIFFICULTY_EASIER;
        $next['consecutiveGoodCount'] = 0;

        return $next;
    }

    /** @param array<string, mixed> $state
     *  @return array<string, mixed>
     */
    public function applySymptomsFlow(array $state): array
    {
        $next = $this->normalizeExerciseSessionState($state);
        $next['status'] = self::STATUS_PAUSED_DUE_TO_SYMPTOMS;
        $next['completed'] = false;
        $next['consecutiveGoodCount'] = 0;
        $next['difficultyMode'] = self::DIFFICULTY_STANDARD;

        return $next;
    }
}
