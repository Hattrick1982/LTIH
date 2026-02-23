<?php

declare(strict_types=1);

namespace Tests;

use App\Services\ImageService;
use PHPUnit\Framework\TestCase;

final class UploadValidationTest extends TestCase
{
    private ImageService $imageService;

    protected function setUp(): void
    {
        $this->imageService = new ImageService();
    }

    public function testRejectsUnsupportedFileType(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upload-test-');
        file_put_contents($tmp, 'not-an-image');

        $files = [[
            'name' => 'test.webp',
            'type' => 'image/webp',
            'tmp_name' => $tmp,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tmp) ?: 0,
        ]];

        $result = $this->imageService->validateUploadConstraints($files);

        self::assertFalse($result['ok']);

        @unlink($tmp);
    }

    public function testRejectsFileLargerThanTenMb(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upload-big-');
        file_put_contents($tmp, str_repeat('a', 1024));

        $files = [[
            'name' => 'large.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $tmp,
            'error' => UPLOAD_ERR_OK,
            'size' => 11 * 1024 * 1024,
        ]];

        $result = $this->imageService->validateUploadConstraints($files);

        self::assertFalse($result['ok']);

        @unlink($tmp);
    }
}
