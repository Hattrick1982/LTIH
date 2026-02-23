<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Domain\AssessmentSchema;
use App\Services\AssessmentAnalyzerInterface;
use App\Services\ImageService;
use App\Services\StorageService;
use Throwable;

final class ApiAssessmentController
{
    public function __construct(
        private readonly StorageService $storage,
        private readonly ImageService $imageService,
        private readonly AssessmentAnalyzerInterface $assessmentService
    ) {
    }

    public function upload(Request $request): Response
    {
        try {
            $files = $request->files('files');
            if ($files === []) {
                $files = $request->files('files[]');
            }
            $validation = $this->imageService->validateUploadConstraints($files);

            if (!($validation['ok'] ?? false)) {
                return Response::json(['error' => $validation['message'] ?? 'Ongeldige upload.'], 400);
            }

            $uploads = $this->imageService->processAndStoreUploads($files, $this->storage);

            return Response::json([
                'files' => array_map(static fn (array $upload): array => [
                    'file_id' => $upload['id'],
                    'mime_type' => $upload['mime_type'],
                    'width' => $upload['width'],
                    'height' => $upload['height'],
                    'size_bytes' => $upload['size_bytes'],
                ], $uploads),
            ]);
        } catch (Throwable $error) {
            return Response::json(['error' => $error->getMessage()], 500);
        }
    }

    public function analyze(Request $request): Response
    {
        try {
            $payload = $request->json();
            $parsed = AssessmentSchema::validateAnalyzeRequest($payload);

            if (!($parsed['ok'] ?? false)) {
                return Response::json([
                    'error' => $parsed['message'] ?? 'Ongeldige input voor analyse.',
                    'details' => $parsed['details'] ?? [],
                ], 400);
            }

            /** @var array<string,mixed> $safe */
            $safe = $parsed['data'];

            $uploadRecords = [];
            foreach ($safe['file_ids'] as $fileId) {
                $uploadRecord = $this->storage->readUploadRecord((string) $fileId);
                if ($uploadRecord === null) {
                    return Response::json(['error' => 'Een of meer geuploade foto\'s zijn niet gevonden.'], 404);
                }
                $uploadRecords[] = $uploadRecord;
            }

            $result = $this->assessmentService->analyze((string) $safe['room_type'], $uploadRecords);

            $assessmentId = $this->uuidV4();
            $record = [
                'assessment_id' => $assessmentId,
                'created_at' => gmdate('c'),
                'file_ids' => $safe['file_ids'],
                'result' => $result,
            ];

            $this->storage->saveAssessmentRecord($record);

            return Response::json(array_merge([
                'assessment_id' => $assessmentId,
            ], $result));
        } catch (Throwable $error) {
            return Response::json([
                'error' => $error->getMessage(),
                'retryable' => true,
            ], 500);
        }
    }

    public function getAssessment(string $assessmentId): Response
    {
        $assessment = $this->storage->readAssessmentRecord($assessmentId);

        if ($assessment === null) {
            return Response::json(['error' => 'Assessment niet gevonden.'], 404);
        }

        return Response::json($assessment);
    }

    public function deleteAssessment(string $assessmentId): Response
    {
        $deleted = $this->storage->deleteAssessmentAndFiles($assessmentId);

        if (!$deleted) {
            return Response::json(['error' => 'Assessment niet gevonden.'], 404);
        }

        return Response::json(['ok' => true]);
    }

    public function checklistPdfRedirect(string $assessmentId): Response
    {
        return Response::redirect('/assessment/result/' . rawurlencode($assessmentId) . '/print');
    }

    private function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
