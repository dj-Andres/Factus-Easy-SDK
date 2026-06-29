<?php

use FactusEasy\Sdk\Exceptions\ValidationException;

/**
 * POST /api/companie/register
 *
 * Crea una nueva empresa.
 *
 * Request:
 *   ruc                   string  required  13 dígitos
 *   name                  string  required  Nombre comercial
 *   business_name         string  required  Razón social
 *   address               string  required  Dirección
 *   phone                 string  required  Teléfono
 *   accounting_required   string  required  SI | YES | NO
 *   special_taxpayer      string  required  SI | YES | NO
 *   special_taxpayer_number string nullable  Requerido si special_taxpayer=SI
 *   major_taxpayer        string  required  SI | YES | NO
 *   sri_resolution_code   string  nullable  Requerido si major_taxpayer=SI
 *   email                 string  required  Correo (único)
 *
 * Response (201):
 *   status: "ok"
 *   data: { ruc, name, business_name, status, ... }
 */

require __DIR__.'/../config.php';

$factus = createClient();

try {
    $response = $factus->company()->create([
        'ruc' => '1234567890001',
        'name' => 'Mi Empresa SDK',
        'business_name' => 'Mi Empresa SDK Cía. Ltda.',
        'address' => 'Av. Principal 123',
        'phone' => '0999999999',
        'accounting_required' => 'SI',
        'special_taxpayer' => 'NO',
        'major_taxpayer' => 'NO',
        'email' => 'empresa-'.time().'@ejemplo.com',
    ]);

    $c = $response['data'];

    echo "CREATE COMPANY OK\n";
    echo "  RUC:          {$c['ruc']}\n";
    echo "  Name:         {$c['name']}\n";
    echo "  BusinessName: {$c['business_name']}\n";
    echo "  Status:       {$c['status']}\n";
    echo "  Email:        {$c['email']}\n";
} catch (ValidationException $e) {
    echo "VALIDATION ERROR\n";
    echo "  {$e->getMessage()}\n";
    foreach ($e->getErrors() as $field => $messages) {
        echo "  {$field}: ".implode(', ', (array) $messages)."\n";
    }
} catch (Exception $e) {
    echo "ERROR\n";
    echo "  {$e->getMessage()}\n";
}
