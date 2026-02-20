<?php

declare(strict_types=1);

namespace App\Core;

final class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            if (preg_match('/^(["\']).*\1$/', $value)) {
                $value = substr($value, 1, -1);
            }

            if (getenv($key) === false) {
                putenv("{$key}={$value}");
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);

        if ($value === false || $value === '') {
            return $default;
        }

        return $value;
    }

    public static function int(string $key, int $default): int
    {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        return (int) $value;
    }

    public static function bool(string $key, bool $default): bool
    {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
