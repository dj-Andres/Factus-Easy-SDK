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

require __DIR__.'/../config.php';

$factus = createClient();

$ruc = '0791844433001';
$accessKey = '2906202601079184443300110010010000000016775908313';

$downloadDir = $_ENV['FACTUS_EASY_DOWNLOAD_DIR'] ?? __DIR__;

$downloadDir = rtrim($downloadDir, DIRECTORY_SEPARATOR);

if (! is_dir($downloadDir)) {
    if (! mkdir($downloadDir, 0775, true) && ! is_dir($downloadDir)) {
        echo "ERROR\n";
        echo "  No se pudo crear el directorio: {$downloadDir}\n";
        exit(1);
    }
}

if (! is_writable($downloadDir)) {
    echo "ERROR\n";
    echo "  Directorio no escribible: {$downloadDir}\n";
    exit(1);
}

$filename = "RIDE_{$accessKey}.pdf";
$savePath = $downloadDir.DIRECTORY_SEPARATOR.$filename;

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
    echo '  Tamaño:  '.number_format($bytes)." bytes\n";
} catch (Exception $e) {
    echo "ERROR\n";
    echo "  {$e->getMessage()}\n";
}
