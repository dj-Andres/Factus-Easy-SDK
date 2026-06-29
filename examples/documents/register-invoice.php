<?php

/**
 * POST /api/document/register
 *
 * Registra una factura electrónica (tipo 01).
 *
 * Headers:
 *   Authorization: Bearer {token}
 *   Idempotency-Key: {uuid} (obligatorio)
 *
 * Request:
 *   ruc         string  required  RUC de la empresa (debe tener certificado)
 *   tipo        string  required  01 = factura
 *   id_externo  string  required  ID único del documento en tu sistema
 *   factura     array   required  Datos de la factura
 *   factura.fecha                string  date(d/m/Y)  Fecha de emisión (debe ser hoy)
 *   factura.establecimiento      string  3 dígitos
 *   factura.puntoEmision         string  3 dígitos
 *   factura.secuencial           string  9 dígitos
 *   factura.descuento            numeric
 *   factura.propina              numeric
 *   factura.total                numeric  Debe coincidir con suma de detalles
 *   factura.cliente              array
 *   detalles                     array   Items de la factura
 *   pagos                        array   Formas de pago
 *   notificaciones               array
 */

require __DIR__ . '/../config.php';

$factus = createClient();

$idempotencyKey = bin2hex(random_bytes(16));

$externalId = 'fact-' . date('Ymd') . '-' . time();

$payload = [
    'ruc' => '0791844433001',
    'tipo' => '01',
    'id_externo' => $externalId,
    'factura' => [
        'fecha' => date('d/m/Y'),
        'establecimiento' => '001',
        'puntoEmision' => '001',
        'secuencial' => '000000001',
        'descuento' => 0,
        'propina' => 0,
        'total' => 11.50,
        'cliente' => [
            'tipoIdentificacion' => '05',
            'documento' => '0707012605',
            'nombre' => 'Diego Jimenez',
            'correo' => 'andres96jimenez@gmail.com',
            'direccion'  => 'Av. Siempre Viva'
        ],
    ],
    'detalles' => [
        [
            'codigoPrincipal' => 'P001',
            'descripcion' => 'Producto de prueba',
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
    'pagos' => [
        [
            'formaPago' => '01',
            'total' => 11.50,
            'plazo' => 0,
            'unidadTiempo' => 'dias'
        ],
    ],
    'notificaciones' => [
        'email' => 'andres96jimenez@gmail.com',
        'webhook_url' => 'https://factuseasy.kreativesofts.com/admin/companies',
    ],
];

echo "Enviando factura...\n";
echo "  External ID: {$externalId}\n";
echo "  Idempotency: {$idempotencyKey}\n\n";

try {
    $response = $factus->document()->register($payload, $idempotencyKey);

    $doc = $response['data'] ?? [];

    echo "FACTURA REGISTRADA OK\n";
    echo "  Access Key: {$doc['access_key']}\n";
    echo "  Series:     {$doc['series']}\n";
    echo "  Sequential: {$doc['sequential']}\n";
    echo "  Status:     {$doc['document_status']}\n";
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
