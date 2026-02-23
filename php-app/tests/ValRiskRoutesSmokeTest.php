<?php

declare(strict_types=1);

namespace Tests;

use App\Controllers\ValRiskController;
use App\Core\Request;
use App\Core\Router;
use App\Core\View;
use App\Services\ValRiskService;
use PHPUnit\Framework\TestCase;

final class ValRiskRoutesSmokeTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];

        $view = new View(__DIR__ . '/../views');
        $controller = new ValRiskController($view, 'https://langerthuisinhuis.nl', '', new ValRiskService());

        $router = new Router();
        $router->get('/valrisico', fn (Request $request) => $controller->welcome($request));
        $router->get('/valrisico/stap/{step}', fn (Request $request, array $params) => $controller->step($request, (int) $params['step']));
        $router->post('/valrisico/antwoord', fn (Request $request) => $controller->submitAnswer($request));
        $router->get('/valrisico/resultaat', fn (Request $request) => $controller->result($request));
        $router->get('/valrisico/oefeningen', fn (Request $request) => $controller->exercises($request));

        $this->router = $router;
    }

    public function testWelcomeRouteRendersSuccessfully(): void
    {
        $response = $this->router->dispatch(new Request('GET', '/valrisico', [], '', []));

        self::assertSame(200, $response->status());
        self::assertStringContainsString('Valpreventie check', $response->body());
    }

    public function testStepRouteRendersQuestion(): void
    {
        $_SESSION['valrisico'] = [
            'caregiver_mode' => false,
            'language' => 'nl',
            'answers' => [],
            'risk_factors' => [],
            'looptest' => ['assist_mode' => null, 'seconds' => null, 'skipped' => false],
            'result' => ['preliminary' => null, 'final' => null, 'computed_at' => null],
            'exercise_feedback' => null,
        ];

        $response = $this->router->dispatch(new Request('GET', '/valrisico/stap/1', [], '', []));

        self::assertSame(200, $response->status());
        self::assertStringContainsString('Waarom vragen we dit?', $response->body());
    }

    public function testStartActionRedirectsToFirstStep(): void
    {
        $_POST = [
            'action' => 'start',
            'language' => 'nl',
            'caregiver_mode' => '1',
        ];

        $response = $this->router->dispatch(new Request('POST', '/valrisico/antwoord', [], '', []));

        self::assertSame(302, $response->status());
    }

    public function testResultRouteWithoutDataRedirectsToWelcome(): void
    {
        $response = $this->router->dispatch(new Request('GET', '/valrisico/resultaat', [], '', []));

        self::assertSame(302, $response->status());
    }

    public function testExercisesRouteRendersSuccessfully(): void
    {
        $response = $this->router->dispatch(new Request('GET', '/valrisico/oefeningen', [], '', []));

        self::assertSame(200, $response->status());
        self::assertStringContainsString('Oefeningen voor thuis', $response->body());
    }

    public function testSessionFeedbackActionStoresFeedbackAndRedirects(): void
    {
        $_POST = [
            'action' => 'session_feedback',
            'feedback' => 'good',
        ];

        $response = $this->router->dispatch(new Request('POST', '/valrisico/antwoord', [], '', []));

        self::assertSame(302, $response->status());
        self::assertSame('good', $_SESSION['valrisico']['exercise_feedback'] ?? null);
    }
}
