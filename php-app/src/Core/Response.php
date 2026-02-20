<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    /** @param array<string, string> $headers */
    public function __construct(
        private readonly string $body,
        private readonly int $status = 200,
        private readonly array $headers = []
    ) {
    }

    /** @param array<string, mixed> $payload */
    public static function json(array $payload, int $status = 200): self
    {
        return new self(
            (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $status,
            ['Content-Type' => 'application/json; charset=utf-8']
        );
    }

    public static function html(string $html, int $status = 200): self
    {
        return new self($html, $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function redirect(string $to, int $status = 302): self
    {
        return new self('', $status, ['Location' => $to]);
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value);
        }

        echo $this->body;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function body(): string
    {
        return $this->body;
    }
}
