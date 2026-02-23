<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Domain\ExerciseLibrary;
use App\Domain\RoomConfig;
use App\Services\ValRiskService;

final class ValRiskController
{
    private const SESSION_KEY = 'valrisico';

    public function __construct(
        private readonly View $view,
        private readonly string $mainSiteUrl,
        private readonly string $supportPhone,
        private readonly ValRiskService $valRiskService
    ) {
    }

    public function welcome(Request $request): Response
    {
        $data = $this->sessionData();

        return $this->render('valrisico_welcome', $request, [
            'title' => 'Valpreventie check',
            'caregiverMode' => (bool) ($data['caregiver_mode'] ?? false),
        ]);
    }

    public function howItWorks(Request $request): Response
    {
        return $this->render('valrisico_how_it_works', $request, [
            'title' => 'Hoe werkt de valrisico check?',
        ]);
    }

    public function step(Request $request, int $stepNumber): Response
    {
        if ($stepNumber < 1 || $stepNumber > 5) {
            return Response::redirect('/valrisico');
        }

        $data = $this->sessionData();
        $answers = is_array($data['answers'] ?? null) ? $data['answers'] : [];

        $this->applyAutoLowResultWhenAllNo($answers, $data);
        $data = $this->sessionData();

        if (($data['result']['final'] ?? null) === ValRiskService::RISK_LOW && $stepNumber >= 4) {
            return Response::redirect('/valrisico/resultaat');
        }

        if ($stepNumber === 1) {
            $question = $this->valRiskService->screeningQuestions()[0];
            return $this->renderQuestionStep($request, $stepNumber, $question, $answers);
        }

        if ($stepNumber === 2) {
            if (!isset($answers['screening_fall_last12'])) {
                return Response::redirect('/valrisico/stap/1');
            }

            $question = $this->valRiskService->screeningQuestions()[1];
            return $this->renderQuestionStep($request, $stepNumber, $question, $answers);
        }

        if ($stepNumber === 3) {
            if (!isset($answers['screening_fall_last12'])) {
                return Response::redirect('/valrisico/stap/1');
            }

            if (!isset($answers['screening_fear_fall'])) {
                return Response::redirect('/valrisico/stap/2');
            }

            $question = $this->valRiskService->screeningQuestions()[2];
            return $this->renderQuestionStep($request, $stepNumber, $question, $answers);
        }

        if ($stepNumber === 4) {
            foreach ($this->valRiskService->screeningQuestions() as $screeningQuestion) {
                if (!isset($answers[$screeningQuestion['key']])) {
                    return Response::redirect('/valrisico/stap/1');
                }
            }

            if ($this->valRiskService->allScreeningAnswersAreNo($answers)) {
                return Response::redirect('/valrisico/resultaat');
            }

            $question = $this->valRiskService->followUpQuestion();
            return $this->renderQuestionStep($request, $stepNumber, $question, $answers);
        }

        if (!isset($answers['recent_fall_help'])) {
            return Response::redirect('/valrisico/stap/4');
        }

        $selectedFactors = is_array($data['risk_factors'] ?? null) ? $data['risk_factors'] : [];

        return $this->render('valrisico_step_checklist', $request, [
            'title' => 'Valrisico check - stap 5',
            'stepNumber' => 5,
            'totalSteps' => 5,
            'riskFactors' => $this->valRiskService->riskFactors(),
            'selectedFactors' => $selectedFactors,
        ]);
    }

