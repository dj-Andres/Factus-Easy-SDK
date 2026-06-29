<?php

/**
 * PUT /api/companie/update/{ruc}
 *
 * Actualiza los datos de una empresa existente.
 *
 * Request:
 *   ruc                   string  required  13 dígitos (debe coincidir con URL)
 *   name                  string  required
 *   business_name         string  required
 *   address               string  required
 *   phone                 string  required
 *   accounting_required   string  required  SI | YES | NO
 *   special_taxpayer      string  required  SI | YES | NO
 *   major_taxpayer        string  required  SI | YES | NO
 *   email                 string  required
 *
 * Response (201):
 *   status: "ok"
 *   data: { ruc, name, business_name, status, ... }
 */

require __DIR__ . '/../config.php';

$factus = createClient(
    email: 'tu-email@ejemplo.com',
    password: 'tu-contraseña',
);

try {
    $response = $factus->company()->update('1234567890001', [
        'ruc' => '1234567890001',
        'name' => 'Mi Empresa SDK - Actualizada',
        'business_name' => 'Mi Empresa SDK Cía. Ltda.',
        'address' => 'Av. Actualizada 456',
        'phone' => '0998888888',
        'accounting_required' => 'SI',
        'special_taxpayer' => 'NO',
        'major_taxpayer' => 'NO',
        'email' => 'actualizada-' . time() . '@ejemplo.com',
    ]);

    $c = $response['data'];

    echo "UPDATE COMPANY OK\n";
    echo "  RUC:          {$c['ruc']}\n";
    echo "  Name:         {$c['name']}\n";
    echo "  Address:      {$c['address']}\n";
    echo "  Phone:        {$c['phone']}\n";
} catch (FactusEasy\Sdk\Exceptions\ValidationException $e) {
    echo "VALIDATION ERROR\n";
    echo "  {$e->getMessage()}\n";
    foreach ($e->getErrors() as $field => $messages) {
        echo "  {$field}: " . implode(', ', (array) $messages) . "\n";
    }
} catch (Exception $e) {
    echo "ERROR\n";
    echo "  {$e->getMessage()}\n";
}
