<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    /** @var array<string, mixed> */
    private array $jsonCache = [];

    public function __construct(
        private readonly string $method,
        private readonly string $path,
        /** @var array<string, mixed> */
        private readonly array $query,
        private readonly string $rawBody,
        /** @var array<string, mixed> */
        private readonly array $files
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $query = $_GET;
        $rawBody = file_get_contents('php://input') ?: '';

        return new self($method, $path, $query, $rawBody, $_FILES);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    /** @return array<string, mixed> */
    public function queryAll(): array
    {
        return $this->query;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function json(): array
    {
        if ($this->jsonCache !== []) {
            return $this->jsonCache;
        }

        if ($this->rawBody === '') {
            return [];
        }

        $decoded = json_decode($this->rawBody, true);

        if (!is_array($decoded)) {
            return [];
        }

        $this->jsonCache = $decoded;
        return $this->jsonCache;
    }

    /**
     * @return array<int, array{name:string,type:string,tmp_name:string,error:int,size:int}>
     */
    public function files(string $key): array
    {
        if (!isset($this->files[$key])) {
            return [];
        }

        $entry = $this->files[$key];

        if (!is_array($entry) || !isset($entry['name'])) {
            return [];
        }

        if (!is_array($entry['name'])) {
            return [
                [
                    'name' => (string) ($entry['name'] ?? ''),
                    'type' => (string) ($entry['type'] ?? ''),
                    'tmp_name' => (string) ($entry['tmp_name'] ?? ''),
                    'error' => (int) ($entry['error'] ?? UPLOAD_ERR_NO_FILE),
                    'size' => (int) ($entry['size'] ?? 0),
                ],
            ];
        }

        $files = [];
        foreach ($entry['name'] as $index => $name) {
            $files[] = [
                'name' => (string) $name,
                'type' => (string) ($entry['type'][$index] ?? ''),
                'tmp_name' => (string) ($entry['tmp_name'][$index] ?? ''),
                'error' => (int) ($entry['error'][$index] ?? UPLOAD_ERR_NO_FILE),
                'size' => (int) ($entry['size'][$index] ?? 0),
            ];
        }

        return $files;
    }
}