    public function submitAnswer(Request $request): Response
    {
        $action = isset($_POST['action']) ? (string) $_POST['action'] : '';

        if ($action === 'start') {
            $this->setSessionData([
                'caregiver_mode' => isset($_POST['caregiver_mode']) && $_POST['caregiver_mode'] === '1',
                'language' => 'nl',
                'answers' => [],
                'risk_factors' => [],
                'looptest' => [
                    'assist_mode' => null,
                    'seconds' => null,
                    'skipped' => false,
                ],
                'result' => [
                    'preliminary' => null,
                    'final' => null,
                    'computed_at' => null,
                ],
                'exercise_feedback' => null,
                'exercise_session' => $this->valRiskService->defaultExerciseSessionState(),
                'exercise_dialog' => null,
            ]);

            return Response::redirect('/valrisico/stap/1');
        }

        if ($action === 'answer_question') {
            $key = isset($_POST['question_key']) ? (string) $_POST['question_key'] : '';
            $value = isset($_POST['answer']) ? (string) $_POST['answer'] : 'unknown';

            if (!in_array($value, ['yes', 'no', 'unknown'], true)) {
                $value = 'unknown';
            }

            $data = $this->sessionData();
            $answers = is_array($data['answers'] ?? null) ? $data['answers'] : [];
            $answers[$key] = $value;
            $data['answers'] = $answers;
            $this->setSessionData($data);

            if ($key === 'screening_fall_last12') {
                return Response::redirect('/valrisico/stap/2');
            }

            if ($key === 'screening_fear_fall') {
                return Response::redirect('/valrisico/stap/3');
            }

            if ($key === 'screening_mobility_balance') {
                if ($this->valRiskService->allScreeningAnswersAreNo($answers)) {
                    $this->storeResult(ValRiskService::RISK_LOW, ValRiskService::RISK_LOW);
                    return Response::redirect('/valrisico/resultaat');
                }

                return Response::redirect('/valrisico/stap/4');
            }

            if ($key === 'recent_fall_help') {
                return Response::redirect('/valrisico/stap/5');
            }

            return Response::redirect('/valrisico/stap/1');
        }

        if ($action === 'answer_checklist') {
            $data = $this->sessionData();
            $selectedFactors = [];
            $selectedRaw = $_POST['risk_factors'] ?? [];

            if (!is_array($selectedRaw)) {
                $selectedRaw = [];
            }

            $selectedLookup = [];
            foreach ($selectedRaw as $itemKey) {
                if (is_string($itemKey) && $itemKey !== '') {
                    $selectedLookup[$itemKey] = true;
                }
            }

            foreach ($this->valRiskService->riskFactors() as $factor) {
                $factorKey = $factor['key'];
                $selectedFactors[$factorKey] = isset($selectedLookup[$factorKey]);
            }

            $data['risk_factors'] = $selectedFactors;
            $data['looptest'] = [
                'assist_mode' => null,
                'seconds' => null,
                'skipped' => false,
            ];
            $this->setSessionData($data);

            $preliminaryRisk = $this->valRiskService->determinePreliminaryRisk($selectedFactors);

            if ($preliminaryRisk === ValRiskService::RISK_HIGH) {
                $this->storeResult(ValRiskService::RISK_HIGH, ValRiskService::RISK_HIGH);
                return Response::redirect('/valrisico/resultaat');
            }

            $this->storeResult(ValRiskService::RISK_MODERATE, null);
            return Response::redirect('/valrisico/looptest');
        }

        if ($action === 'session_feedback') {
            $feedbackRaw = isset($_POST['feedback']) ? (string) $_POST['feedback'] : null;
            $feedback = $this->valRiskService->normalizeSessionFeedback($feedbackRaw);
            if ($feedback === null) {
                return Response::redirect('/valrisico/oefeningen');
            }

            $data = $this->sessionData();
            $exerciseState = $this->valRiskService->normalizeExerciseSessionState(
                is_array($data['exercise_session'] ?? null) ? $data['exercise_session'] : []
            );
            $exerciseState = $this->valRiskService->applyFeedback($exerciseState, $feedback);
            if ($feedback === ValRiskService::FEEDBACK_HARD) {
                $exerciseState = $this->valRiskService->applyEasierMode($exerciseState);
            }

            $data['exercise_session'] = $exerciseState;
            $data['exercise_feedback'] = $feedback;
            $data['exercise_dialog'] = ['type' => $feedback];
            $this->setSessionData($data);

            return Response::redirect('/valrisico/oefeningen');
        }

        if ($action === 'session_feedback_modal') {
            $modalAction = isset($_POST['modal_action']) ? (string) $_POST['modal_action'] : '';
            $data = $this->sessionData();
            $exerciseState = $this->valRiskService->normalizeExerciseSessionState(
                is_array($data['exercise_session'] ?? null) ? $data['exercise_session'] : []
            );
            $data['exercise_dialog'] = null;

            if ($modalAction === 'make_easier') {
                $data['exercise_session'] = $this->valRiskService->applyEasierMode($exerciseState);
                $this->setSessionData($data);
                return Response::redirect('/valrisico/oefeningen');
            }

            $this->setSessionData($data);

            if ($modalAction === 'next_session') {
                return Response::redirect('/valrisico/resultaat');
            }

            if ($modalAction === 'show_all_exercises') {
                return Response::redirect('/valrisico/oefeningen#valrisico-first-exercise-block');
            }

            if ($modalAction === 'plan_advice') {
                return Response::redirect('/contact');
            }

            if ($modalAction === 'safety_tips') {
                return Response::redirect('/valrisico/uitleg');
            }

            return Response::redirect('/valrisico/oefeningen');
        }

        return Response::redirect('/valrisico');
    }

