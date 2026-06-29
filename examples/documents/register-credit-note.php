<?php

use FactusEasy\Sdk\Exceptions\ValidationException;

/**
 * POST /api/document/register
 *
 * Registra una nota de crédito (tipo 04).
 *
 * Nota: Requiere que exista una factura autorizada previamente.
 * Los datos de docModificado deben coincidir con la factura original.
 *
 * Request:
 *   ruc         string  required  RUC de la empresa
 *   tipo        string  required  04 = nota de crédito
 *   id_externo  string  required
 *   nota        array   required  Datos de la nota de crédito
 *   nota.fecha                    string  date(d/m/Y)
 *   nota.establecimiento          string  3 dígitos
 *   nota.puntoEmision             string  3 dígitos
 *   nota.secuencial               string  9 dígitos
 *   nota.tipo                     string  Tipo de nota (ej: "1", "2", etc.)
 *   nota.docModificado            array   Factura que se modifica
 *   nota.docModificado.tipo               string  (01, 03, 05, 06, 07)
 *   nota.docModificado.numero             string  001-001-000000001
 *   nota.docModificado.fechaEmision       string  date(d/m/Y)
 *   nota.docModificado.claveAutorizacion  string  49 dígitos
 *   detalles                     array   Items modificados
 *   notificaciones               array
 */

require __DIR__.'/../config.php';

$factus = createClient();

$idempotencyKey = bin2hex(random_bytes(16));

$externalId = 'nc-'.date('Ymd').'-'.time();

$payload = [
    'ruc' => '1234567890001',
    'tipo' => '04',
    'id_externo' => $externalId,
    'nota' => [
        'fecha' => date('d/m/Y'),
        'establecimiento' => '001',
        'puntoEmision' => '001',
        'secuencial' => '000000001',
        'tipo' => '1',
        'docModificado' => [
            'tipo' => '01',
            'numero' => '001-001-000000001',
            'fechaEmision' => date('d/m/Y'),
            'claveAutorizacion' => '1234567890123456789012345678901234567890123456789',
        ],
    ],
    'detalles' => [
        [
            'codigoPrincipal' => 'P001',
            'descripcion' => 'Producto de prueba - Nota de crédito',
            'cantidad' => 1,
            'precioUnitario' => 10.00,
            'descuento' => 0,
            'precioTotalSinImpuesto' => 10.00,
            'impuestos' => [
                [
                    'codigo' => '2',
                    'codigoPorcentaje' => '4',
                    'tarifa' => 15.00,
                    'baseImponible' => 10.00,
                    'valor' => 1.50,
                ],
            ],
        ],
    ],
    'notificaciones' => [
        'email' => null,
        'webhook_url' => null,
    ],
];

echo "Enviando nota de crédito...\n";
echo "  External ID: {$externalId}\n";
echo "  Idempotency: {$idempotencyKey}\n\n";

try {
    $response = $factus->document()->register($payload, $idempotencyKey);

    $doc = $response['data'] ?? [];

    echo "NOTA DE CRÉDITO REGISTRADA OK\n";
    echo "  Access Key: {$doc['access_key']}\n";
    echo "  Series:     {$doc['series']}\n";
    echo "  Sequential: {$doc['sequential']}\n";
    echo "  Status:     {$doc['document_status']}\n";
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
