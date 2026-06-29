<?php

/**
 * GET /api/document/status
 *
 * Consulta el estado de documentos electrónicos.
 * Filtros disponibles:
 *   ruc          string  required  RUC de la empresa
 *   tipo         string  optional  01, 04 o 07 (solo ese tipo)
 *   external_id  string  optional  ID externo del documento
 *   access_key   string  optional  Clave de acceso (49 dígitos)
 *   status       string  optional  GENERATED, SIGNED, RECEIVED, AUTHORIZED, REJECTED, ERROR, RETURNED
 *   date_from    string  optional  Y-m-d
 *   date_to      string  optional  Y-m-d
 *   page         int     optional  Página (default 1)
 *   per_page     int     optional  Items por página (default 20, max 100)
 *   include_xml  bool    optional  Incluir XML generado en respuesta
 *
 * Response:
 *   data.documents           array   Lista de documentos
 *   data.pagination          array   { current_page, per_page, total, last_page, has_more }
 *   data.summary             array   Resumen por estado
 *   data.filters_applied     array   Filtros aplicados
 */

require __DIR__.'/../config.php';

$factus = createClient();

// ─── 1. Consultar por external_id ───────────────────
echo "1. Consulta por External ID\n";
echo str_repeat('─', 40)."\n";

try {
    $response = $factus->document()->status([
        'ruc' => '1234567890001',
        'external_id' => 'fact-20250629-12345',
    ]);

    $doc = $response['data'] ?? [];

    if (isset($doc['access_key'])) {
        echo "  Access Key: {$doc['access_key']}\n";
        echo "  Status:     {$doc['status']}\n";
        echo "  Issue Date: {$doc['issue_date']}\n";
    } else {
        echo "  Documento no encontrado\n";
    }
} catch (Exception $e) {
    echo "  Error: {$e->getMessage()}\n";
}

echo "\n";

// ─── 2. Listar documentos con filtros ───────────────
echo "2. Listado con filtros\n";
echo str_repeat('─', 40)."\n";

try {
    $response = $factus->document()->status([
        'ruc' => '1234567890001',
        'status' => 'AUTHORIZED',
        'date_from' => date('Y-m-d', strtotime('-30 days')),
        'date_to' => date('Y-m-d'),
        'page' => 1,
        'per_page' => 10,
    ]);

    $data = $response['data'] ?? [];

    echo "  Documentos: {$data['pagination']['total']}\n";
    echo "  Página:     {$data['pagination']['current_page']}/{$data['pagination']['last_page']}\n";

    foreach ($data['documents'] as $doc) {
        echo "\n";
        echo "  - {$doc['access_key']}\n";
        echo "    Tipo:   {$doc['document_type']}\n";
        echo "    Status: {$doc['status']}\n";
        echo "    Fecha:  {$doc['issue_date']}\n";
    }
} catch (Exception $e) {
    echo "  Error: {$e->getMessage()}\n";
}

echo "\n";

// ─── 3. Solo resumen (sin external_id) ──────────────
echo "3. Resumen general\n";
echo str_repeat('─', 40)."\n";

try {
    $response = $factus->document()->status([
        'ruc' => '1234567890001',
        'per_page' => 1,
    ]);

    $data = $response['data'] ?? [];
    $summary = $data['summary'] ?? [];

    echo "  Total docs:    {$summary['total_documents']}\n";
    echo "  Facturas:      {$summary['total_invoices']}\n";
    echo "  Notas crédito: {$summary['total_credit_notes']}\n";
    echo "  Retenciones:   {$summary['total_retentions']}\n";
    echo "  Finalizados:   {$summary['finalized_documents']}\n";
    echo "  Procesando:    {$summary['processing_documents']}\n";
} catch (Exception $e) {
    echo "  Error: {$e->getMessage()}\n";
}