    public function looptest(Request $request): Response
    {
        $data = $this->sessionData();
        $preliminaryRisk = (string) ($data['result']['preliminary'] ?? '');

        if ($preliminaryRisk === '') {
            return Response::redirect('/valrisico/stap/1');
        }

        if ($preliminaryRisk === ValRiskService::RISK_HIGH) {
            return Response::redirect('/valrisico/resultaat');
        }

        $phase = (string) $request->query('fase', 'veiligheid');
        if (!in_array($phase, ['veiligheid', 'instructie', 'invoer'], true)) {
            $phase = 'veiligheid';
        }

        return $this->render('valrisico_looptest', $request, [
            'title' => 'Looptest (optioneel)',
            'phase' => $phase,
            'assistMode' => (string) (($data['looptest']['assist_mode'] ?? 'with_help')),
            'seconds' => $data['looptest']['seconds'] ?? null,
            'errorMessage' => (string) $request->query('error', ''),
        ]);
    }

    public function submitLooptest(Request $request): Response
    {
        $data = $this->sessionData();
        $preliminaryRisk = (string) ($data['result']['preliminary'] ?? '');

        if ($preliminaryRisk !== ValRiskService::RISK_MODERATE) {
            return Response::redirect('/valrisico/resultaat');
        }

        $action = isset($_POST['action']) ? (string) $_POST['action'] : '';

        if ($action === 'skip') {
            $data['looptest'] = [
                'assist_mode' => $data['looptest']['assist_mode'] ?? null,
                'seconds' => null,
                'skipped' => true,
            ];
            $this->setSessionData($data);

            $this->storeResult(ValRiskService::RISK_MODERATE, ValRiskService::RISK_MODERATE);

            return Response::redirect('/valrisico/resultaat');
        }

        if ($action === 'save_choice') {
            $assistMode = isset($_POST['assist_mode']) ? (string) $_POST['assist_mode'] : 'with_help';
            if (!in_array($assistMode, ['with_help', 'alone'], true)) {
                $assistMode = 'with_help';
            }

            $data['looptest']['assist_mode'] = $assistMode;
            $this->setSessionData($data);

            return Response::redirect('/valrisico/looptest?fase=instructie');
        }

        if ($action === 'to_input') {
            return Response::redirect('/valrisico/looptest?fase=invoer');
        }

        if ($action === 'submit_seconds') {
            $secondsRaw = isset($_POST['seconds']) ? (string) $_POST['seconds'] : '';
            $secondsRaw = str_replace(',', '.', trim($secondsRaw));

            if (!is_numeric($secondsRaw)) {
                return Response::redirect('/valrisico/looptest?fase=invoer&error=' . rawurlencode('Vul een geldig aantal seconden in.'));
            }

            $seconds = (float) $secondsRaw;

            if ($seconds <= 0 || $seconds > 120) {
                return Response::redirect('/valrisico/looptest?fase=invoer&error=' . rawurlencode('Gebruik een waarde tussen 0.1 en 120 seconden.'));
            }

            $data['looptest'] = [
                'assist_mode' => $data['looptest']['assist_mode'] ?? 'with_help',
                'seconds' => $seconds,
                'skipped' => false,
            ];
            $this->setSessionData($data);

            $finalRisk = $this->valRiskService->determineFinalRisk(
                ValRiskService::RISK_MODERATE,
                $seconds,
                false
            );

            $this->storeResult(ValRiskService::RISK_MODERATE, $finalRisk);

            return Response::redirect('/valrisico/resultaat');
        }

        return Response::redirect('/valrisico/looptest');
    }

