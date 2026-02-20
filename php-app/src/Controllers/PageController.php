<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Domain\Presentation;
use App\Domain\RoomConfig;
use App\Services\StorageService;

final class PageController
{
    public function __construct(
        private readonly View $view,
        private readonly StorageService $storage,
        private readonly string $mainSiteUrl
    ) {
    }

    public function home(Request $request): Response
    {
        return $this->render('home', $request, ['title' => 'Start je foto-assessment']);
    }

    public function assessmentLanding(Request $request): Response
    {
        return $this->render('assessment_landing', $request, ['title' => 'Start foto-assessment']);
    }

    public function newAssessment(Request $request): Response
    {
        $room = (string) $request->query('room', '');
        $roomConfig = RoomConfig::one($room);

        if ($roomConfig === null) {
            return $this->render('assessment_invalid_room', $request, [
                'title' => 'Ongeldige ruimtekeuze',
            ], 400);
        }

        return $this->render('assessment_new', $request, [
            'title' => $roomConfig['title'],
            'roomType' => $room,
            'roomConfig' => $roomConfig,
        ]);
    }

    public function roomAssessment(Request $request, string $roomType): Response
    {
        $roomConfig = RoomConfig::one($roomType);

        if ($roomConfig === null) {
            return $this->notFound($request);
        }

        return $this->render('assessment_new', $request, [
            'title' => $roomConfig['title'],
            'roomType' => $roomType,
            'roomConfig' => $roomConfig,
        ]);
    }

    public function result(Request $request, string $assessmentId): Response
    {
        $record = $this->storage->readAssessmentRecord($assessmentId);

        if ($record === null) {
            return $this->notFound($request);
        }

        $result = $record['result'] ?? [];
        if (!is_array($result)) {
            return $this->notFound($request);
        }

        $risk = Presentation::riskLabel((int) ($result['overall_risk_score_0_100'] ?? 0));
        $hazards = is_array($result['hazards'] ?? null) ? $result['hazards'] : [];

        usort($hazards, static function (array $a, array $b): int {
            $left = ((int) ($a['severity_1_5'] ?? 0)) * ((float) ($a['confidence_0_1'] ?? 0));
            $right = ((int) ($b['severity_1_5'] ?? 0)) * ((float) ($b['confidence_0_1'] ?? 0));
            return $right <=> $left;
        });

        $topIssues = array_slice($hazards, 0, 5);
        $actionPlan = Presentation::buildActionPlan($hazards);

        return $this->render('assessment_result', $request, [
            'title' => 'Jouw woonveiligheidsresultaat',
            'assessmentId' => $assessmentId,
            'record' => $record,
            'result' => $result,
            'risk' => $risk,
            'topIssues' => $topIssues,
            'actionPlan' => $actionPlan,
            'disclaimerParagraphs' => Presentation::DISCLAIMER_PARAGRAPHS,
        ]);
    }

    public function print(Request $request, string $assessmentId): Response
    {
        $record = $this->storage->readAssessmentRecord($assessmentId);

        if ($record === null) {
            return $this->notFound($request);
        }

        $result = $record['result'] ?? [];
        if (!is_array($result)) {
            return $this->notFound($request);
        }

        return $this->render('assessment_print', $request, [
            'title' => 'Checklist woonveiligheid',
            'assessmentId' => $assessmentId,
            'record' => $record,
            'result' => $result,
            'risk' => Presentation::riskLabel((int) ($result['overall_risk_score_0_100'] ?? 0)),
            'disclaimerParagraphs' => Presentation::DISCLAIMER_PARAGRAPHS,
        ]);
    }

    public function contact(Request $request): Response
    {
        return $this->render('contact', $request, ['title' => 'Plan adviesgesprek']);
    }

    public function notFound(Request $request): Response
    {
        return $this->render('not_found', $request, ['title' => 'Pagina niet gevonden'], 404);
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
}
