<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class ImageService
{
    private const MAX_FILE_SIZE_BYTES = 10485760;
    private const MAX_IMAGES = 5;
    private const MAX_WIDTH = 1600;

    /** @var array<int, string> */
    private const ACCEPTED_TYPES = ['image/jpeg', 'image/png'];

    /**
     * @param array<int, array{name:string,type:string,tmp_name:string,error:int,size:int}> $files
     * @return array{ok:bool,message?:string}
     */
    public function validateUploadConstraints(array $files): array
    {
        $files = array_values(array_filter($files, static fn (array $file): bool => $file['error'] !== UPLOAD_ERR_NO_FILE));

        if (count($files) < 1) {
            return ['ok' => false, 'message' => 'Upload minimaal 1 foto.'];
        }

        if (count($files) > self::MAX_IMAGES) {
            return ['ok' => false, 'message' => 'Upload maximaal 5 foto\'s.'];
        }

        foreach ($files as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return ['ok' => false, 'message' => 'Uploadfout bij bestand: ' . $file['name'] . '.'];
            }

            if ($file['size'] > self::MAX_FILE_SIZE_BYTES) {
                return ['ok' => false, 'message' => 'Bestand te groot: ' . $file['name'] . '. Maximaal 10MB per foto.'];
            }

            $mime = $this->detectMimeType($file['tmp_name']);
            if (!in_array($mime, self::ACCEPTED_TYPES, true)) {
                return ['ok' => false, 'message' => 'Bestandstype niet toegestaan: ' . $file['name'] . '. Gebruik JPG of PNG.'];
            }
        }

        return ['ok' => true];
    }

    /**
     * @param array<int, array{name:string,type:string,tmp_name:string,error:int,size:int}> $files
     * @return array<int, array<string,mixed>>
     */
    public function processAndStoreUploads(array $files, StorageService $storage): array
    {
        $storage->ensureDirs();
        $output = [];

        foreach ($files as $file) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Uploadfout voor ' . $file['name']);
            }

            $mime = $this->detectMimeType($file['tmp_name']);
            if (!in_array($mime, self::ACCEPTED_TYPES, true)) {
                throw new RuntimeException('Bestandstype niet toegestaan voor ' . $file['name']);
            }

            $imageResource = $this->createImageResource($file['tmp_name'], $mime);
            if ($imageResource === null) {
                throw new RuntimeException('Kan afbeelding niet verwerken: ' . $file['name']);
            }

            $imageResource = $this->applyOrientation($imageResource, $file['tmp_name'], $mime);
            $resized = $this->resizeIfNeeded($imageResource, self::MAX_WIDTH);

            if ($resized !== $imageResource) {
                imagedestroy($imageResource);
                $imageResource = $resized;
            }

            $width = imagesx($imageResource);
            $height = imagesy($imageResource);

            $fileId = $this->uuidV4();
            $extension = $mime === 'image/png' ? 'png' : 'jpg';
            $targetPath = $storage->uploadImagePath($fileId, $extension);

            if ($mime === 'image/png') {
                imagealphablending($imageResource, false);
                imagesavealpha($imageResource, true);
                imagepng($imageResource, $targetPath, 9);
            } else {
                imagejpeg($imageResource, $targetPath, 82);
            }

            imagedestroy($imageResource);

            $record = [
                'id' => $fileId,
                'mime_type' => $mime,
                'path' => $targetPath,
                'size_bytes' => filesize($targetPath) ?: 0,
                'width' => $width,
                'height' => $height,
                'created_at' => gmdate('c'),
            ];

            $storage->saveUploadRecord($record);
            $output[] = $record;
        }

        return $output;
    }

    private function detectMimeType(string $path): string
    {
        if ($path === '' || !is_file($path) || !is_readable($path)) {
            return '';
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($path);

        if (!is_string($mime)) {
            return '';
        }

        return $mime;
    }

    private function createImageResource(string $path, string $mime): \GdImage|null
    {
        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path) ?: null,
            'image/png' => @imagecreatefrompng($path) ?: null,
            default => null,
        };
    }

    private function applyOrientation(\GdImage $image, string $path, string $mime): \GdImage
    {
        if ($mime !== 'image/jpeg' || !function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        if (!is_array($exif) || !isset($exif['Orientation'])) {
            return $image;
        }

        $orientation = (int) $exif['Orientation'];

        return match ($orientation) {
            3 => imagerotate($image, 180, 0) ?: $image,
            6 => imagerotate($image, -90, 0) ?: $image,
            8 => imagerotate($image, 90, 0) ?: $image,
            default => $image,
        };
    }

    private function resizeIfNeeded(\GdImage $image, int $maxWidth): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $maxWidth) {
            return $image;
        }

        $ratio = $maxWidth / $width;
        $newWidth = $maxWidth;
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $resized;
    }

    private function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