    public function result(Request $request): Response
    {
        $data = $this->sessionData();
        $answers = is_array($data['answers'] ?? null) ? $data['answers'] : [];
        $result = is_array($data['result'] ?? null) ? $data['result'] : [];

        if (($result['preliminary'] ?? null) === null && $answers !== []) {
            $this->applyAutoLowResultWhenAllNo($answers, $data);
            $data = $this->sessionData();
            $result = is_array($data['result'] ?? null) ? $data['result'] : [];
        }

        $preliminaryRisk = $result['preliminary'] ?? null;
        $finalRisk = $result['final'] ?? null;

        if (!is_string($preliminaryRisk) || $preliminaryRisk === '' || !is_string($finalRisk) || $finalRisk === '') {
            return Response::redirect('/valrisico');
        }

        $resultContent = $this->valRiskService->resultContent($finalRisk);
        $supportPhoneRaw = trim($this->supportPhone);
        $supportPhoneTel = $this->toTelHref($supportPhoneRaw);
        $looptest = is_array($data['looptest'] ?? null) ? $data['looptest'] : [];

        return $this->render('valrisico_result', $request, [
            'title' => 'Valrisico uitslag',
            'riskLevel' => $finalRisk,
            'preliminaryRisk' => $preliminaryRisk,
            'content' => $resultContent,
            'supportPhoneRaw' => $supportPhoneRaw,
            'supportPhoneTel' => $supportPhoneTel,
            'looptest' => $looptest,
        ]);
    }

    public function exercises(Request $request): Response
    {
        $data = $this->sessionData();
        $feedback = $this->valRiskService->normalizeSessionFeedback(
            is_string($data['exercise_feedback'] ?? null) ? $data['exercise_feedback'] : null
        );

        $exerciseSession = $this->valRiskService->normalizeExerciseSessionState(
            is_array($data['exercise_session'] ?? null) ? $data['exercise_session'] : []
        );

        $feedbackDialog = null;
        if (is_array($data['exercise_dialog'] ?? null) && is_string($data['exercise_dialog']['type'] ?? null)) {
            $dialogType = $this->valRiskService->normalizeSessionFeedback((string) $data['exercise_dialog']['type']);
            if ($dialogType !== null) {
                $feedbackDialog = ['type' => $dialogType];
            }
        }

        if ($feedbackDialog !== null || !isset($data['exercise_session'])) {
            $data['exercise_session'] = $exerciseSession;
            $data['exercise_dialog'] = null;
            $this->setSessionData($data);
        }

        return $this->render('valrisico_exercises', $request, [
            'title' => 'Oefeningen voor thuis',
            'exercises' => ExerciseLibrary::all(),
            'starterSession' => $this->valRiskService->starterSession(),
            'feedback' => $feedback,
            'exerciseSession' => $exerciseSession,
            'feedbackDialog' => $feedbackDialog,
            'progressAdvice' => $this->valRiskService->nextProgressAdvice($feedback),
        ]);
    }

    public function exerciseDetail(Request $request, string $slug): Response
    {
        $exercise = ExerciseLibrary::findBySlug($slug);

        if ($exercise === null) {
            return Response::redirect('/valrisico/oefeningen');
        }

        return $this->render('valrisico_exercise_detail', $request, [
            'title' => (string) $exercise['titel'],
            'exercise' => $exercise,
        ]);
    }

