#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Env;
use App\Services\StorageService;

require __DIR__ . '/../src/bootstrap.php';

$tempStorage = Env::get('TEMP_STORAGE_PATH', sys_get_temp_dir() . '/ltih-assessment') ?? (sys_get_temp_dir() . '/ltih-assessment');
$ttlHours = Env::int('ASSESSMENT_TTL_HOURS', 24);

$storage = new StorageService($tempStorage, $ttlHours);
$result = $storage->cleanupExpired();

echo 'Cleanup voltooid. assessments verwijderd: ' . $result['assessments_deleted'] . ', uploads verwijderd: ' . $result['uploads_deleted'] . PHP_EOL;
