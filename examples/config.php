<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use FactusEasy\Sdk\FactusEasy;

function createClient(?string $email = null, ?string $password = null): FactusEasy
{
    $envFile = __DIR__ . '/../.env';

    if (file_exists($envFile)) {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
    }

    $factus = new FactusEasy();

    $email ??= $_ENV['FACTUS_EASY_EMAIL'] ?? null;
    $password ??= $_ENV['FACTUS_EASY_PASSWORD'] ?? null;

    if ($email !== null && $password !== null) {
        $factus->auth()->login($email, $password);
    }

    return $factus;
}
