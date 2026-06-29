<?php

use FactusEasy\Sdk\Exceptions\ValidationException;

/**
 * POST /api/document/register
 *
 * Registra una retención electrónica (tipo 07).
 *
 * Request:
 *   ruc         string  required  RUC de la empresa
 *   tipo        string  required  07 = retención
 *   id_externo  string  required
 *   retencion   array   required  Datos de la retención
 *   retencion.fecha                    string  date(d/m/Y)
 *   retencion.establecimiento          string  3 dígitos
 *   retencion.puntoEmision             string  3 dígitos
 *   retencion.secuencial               string  9 dígitos
 *   retencion.periodoFiscal            string  m/Y (ej: 06/2026)
 *   retencion.sujetoRetenido           array
 *   retencion.sujetoRetenido.tipoIdentificacion  string  (04, 05, 06, 07, 08)
 *   retencion.sujetoRetenido.documento           string
 *   retencion.sujetoRetenido.nombre              string
 *   retencion.total                    numeric
 *   detalles                     array   Items de retención
 *   detalles.*.codigo            integer   1, 2 o 6
 *   detalles.*.codigoRetencion   integer   Debe existir en retention_configs
 *   detalles.*.baseImponible     numeric
 *   detalles.*.porcentajeRetener numeric   Entre 0 y 100
 *   detalles.*.valorRetenido     numeric
 *   detalles.*.codDocSustento    string    01, 04, 06, 07
 *   detalles.*.numDocSustento    string    Formato 001-001-000000001
 *   detalles.*.fechaEmisionSustento string  date(d/m/Y)
 *   notificaciones               array
 */

require __DIR__.'/../config.php';

$factus = createClient();

$idempotencyKey = bin2hex(random_bytes(16));

$externalId = 'ret-'.date('Ymd').'-'.time();

$payload = [
    'ruc' => '1234567890001',
    'tipo' => '07',
    'id_externo' => $externalId,
    'retencion' => [
        'fecha' => date('d/m/Y'),
        'establecimiento' => '001',
        'puntoEmision' => '001',
        'secuencial' => '000000001',
        'periodoFiscal' => date('m/Y'),
        'sujetoRetenido' => [
            'tipoIdentificacion' => '04',
            'documento' => '1790012345001',
            'nombre' => 'Proveedor de Prueba',
        ],
        'total' => 100.00,
    ],
    'detalles' => [
        [
            'codigo' => 1,
            'codigoRetencion' => 303,
            'baseImponible' => 100.00,
            'porcentajeRetener' => 1.00,
            'valorRetenido' => 1.00,
            'codDocSustento' => '01',
            'numDocSustento' => '001-001-000000001',
            'fechaEmisionSustento' => date('d/m/Y'),
        ],
    ],
    'notificaciones' => [
        'email' => null,
        'webhook_url' => null,
    ],
];

echo "Enviando retención...\n";
echo "  External ID: {$externalId}\n";
echo "  Idempotency: {$idempotencyKey}\n\n";

try {
    $response = $factus->document()->register($payload, $idempotencyKey);

    $doc = $response['data'] ?? [];

    echo "RETENCIÓN REGISTRADA OK\n";
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
