<?php

/**
 * POST /api/register
 *
 * Registra un nuevo usuario en la plataforma.
 * Crea automáticamente una suscripción trial de 30 días.
 *
 * Request:
 *   name                  string  required  Nombre completo
 *   email                 string  required  Correo electrónico (único)
 *   password              string  required  Mínimo 8 caracteres
 *   password_confirmation string  required  Debe coincidir con password
 *
 * Response (201):
 *   status: "ok"
 *   data:
 *     user:  { id, name, email, created_at }
 *     token: "1|abc123..." (token Sanctum)
 */

require __DIR__ . '/../config.php';

$factus = createClient();

try {
    $result = $factus->auth()->register(
        name: 'test user',
        email: 'test@exmaple.com',
        password: 'test123',
        passwordConfirmation: 'test123',
    );

    echo "REGISTER OK\n";
    echo "  User ID:    {$result['user']['id']}\n";
    echo "  Name:       {$result['user']['name']}\n";
    echo "  Email:      {$result['user']['email']}\n";
    echo "  Token:      {$result['token']}\n";
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
