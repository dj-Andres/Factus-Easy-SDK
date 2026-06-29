<?php

/**
 * POST /api/companie/upload/logo
 *
 * Sube el logo de una empresa (imagen, máx 2MB).
 *
 * Request (multipart/form-data):
 *   ruc   string  required  RUC de la empresa
 *   logo  file    required  Imagen (jpg, png, etc.) - máximo 2048 KB
 *
 * Response (200):
 *   status: "ok"
 *   data: { ruc, name, logo, ... }
 */

require __DIR__ . '/../config.php';

$factus = createClient();

$ruc = '1234567890001';
$logoPath = __DIR__ . '/../test-logo.png';

if (! file_exists($logoPath)) {
    echo "SKIP\n";
    echo "  Archivo no encontrado: {$logoPath}\n";
    echo "  Coloca una imagen (logo.png) en examples/ para probar.\n";
    exit;
}

try {
    $response = $factus->company()->uploadLogo($ruc, $logoPath);
    $c = $response['data'];

    echo "UPLOAD LOGO OK\n";
    echo "  RUC:  {$c['ruc']}\n";
    echo "  Name: {$c['name']}\n";
    echo "  Logo: {$c['logo']}\n";
} catch (Exception $e) {
    echo "ERROR\n";
    echo "  {$e->getMessage()}\n";
}
