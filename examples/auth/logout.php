<?php

/**
 * POST /api/logout
 *
 * Invalida el token actual del usuario.
 * Requiere autenticación (Bearer token).
 *
 * Request:
 *   (ningún body requerido)
 *
 * Headers:
 *   Authorization: Bearer {token}
 *
 * Response (200):
 *   status: "ok"
 *   message: "Logged out successfully"
 *   data: []
 */

require __DIR__.'/../config.php';

$factus = createClient();

// Primero iniciar sesión para obtener token
try {
    $token = $factus->auth()->login(
        email: 'admin@factus-easy.com',
        password: 'password',
    );

    echo "Login previo OK\n";
    echo "  Token: {$token}\n\n";
} catch (Exception $e) {
    echo "Error en login: {$e->getMessage()}\n\n";
    exit(1);
}

// Ahora cerrar sesión
try {
    $result = $factus->auth()->logout();

    echo "LOGOUT OK\n";
    echo '  Resultado: '.($result ? 'token invalidado' : 'falló')."\n";
} catch (Exception $e) {
    echo "LOGOUT ERROR\n";
    echo "  {$e->getMessage()}\n";
}
