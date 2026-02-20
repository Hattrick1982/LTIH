<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
