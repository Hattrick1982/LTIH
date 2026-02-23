<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public function __construct(
        private readonly string $basePath
    ) {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): string
    {
        $content = $this->renderFile($this->basePath . '/pages/' . $template . '.php', $data);

        return $this->renderFile($this->basePath . '/layout.php', array_merge($data, [
            'content' => $content,
        ]));
    }

    /** @param array<string, mixed> $data */
    private function renderFile(string $file, array $data): string
    {
        if (!is_file($file)) {
            throw new \RuntimeException('Template niet gevonden: ' . $file);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        $output = ob_get_clean();

        return $output === false ? '' : $output;
    }
}
