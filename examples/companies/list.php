<?php

/**
 * GET /api/companie/list
 *
 * Lista todas las empresas del usuario autenticado.
 *
 * Headers:
 *   Authorization: Bearer {token}
 *
 * Response (200):
 *   status: "ok"
 *   data: [ { ruc, name, business_name, address, phone, email, status, ... } ]
 */

require __DIR__ . '/../config.php';

$factus = createClient();

try {
    $response = $factus->company()->list();
    $companies = $response['data'] ?? [];

    echo "LIST COMPANIES OK\n";
    echo "  Total: " . count($companies) . "\n";

    foreach ($companies as $c) {
        echo "\n";
        echo "  RUC:      {$c['ruc']}\n";
        echo "  Name:     {$c['name']}\n";
        echo "  Status:   {$c['status']}\n";
        echo "  Email:    {$c['email']}\n";
        echo "  Phone:    {$c['phone']}\n";
    }
} catch (Exception $e) {
    echo "ERROR\n";
    echo "  {$e->getMessage()}\n";
}
