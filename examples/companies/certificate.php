<?php

/**
 * POST /api/companie/certificate
 *
 * Sube y valida el certificado digital (.p12) de una empresa.
 * El archivo se valida con Node.js antes de almacenarse.
 *
 * Request (multipart/form-data):
 *   ruc      string  required  RUC de la empresa
 *   certify  file    required  Archivo .p12 del certificado
 *   password string  required  Contraseña del certificado
 *
 * Response (200):
 *   status: "ok"
 *   data: { ruc, name, certificate, signature_issue_date, signature_expiration_date }
 */

require __DIR__ . '/../config.php';

$factus = createClient();

$ruc = '1234567890001';
$certPath = __DIR__ . '/../test-certificate.p12';
$certPassword = 'mi-password-del-cert';

if (! file_exists($certPath)) {
    echo "SKIP\n";
    echo "  Archivo no encontrado: {$certPath}\n";
    echo "  Coloca un archivo .p12 válido en examples/ para probar.\n";
    exit;
}

try {
    $response = $factus->company()->uploadCertificate($ruc, $certPath, $certPassword);
    $c = $response['data'];

    echo "UPLOAD CERTIFICATE OK\n";
    echo "  RUC:          {$c['ruc']}\n";
    echo "  Certificate:  {$c['certificate']}\n";
    echo "  Issue date:   {$c['signature_issue_date']}\n";
    echo "  Expiry date:  {$c['signature_expiration_date']}\n";
} catch (Exception $e) {
    echo "ERROR\n";
    echo "  {$e->getMessage()}\n";
}
