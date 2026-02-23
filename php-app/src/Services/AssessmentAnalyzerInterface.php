<?php

declare(strict_types=1);

namespace App\Services;

interface AssessmentAnalyzerInterface
{
    /**
     * @param array<int, array<string,mixed>> $uploads
     * @return array<string,mixed>
     */
    public function analyze(string $roomType, array $uploads): array;
}
