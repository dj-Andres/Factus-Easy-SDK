<?php

use FactusEasy\Sdk\Exceptions\AuthenticationException;

/**
 * POST /api/login
 *
 * Inicia sesión con credenciales existentes.
 * El token devuelto es válido por 24 horas.
 * NOTA: Al iniciar sesión, se eliminan TODOS los tokens anteriores del usuario.
 *
 * Request:
 *   email    string  required  Correo electrónico
 *   password string  required  Contraseña
 *
 * Response (200):
 *   status: "ok"
 *   data: "1|abc123..." (token Sanctum como string plano)
 *
 * Error (404):
 *   status: "info"
 *   message: "The provided credentials are incorrect"
 */

require __DIR__.'/../config.php';

$factus = createClient();

try {
    $token = $factus->auth()->login(
        email: 'admin@factus-easy.com',
        password: 'password',
    );

    echo "LOGIN OK\n";
    echo "  Token: {$token}\n";
} catch (AuthenticationException $e) {
    echo "AUTH ERROR\n";
    echo "  {$e->getMessage()}\n";
} catch (Exception $e) {
    echo "ERROR\n";
    echo "  {$e->getMessage()}\n";
}
