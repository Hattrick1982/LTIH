<?php

declare(strict_types=1);

use App\Controllers\ApiAssessmentController;
use App\Controllers\PageController;
use App\Controllers\ValRiskController;
use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\View;
use App\Services\AssessmentService;
use App\Services\ImageService;
use App\Services\OpenAIClient;
use App\Services\StorageService;
use App\Services\ValRiskService;

require __DIR__ . '/../src/bootstrap.php';

ini_set('display_errors', '0');
ini_set('html_errors', '0');
ini_set('max_execution_time', '120');
ini_set('memory_limit', '512M');
error_reporting(E_ALL);
set_time_limit(120);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH) ?: '/';
$isApiRequest = str_starts_with($requestPath, '/api/');

set_exception_handler(static function (Throwable $exception) use ($isApiRequest): void {
    error_log('Uncaught exception: ' . $exception->getMessage());

    if ($isApiRequest) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Interne serverfout. Controleer serverlogs.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    http_response_code(500);
    echo 'Interne serverfout.';
});

register_shutdown_function(static function () use ($isApiRequest): void {
    $error = error_get_last();

    if (!is_array($error)) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($error['type'] ?? 0, $fatalTypes, true)) {
        return;
    }

    error_log('Fatal shutdown error: ' . ($error['message'] ?? 'unknown'));

    if ($isApiRequest && !headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Fatale serverfout. Controleer serverlogs.'], JSON_UNESCAPED_UNICODE);
    }
});

$mainSiteUrl = Env::get('MAIN_SITE_URL', 'https://langerthuisinhuis.nl') ?? 'https://langerthuisinhuis.nl';
if (!str_starts_with($mainSiteUrl, 'http://') && !str_starts_with($mainSiteUrl, 'https://')) {
    $mainSiteUrl = 'https://' . $mainSiteUrl;
}

$tempStorage = Env::get('TEMP_STORAGE_PATH', sys_get_temp_dir() . '/ltih-assessment') ?? (sys_get_temp_dir() . '/ltih-assessment');
$ttlHours = Env::int('ASSESSMENT_TTL_HOURS', 24);
$openAiApiKey = Env::get('OPENAI_API_KEY', '') ?? '';
$openAiModel = Env::get('OPENAI_MODEL', 'gpt-5.2') ?? 'gpt-5.2';
$openAiTimeout = Env::int('OPENAI_TIMEOUT_SECONDS', 45);
$openAiRetries = Env::int('OPENAI_RETRIES', 2);
$supportPhone = Env::get('SUPPORT_PHONE', '') ?? '';

$storage = new StorageService($tempStorage, $ttlHours);
$imageService = new ImageService();
$openAiClient = new OpenAIClient($openAiApiKey, $openAiModel, $openAiTimeout, $openAiRetries);
$assessmentService = new AssessmentService($openAiClient);
$valRiskService = new ValRiskService();
$view = new View(__DIR__ . '/../views');

$pageController = new PageController($view, $storage, $mainSiteUrl);
$apiController = new ApiAssessmentController($storage, $imageService, $assessmentService);
$valRiskController = new ValRiskController($view, $mainSiteUrl, $supportPhone, $valRiskService);

$router = new Router();

$router->get('/', fn (Request $request): Response => $pageController->home($request));
$router->get('/assessment', fn (Request $request): Response => $pageController->assessmentLanding($request));
$router->get('/assessment/new', fn (Request $request): Response => $pageController->newAssessment($request));
$router->get('/assessment/result/{assessmentId}', fn (Request $request, array $params): Response => $pageController->result($request, (string) $params['assessmentId']));
$router->get('/assessment/result/{assessmentId}/print', fn (Request $request, array $params): Response => $pageController->print($request, (string) $params['assessmentId']));
$router->get('/woonkamer', fn (Request $request): Response => $pageController->roomAssessment($request, 'living_room'));
$router->get('/slaapkamer', fn (Request $request): Response => $pageController->roomAssessment($request, 'bedroom'));
$router->get('/keuken', fn (Request $request): Response => $pageController->roomAssessment($request, 'kitchen'));
$router->get('/contact', fn (Request $request): Response => $pageController->contact($request));
// Valrisico module
$router->get('/valrisico', fn (Request $request): Response => $valRiskController->welcome($request));
$router->get('/valrisico/uitleg', fn (Request $request): Response => $valRiskController->howItWorks($request));
$router->get('/valrisico/stap/{step}', fn (Request $request, array $params): Response => $valRiskController->step($request, (int) $params['step']));
$router->post('/valrisico/antwoord', fn (Request $request): Response => $valRiskController->submitAnswer($request));
$router->get('/valrisico/looptest', fn (Request $request): Response => $valRiskController->looptest($request));
$router->post('/valrisico/looptest', fn (Request $request): Response => $valRiskController->submitLooptest($request));
$router->get('/valrisico/resultaat', fn (Request $request): Response => $valRiskController->result($request));
$router->get('/valrisico/oefeningen', fn (Request $request): Response => $valRiskController->exercises($request));
$router->get('/valrisico/oefeningen/{slug}', fn (Request $request, array $params): Response => $valRiskController->exerciseDetail($request, (string) $params['slug']));
$router->post('/valrisico/reset', fn (Request $request): Response => $valRiskController->reset($request));
$router->get('/valrisico/print', fn (Request $request): Response => $valRiskController->printSummary($request));

$router->post('/api/assessment/upload', fn (Request $request): Response => $apiController->upload($request));
$router->post('/api/assessment/analyze', fn (Request $request): Response => $apiController->analyze($request));
$router->get('/api/assessment/{assessmentId}', fn (Request $request, array $params): Response => $apiController->getAssessment((string) $params['assessmentId']));
$router->delete('/api/assessment/{assessmentId}', fn (Request $request, array $params): Response => $apiController->deleteAssessment((string) $params['assessmentId']));
$router->post('/api/assessment/{assessmentId}/delete', fn (Request $request, array $params): Response => $apiController->deleteAssessment((string) $params['assessmentId']));
$router->get('/api/assessment/{assessmentId}/checklist.pdf', fn (Request $request, array $params): Response => $apiController->checklistPdfRedirect((string) $params['assessmentId']));

$router->setNotFoundHandler(fn (Request $request): Response => $pageController->notFound($request));

$request = Request::fromGlobals();
$response = $router->dispatch($request);
$response->send();
