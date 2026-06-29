# Factus Easy SDK PHP

SDK oficial para integrar la API de **Factus Easy** — Facturación Electrónica SRI Ecuador.

> Consulta la documentación completa de la API en [factuseasy.kreativesofts.com/docs](https://factuseasy.kreativesofts.com/docs)

## Requisitos

- PHP ^8.3
- GuzzleHttp ^7.0
- Extensión `json`
- Extensión `mbstring`

## Instalación

```bash
composer require factus-easy/factus-easy-sdk
```

## Uso rápido

```php
<?php

require 'vendor/autoload.php';

use FactusEasy\Sdk\FactusEasy;

$factus = new FactusEasy([
    'base_url' => 'https://tudominio.com',
]);

// Login
$factus->auth()->login('email@ejemplo.com', 'password');

// Listar empresas
$response = $factus->company()->list();
```

## Configuración

| Opción | Default | Descripción |
|--------|---------|-------------|
| `base_url` | `https://api.factus-easy.com` | URL base de la API |
| `timeout` | `30` | Timeout de petición (segundos) |
| `connect_timeout` | `10` | Timeout de conexión (segundos) |
| `verify` | `true` | Verificar SSL |

```php
$factus = new FactusEasy([
    'base_url' => 'https://tudominio.com',
    'timeout'  => 60,
]);
```

## Auth

### Login

```php
$token = $factus->auth()->login('email@ejemplo.com', 'password');
// → string: token Sanctum
```

### Register

```php
$result = $factus->auth()->register(
    name: 'Usuario SDK',
    email: 'sdk@ejemplo.com',
    password: 'MiPassword123',
    passwordConfirmation: 'MiPassword123',
);
// → array: ['user' => [...], 'token' => '...']
```

### Logout

```php
$result = $factus->auth()->logout();
// → bool: true/false
```

### Token manual

```php
$factus->setToken('token-existente');
```

## Companies

### Listar empresas

```php
$response = $factus->company()->list();
// response['data'] → array de empresas
```

### Crear empresa

```php
$response = $factus->company()->create([
    'ruc'                => '1234567890001',
    'name'               => 'Mi Empresa',
    'business_name'      => 'Mi Empresa Cía. Ltda.',
    'address'            => 'Av. Principal 123',
    'phone'              => '0999999999',
    'accounting_required'=> 'SI',
    'special_taxpayer'   => 'NO',
    'major_taxpayer'     => 'NO',
    'email'              => 'empresa@ejemplo.com',
]);
```

### Actualizar empresa

```php
$response = $factus->company()->update('1234567890001', [
    'ruc'   => '1234567890001',
    'name'  => 'Nombre Actualizado',
    // ... mismos campos que create
]);
```

### Subir certificado digital (.p12)

```php
$response = $factus->company()->uploadCertificate(
    ruc: '1234567890001',
    filePath: '/ruta/certificado.p12',
    password: 'clave-del-certificado',
);
```

### Subir logo

```php
$response = $factus->company()->uploadLogo(
    ruc: '1234567890001',
    filePath: '/ruta/logo.png',
);
```

## Manejo de errores

```php
use FactusEasy\Sdk\Exceptions\ValidationException;
use FactusEasy\Sdk\Exceptions\AuthenticationException;

try {
    $factus->company()->create([...]);
} catch (ValidationException $e) {
    echo $e->getMessage();
    print_r($e->getErrors());   // errores por campo
} catch (AuthenticationException $e) {
    echo 'Token inválido o expirado';
} catch (FactusEasy\Sdk\Exceptions\FactusEasyException $e) {
    echo $e->getMessage();
}
```

## Endpoints disponibles

| Método SDK | Endpoint | Auth |
|-----------|----------|------|
| `auth()->register()` | `POST /api/register` | No |
| `auth()->login()` | `POST /api/login` | No |
| `auth()->logout()` | `POST /api/logout` | Sí |
| `company()->list()` | `GET /api/companie/list` | Sí |
| `company()->create()` | `POST /api/companie/register` | Sí |
| `company()->update()` | `PUT /api/companie/update/{ruc}` | Sí |
| `company()->uploadCertificate()` | `POST /api/companie/certificate` | Sí |
| `company()->uploadLogo()` | `POST /api/companie/upload/logo` | Sí |

## Ejemplos

Ver scripts de ejemplo en `examples/`:

```bash
# 1. Editar credenciales en el script
# 2. Ejecutar
php examples/auth/login.php
php examples/auth/register.php
php examples/companies/list.php
php examples/companies/create.php
```

## Desarrollo local

```bash
git clone https://github.com/tu-repo/factus-easy-sdk.git
cd factus-easy-sdk
composer install
```

## Licencia

MIT
