<?php

use FactusEasy\Sdk\Exceptions\ValidationException;

/**
 * POST /api/document/batch/register
 *
 * Registra múltiples documentos en un solo lote (máx 50).
 * Todos los documentos deben ser del mismo tipo (01, 04 o 07).
 *
 * Headers:
 *   Authorization: Bearer {token}
 *   Idempotency-Key: {uuid} (obligatorio)
 *
 * Request:
 *   ruc         string   required
 *   tipo        string   required  01, 04 o 07
 *   documentos  array[]  required  Lista de documentos (máx 50)
 *   documentos.*.id_externo  string  required
 *   documentos.*.factura/nota/retencion  array  Según el tipo
 *   documentos.*.detalles     array
 *   documentos.*.pagos        array  (solo tipo 01)
 *   documentos.*.notificaciones  array
 *
 * Response (201):
 *   data.batch_id:       ID del lote
 *   data.batch_key:      Clave del lote
 *   data.documents_count: Cantidad de documentos
 *   data.status:         Estado del lote
 */

require __DIR__.'/../config.php';

$factus = createClient();

$idempotencyKey = bin2hex(random_bytes(16));

$payload = [
    'ruc' => '1234567890001',
    'tipo' => '01',
    'documentos' => [
        [
            'id_externo' => 'batch-'.time().'-1',
            'factura' => [
                'fecha' => date('d/m/Y'),
                'establecimiento' => '001',
                'puntoEmision' => '001',
                'secuencial' => '000000010',
                'descuento' => 0,
                'propina' => 0,
                'total' => 11.50,
                'cliente' => [
                    'tipoIdentificacion' => '05',
                    'documento' => '1712345678',
                    'nombre' => 'Cliente Batch 1',
                ],
            ],
            'detalles' => [
                [
                    'codigoPrincipal' => 'P001',
                    'descripcion' => 'Producto batch 1',
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
                ['formaPago' => '01', 'total' => 11.50],
            ],
            'notificaciones' => [
                'email' => null,
                'webhook_url' => null,
            ],
        ],
        [
            'id_externo' => 'batch-'.time().'-2',
            'factura' => [
                'fecha' => date('d/m/Y'),
                'establecimiento' => '001',
                'puntoEmision' => '001',
                'secuencial' => '000000011',
                'descuento' => 0,
                'propina' => 0,
                'total' => 23.00,
                'cliente' => [
                    'tipoIdentificacion' => '05',
                    'documento' => '1790012345',
                    'nombre' => 'Cliente Batch 2',
                ],
            ],
            'detalles' => [
                [
                    'codigoPrincipal' => 'P002',
                    'descripcion' => 'Producto batch 2',
                    'cantidad' => 2,
                    'precioUnitario' => 10.00,
                    'descuento' => 0,
                    'precioTotalSinImpuesto' => 20.00,
                    'impuestos' => [
                        [
                            'codigo' => '2',
                            'codigoPorcentaje' => '4',
                            'tarifa' => 15.00,
                            'baseImponible' => 20.00,
                            'valor' => 3.00,
                        ],
                    ],
                ],
            ],
            'pagos' => [
                ['formaPago' => '01', 'total' => 23.00],
            ],
            'notificaciones' => [
                'email' => null,
                'webhook_url' => null,
            ],
        ],
    ],
];

echo "Enviando lote de documentos...\n";
echo '  Documentos: '.count($payload['documentos'])."\n";
echo "  Idempotency: {$idempotencyKey}\n\n";

try {
    $response = $factus->document()->registerBatch($payload, $idempotencyKey);

    $data = $response['data'] ?? [];

    echo "LOTE REGISTRADO OK\n";
    echo "  Batch ID:    {$data['batch_id']}\n";
    echo "  Batch Key:   {$data['batch_key']}\n";
    echo "  Tipo:        {$data['tipo']}\n";
    echo "  Documentos:  {$data['documents_count']}\n";
    echo "  Status:      {$data['status']}\n";
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
