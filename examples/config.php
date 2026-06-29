<?php

require __DIR__ . '/../vendor/autoload.php';

use FactusEasy\Sdk\FactusEasy;

function createClient(?string $email = null, ?string $password = null): FactusEasy
{
    $factus = new FactusEasy([
        'base_url' => 'https://factuseasy.kreativesofts.com',
    ]);

    if ($email !== null && $password !== null) {
        $factus->auth()->login($email, $password);
    }

    return $factus;
}
