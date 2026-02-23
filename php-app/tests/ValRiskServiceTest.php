<?php

declare(strict_types=1);

namespace Tests;

use App\Services\ValRiskService;
use PHPUnit\Framework\TestCase;

final class ValRiskServiceTest extends TestCase
{
    private ValRiskService $service;

    protected function setUp(): void
    {
        $this->service = new ValRiskService();
    }

    public function testAllScreeningNoLeadsToLowPath(): void
    {
        $answers = [
            'screening_fall_last12' => 'no',
            'screening_fear_fall' => 'no',
            'screening_mobility_balance' => 'no',
        ];

        self::assertTrue($this->service->allScreeningAnswersAreNo($answers));
    }

    public function testAnyRiskFactorSetsHighPreliminaryRisk(): void
    {
        $factors = [
            'injury_after_fall' => false,
            'two_or_more_falls' => true,
        ];

        self::assertSame(ValRiskService::RISK_HIGH, $this->service->determinePreliminaryRisk($factors));
    }

    public function testModerateRiskWithFastLooptestCanBecomeLow(): void
    {
        $final = $this->service->determineFinalRisk(ValRiskService::RISK_MODERATE, 3.9, false);
        self::assertSame(ValRiskService::RISK_LOW, $final);
    }

    public function testModerateRiskWithSlowLooptestStaysModerate(): void
    {
        $final = $this->service->determineFinalRisk(ValRiskService::RISK_MODERATE, 4.0, false);
        self::assertSame(ValRiskService::RISK_MODERATE, $final);
    }

    public function testHighRiskCannotBeLoweredByLooptest(): void
    {
        $final = $this->service->determineFinalRisk(ValRiskService::RISK_HIGH, 3.1, false);
        self::assertSame(ValRiskService::RISK_HIGH, $final);
    }

    public function testApplyProgressionIfEligibleBuildsUpAfterTwoGoodInARow(): void
    {
        $state = $this->service->defaultExerciseSessionState();
        $state['lastFeedback'] = ValRiskService::FEEDBACK_GOOD;
        $state['consecutiveGoodCount'] = 2;

        $next = $this->service->applyProgressionIfEligible($state);

        self::assertSame(7, $next['params']['sit_to_stand']['value']);
        self::assertSame(10, $next['params']['heel_raises']['value']);
        self::assertSame(55, $next['params']['marching']['value']);
        self::assertSame(0, $next['consecutiveGoodCount']);
    }

    public function testApplyProgressionIfEligibleRespectsMaximumCaps(): void
    {
        $state = $this->service->defaultExerciseSessionState();
        $state['lastFeedback'] = ValRiskService::FEEDBACK_GOOD;
        $state['consecutiveGoodCount'] = 2;
        $state['params']['sit_to_stand']['value'] = 12;
        $state['params']['heel_raises']['value'] = 16;
        $state['params']['marching']['value'] = 60;

        $next = $this->service->applyProgressionIfEligible($state);

        self::assertSame(12, $next['params']['sit_to_stand']['value']);
        self::assertSame(16, $next['params']['heel_raises']['value']);
        self::assertSame(60, $next['params']['marching']['value']);
        self::assertSame(0, $next['consecutiveGoodCount']);
    }

    public function testApplyEasierModeRespectsMinimumCapsAndAddsRest(): void
    {
        $state = $this->service->defaultExerciseSessionState();
        $state['params']['sit_to_stand']['value'] = 4;
        $state['params']['heel_raises']['value'] = 6;
        $state['params']['marching']['value'] = 15;

        $next = $this->service->applyEasierMode($state);

        self::assertSame(4, $next['params']['sit_to_stand']['value']);
        self::assertSame(6, $next['params']['heel_raises']['value']);
        self::assertSame(15, $next['params']['marching']['value']);
        self::assertSame(30, $next['restSeconds']);
        self::assertSame(ValRiskService::DIFFICULTY_EASIER, $next['difficultyMode']);
        self::assertNotSame('', $next['params']['sit_to_stand']['tip']);
        self::assertNotSame('', $next['params']['heel_raises']['tip']);
        self::assertNotSame('', $next['params']['marching']['tip']);
    }

    public function testApplySymptomsFlowPausesSessionAndBlocksCompletion(): void
    {
        $state = $this->service->defaultExerciseSessionState();
        $state['completed'] = true;
        $state['consecutiveGoodCount'] = 3;

        $next = $this->service->applySymptomsFlow($state);

        self::assertSame(ValRiskService::STATUS_PAUSED_DUE_TO_SYMPTOMS, $next['status']);
        self::assertFalse($next['completed']);
        self::assertSame(0, $next['consecutiveGoodCount']);
    }

    public function testConsecutiveGoodCountResetsOnHardAndSymptoms(): void
    {
        $state = $this->service->defaultExerciseSessionState();
        $state['lastFeedback'] = ValRiskService::FEEDBACK_GOOD;
        $state['consecutiveGoodCount'] = 2;

        $afterHard = $this->service->applyFeedback($state, ValRiskService::FEEDBACK_HARD);
        self::assertSame(0, $afterHard['consecutiveGoodCount']);
        self::assertSame(ValRiskService::FEEDBACK_HARD, $afterHard['lastFeedback']);

        $afterSymptoms = $this->service->applyFeedback($state, ValRiskService::FEEDBACK_SYMPTOMS);
        self::assertSame(0, $afterSymptoms['consecutiveGoodCount']);
        self::assertSame(ValRiskService::FEEDBACK_SYMPTOMS, $afterSymptoms['lastFeedback']);
        self::assertSame(ValRiskService::STATUS_PAUSED_DUE_TO_SYMPTOMS, $afterSymptoms['status']);
    }
}
