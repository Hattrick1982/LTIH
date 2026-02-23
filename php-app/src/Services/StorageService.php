<?php

declare(strict_types=1);

namespace App\Services;

final class StorageService
{
    public function __construct(
        private readonly string $basePath,
        private readonly int $ttlHours = 24
    ) {
    }

    /** @return array{base:string,uploads:string,assessments:string} */
    public function ensureDirs(): array
    {
        $paths = $this->paths();

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0775, true);
            }
        }

        return $paths;
    }

    /** @return array{base:string,uploads:string,assessments:string} */
    public function paths(): array
    {
        $base = rtrim($this->basePath, '/');

        return [
            'base' => $base,
            'uploads' => $base . '/uploads',
            'assessments' => $base . '/assessments',
        ];
    }

    /** @param array<string,mixed> $record */
    public function saveUploadRecord(array $record): void
    {
        $this->ensureDirs();
        file_put_contents($this->uploadMetadataPath((string) $record['id']), json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /** @return array<string,mixed>|null */
    public function readUploadRecord(string $fileId): ?array
    {
        $this->ensureDirs();
        $path = $this->uploadMetadataPath($fileId);

        if (!is_file($path)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($path), true);
        return is_array($data) ? $data : null;
    }

    /** @param array<string,mixed> $record */
    public function saveAssessmentRecord(array $record): void
    {
        $this->ensureDirs();
        file_put_contents($this->assessmentPath((string) $record['assessment_id']), json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /** @return array<string,mixed>|null */
    public function readAssessmentRecord(string $assessmentId): ?array
    {
        $this->ensureDirs();
        $path = $this->assessmentPath($assessmentId);

        if (!is_file($path)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($path), true);
        return is_array($data) ? $data : null;
    }

    public function deleteAssessmentAndFiles(string $assessmentId): bool
    {
        $record = $this->readAssessmentRecord($assessmentId);

        if ($record === null) {
            return false;
        }

        $fileIds = $record['file_ids'] ?? [];
        if (is_array($fileIds)) {
            foreach ($fileIds as $fileId) {
                if (is_string($fileId)) {
                    $this->deleteUploadRecordAndImage($fileId);
                }
            }
        }

        @unlink($this->assessmentPath($assessmentId));

        return true;
    }

    public function deleteUploadRecordAndImage(string $fileId): void
    {
        $record = $this->readUploadRecord($fileId);

        if ($record !== null) {
            $imagePath = $record['path'] ?? '';
            if (is_string($imagePath) && $imagePath !== '') {
                @unlink($imagePath);
            }
        }

        @unlink($this->uploadMetadataPath($fileId));
    }

    /** @return array{assessments_deleted:int,uploads_deleted:int} */
    public function cleanupExpired(): array
    {
        $this->ensureDirs();

        $deletedAssessments = 0;
        $deletedUploads = 0;
        $maxAgeSeconds = max(1, $this->ttlHours) * 3600;
        $now = time();

        foreach (glob($this->paths()['assessments'] . '/*.json') ?: [] as $assessmentMeta) {
            $data = json_decode((string) file_get_contents($assessmentMeta), true);
            if (!is_array($data)) {
                continue;
            }

            $createdAt = strtotime((string) ($data['created_at'] ?? ''));
            if ($createdAt === false || ($now - $createdAt) <= $maxAgeSeconds) {
                continue;
            }

            $assessmentId = (string) ($data['assessment_id'] ?? '');
            if ($assessmentId !== '' && $this->deleteAssessmentAndFiles($assessmentId)) {
                $deletedAssessments++;
            }
        }

        foreach (glob($this->paths()['uploads'] . '/*.json') ?: [] as $uploadMeta) {
            $data = json_decode((string) file_get_contents($uploadMeta), true);
            if (!is_array($data)) {
                continue;
            }

            $createdAt = strtotime((string) ($data['created_at'] ?? ''));
            if ($createdAt === false || ($now - $createdAt) <= $maxAgeSeconds) {
                continue;
            }

            $fileId = (string) ($data['id'] ?? '');
            if ($fileId !== '') {
                $this->deleteUploadRecordAndImage($fileId);
                $deletedUploads++;
            }
        }

        return [
            'assessments_deleted' => $deletedAssessments,
            'uploads_deleted' => $deletedUploads,
        ];
    }

    public function uploadImagePath(string $fileId, string $extension): string
    {
        $this->ensureDirs();
        return $this->paths()['uploads'] . '/' . $fileId . '.' . $extension;
    }

    private function uploadMetadataPath(string $fileId): string
    {
        return $this->paths()['uploads'] . '/' . $fileId . '.json';
    }

    private function assessmentPath(string $assessmentId): string
    {
        return $this->paths()['assessments'] . '/' . $assessmentId . '.json';
    }
}