    public function printSummary(Request $request): Response
    {
        $data = $this->sessionData();
        $result = is_array($data['result'] ?? null) ? $data['result'] : [];
        $riskLevel = is_string($result['final'] ?? null) ? $result['final'] : '';

        if ($riskLevel === '') {
            return Response::redirect('/valrisico');
        }

        return $this->render('valrisico_print', $request, [
            'title' => 'Valrisico samenvatting',
            'riskLevel' => $riskLevel,
            'content' => $this->valRiskService->resultContent($riskLevel),
        ]);
    }

    public function reset(Request $request): Response
    {
        unset($_SESSION[self::SESSION_KEY]);
        return Response::redirect('/valrisico');
    }

    /**
     * @param array<string, string> $answers
     * @param array<string, mixed> $data
     */
    private function applyAutoLowResultWhenAllNo(array $answers, array $data): void
    {
        foreach ($this->valRiskService->screeningQuestions() as $question) {
            if (!isset($answers[$question['key']])) {
                return;
            }
        }

        if ($this->valRiskService->allScreeningAnswersAreNo($answers)) {
            $this->storeResult(ValRiskService::RISK_LOW, ValRiskService::RISK_LOW);

            $data['looptest'] = [
                'assist_mode' => null,
                'seconds' => null,
                'skipped' => true,
            ];
            $this->setSessionData($data);
        }
    }

    private function storeResult(string $preliminaryRisk, ?string $finalRisk): void
    {
        $data = $this->sessionData();
        $data['result'] = [
            'preliminary' => $preliminaryRisk,
            'final' => $finalRisk ?? $preliminaryRisk,
            'computed_at' => gmdate('c'),
        ];
        $this->setSessionData($data);
    }

    /** @return array<string,mixed> */
    private function sessionData(): array
    {
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [
                'caregiver_mode' => false,
                'language' => 'nl',
                'answers' => [],
                'risk_factors' => [],
                'looptest' => [
                    'assist_mode' => null,
                    'seconds' => null,
                    'skipped' => false,
                ],
                'result' => [
                    'preliminary' => null,
                    'final' => null,
                    'computed_at' => null,
                ],
                'exercise_feedback' => null,
                'exercise_session' => $this->valRiskService->defaultExerciseSessionState(),
                'exercise_dialog' => null,
            ];
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /** @param array<string,mixed> $data */
    private function setSessionData(array $data): void
    {
        $_SESSION[self::SESSION_KEY] = $data;
    }

    private function toTelHref(string $phone): string
    {
        if ($phone === '') {
            return '';
        }

        $clean = preg_replace('/[^0-9+]/', '', $phone);

        return is_string($clean) ? 'tel:' . $clean : '';
    }

    /** @param array<string,mixed> $data */
    private function render(string $template, Request $request, array $data = [], int $status = 200): Response
    {
        $path = rtrim($request->path(), '/') ?: '/';
        $roomQuery = (string) $request->query('room', '');

        $html = $this->view->render($template, array_merge($data, [
            'mainSiteUrl' => $this->mainSiteUrl,
            'currentPath' => $path,
            'queryRoom' => $roomQuery,
            'navItems' => RoomConfig::navItems(),
            'activeRoomKey' => RoomConfig::activeRoomKey($path, $roomQuery),
        ]));

        return Response::html($html, $status);
    }

    private function renderQuestionStep(Request $request, int $stepNumber, array $question, array $answers): Response
    {
        return $this->render('valrisico_step_question', $request, [
            'title' => 'Valrisico check - stap ' . $stepNumber,
            'stepNumber' => $stepNumber,
            'totalSteps' => 5,
            'questionKey' => $question['key'],
            'question' => $question['question'],
            'why' => $question['why'],
            'currentValue' => $answers[$question['key']] ?? null,
        ]);
    }
}
