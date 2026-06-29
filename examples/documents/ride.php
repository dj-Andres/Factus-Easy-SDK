<?php

/**
 * GET /api/document/{accessKey}/ride
 *
 * Descarga el RIDE (Representación Impresa de Documentos Electrónicos) en PDF.
 * Solo disponible para documentos con estado AUTHORIZED.
 *
 * Request:
 *   ruc  string  required  RUC de la empresa (query param)
 *   accessKey  string  required  49 dígitos (en la URL)
 *
 * Response: application/pdf (binario)
 *   Content-Type: application/pdf
 *   Content-Disposition: attachment; filename="RIDE_{accessKey}.pdf"
 */

require __DIR__ . '/../config.php';

$factus = createClient();

$ruc = '1234567890001';
$accessKey = '1234567890123456789012345678901234567890123456789';

$filename = "RIDE_{$accessKey}.pdf";
$savePath = __DIR__ . '/' . $filename;

echo "Descargando RIDE...\n";
echo "  Access Key: {$accessKey}\n";
echo "  Destino:    {$savePath}\n\n";

try {
    $pdfContent = $factus->document()->downloadRide($accessKey, $ruc);

    $bytes = file_put_contents($savePath, $pdfContent);

    if ($bytes === false) {
        echo "ERROR\n";
        echo "  No se pudo guardar el archivo\n";
        exit(1);
    }

    echo "RIDE DESCARGADO OK\n";
    echo "  Archivo: {$filename}\n";
    echo "  Tamaño:  " . number_format($bytes) . " bytes\n";
} catch (Exception $e) {
    echo "ERROR\n";
    echo "  {$e->getMessage()}\n";
}
