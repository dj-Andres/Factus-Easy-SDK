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

## Configuración inicial

Copia el archivo de ejemplo:

```bash
cp .env.test .env
```

Edita `.env` con tus credenciales:

```env
FACTUS_EASY_EMAIL=tu-email@ejemplo.com
FACTUS_EASY_PASSWORD=tu-contraseña
FACTUS_EASY_DOWNLOAD_DIR=examples/downloads
```

La URL base de la API ya está preconfigurada en el SDK. No necesita configurarse.

## Uso rápido

```php
<?php

require 'vendor/autoload.php';

use FactusEasy\Sdk\FactusEasy;
use FactusEasy\Sdk\Exceptions\ValidationException;

$factus = new FactusEasy();

$factus->auth()->login('email@ejemplo.com', 'password');

// Listar empresas
$companies = $factus->company()->list();

// Emitir factura
$response = $factus->document()->register($payload, $idempotencyKey);

// Consultar estado de un documento
$status = $factus->document()->status([
    'ruc' => '1234567890001',
    'external_id' => 'mi-id-externo',
]);
```

## Estructura del SDK

```
src/
├── FactusEasy.php          ← Punto de entrada
├── Config.php              ← Configuración
├── HttpClient.php          ← Cliente HTTP (Guzzle)
├── Resources/              ← Recursos de la API
│   ├── Auth.php            → autenticación
│   ├── Company.php         → empresas
│   └── Document.php        → documentos electrónicos
└── Exceptions/             ← Excepciones
    ├── FactusEasyException.php
    ├── AuthenticationException.php
    └── ValidationException.php
```

## Auth

### Login

```php
$token = $factus->auth()->login('email@ejemplo.com', 'password');
// → string: token Sanctum (se guarda automáticamente para siguientes requests)
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
// Útil si ya tienes un token guardado en sesión o BD
```

## Companies

### Listar

```php
$response = $factus->company()->list();
// response['data'] → array de empresas
```

### Crear

```php
$response = $factus->company()->create([
    'ruc'                 => '1234567890001',
    'name'                => 'Mi Empresa',
    'business_name'       => 'Mi Empresa Cía. Ltda.',
    'address'             => 'Av. Principal 123',
    'phone'               => '0999999999',
    'accounting_required' => 'SI',
    'special_taxpayer'    => 'NO',
    'major_taxpayer'      => 'NO',
    'email'               => 'empresa@ejemplo.com',
]);
```

### Actualizar

```php
$response = $factus->company()->update('1234567890001', [
    'name' => 'Nombre Actualizado',
    // mismos campos que create
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

## Documents

### Registrar factura (tipo 01)

```php
$idempotencyKey = bin2hex(random_bytes(16));

$payload = [
    'ruc' => '1234567890001',
    'tipo' => '01',
    'id_externo' => 'fact-001',
    'factura' => [
        'fecha' => date('d/m/Y'),
        'establecimiento' => '001',
        'puntoEmision' => '001',
        'secuencial' => '000000001',
        'descuento' => 0,
        'propina' => 0,
        'total' => 11.50,
        'cliente' => [
            'tipoIdentificacion' => '05',
            'documento' => '1712345678',
            'nombre' => 'Cliente de Prueba',
            'correo' => 'cliente@ejemplo.com',
        ],
    ],
    'detalles' => [
        [
            'codigoPrincipal' => 'P001',
            'descripcion' => 'Producto A',
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
];

$response = $factus->document()->register($payload, $idempotencyKey);
```

### Registrar nota de crédito (tipo 04)

```php
$payload = [
    'ruc' => '1234567890001',
    'tipo' => '04',
    'id_externo' => 'nc-001',
    'nota' => [
        'fecha' => date('d/m/Y'),
        'establecimiento' => '001',
        'puntoEmision' => '001',
        'secuencial' => '000000001',
        'tipo' => '1',
        'docModificado' => [
            'tipo' => '01',
            'numero' => '001-001-000000001',
            'fechaEmision' => date('d/m/Y'),
            'claveAutorizacion' => '49 dígitos de la factura original',
        ],
    ],
    'detalles' => [
        [
            'codigoPrincipal' => 'P001',
            'descripcion' => 'Producto A - Nota de crédito',
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
    'notificaciones' => [
        'email' => null,
        'webhook_url' => null,
    ],
];

$response = $factus->document()->register($payload, $idempotencyKey);
```

### Registrar retención (tipo 07)

```php
$payload = [
    'ruc' => '1234567890001',
    'tipo' => '07',
    'id_externo' => 'ret-001',
    'retencion' => [
        'fecha' => date('d/m/Y'),
        'establecimiento' => '001',
        'puntoEmision' => '001',
        'secuencial' => '000000001',
        'periodoFiscal' => date('m/Y'),
        'sujetoRetenido' => [
            'tipoIdentificacion' => '04',
            'documento' => '1790012345001',
            'nombre' => 'Proveedor de Prueba',
        ],
        'total' => 100.00,
    ],
    'detalles' => [
        [
            'codigo' => 1,
            'codigoRetencion' => 303,
            'baseImponible' => 100.00,
            'porcentajeRetener' => 1.00,
            'valorRetenido' => 1.00,
            'codDocSustento' => '01',
            'numDocSustento' => '001-001-000000001',
            'fechaEmisionSustento' => date('d/m/Y'),
        ],
    ],
    'notificaciones' => [
        'email' => null,
        'webhook_url' => null,
    ],
];

$response = $factus->document()->register($payload, $idempotencyKey);
```

### Registrar lote de documentos

```php
$payload = [
    'ruc' => '1234567890001',
    'tipo' => '01',
    'documentos' => [
        [
            'id_externo' => 'batch-001',
            'factura' => [...],
            'detalles' => [...],
            'pagos' => [...],
            'notificaciones' => [...],
        ],
        // ... hasta 50 documentos
    ],
];

$response = $factus->document()->registerBatch($payload, $idempotencyKey);
```

### Consultar estado de documentos

```php
// Por external_id
$response = $factus->document()->status([
    'ruc' => '1234567890001',
    'external_id' => 'fact-001',
]);

// Con filtros y paginación
$response = $factus->document()->status([
    'ruc' => '1234567890001',
    'status' => 'AUTHORIZED',
    'date_from' => '2026-01-01',
    'date_to' => '2026-12-31',
    'page' => 1,
    'per_page' => 20,
]);

// Parámetros disponibles:
// ruc, tipo (01/04/07), external_id, access_key, status,
// date_from, date_to, page, per_page, include_xml
```

### Descargar RIDE PDF

```php
$pdfContent = $factus->document()->downloadRide(
    accessKey: '49 dígitos de la clave de acceso',
    ruc: '1234567890001',
);

file_put_contents('factura.pdf', $pdfContent);
```

## Manejo de errores

```php
use FactusEasy\Sdk\Exceptions\ValidationException;
use FactusEasy\Sdk\Exceptions\AuthenticationException;
use FactusEasy\Sdk\Exceptions\FactusEasyException;

try {
    $factus->company()->create([...]);
} catch (ValidationException $e) {
    echo $e->getMessage();
    print_r($e->getErrors());   // errores por campo
} catch (AuthenticationException $e) {
    echo 'Token inválido o expirado: ' . $e->getMessage();
} catch (FactusEasyException $e) {
    echo 'Error: ' . $e->getMessage();
    print_r($e->getContext());
}
```

| Excepción | Código HTTP | Cuándo ocurre |
|-----------|-------------|---------------|
| `ValidationException` | 422 | Datos inválidos (errores por campo en `getErrors()`) |
| `AuthenticationException` | 401 | Token inválido o expirado |
| `FactusEasyException` | 4xx/5xx | Otros errores (contexto en `getContext()`) |

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
| `document()->register()` | `POST /api/document/register` | Sí |
| `document()->registerBatch()` | `POST /api/document/batch/register` | Sí |
| `document()->status()` | `GET /api/document/status` | Sí |
| `document()->downloadRide()` | `GET /api/document/{accessKey}/ride` | Sí |

## Ejemplos disponibles

```bash
# Auth
php examples/auth/login.php
php examples/auth/register.php
php examples/auth/logout.php

# Empresas
php examples/companies/list.php
php examples/companies/create.php
php examples/companies/update.php
php examples/companies/certificate.php
php examples/companies/logo.php

# Documentos
php examples/documents/register-invoice.php
php examples/documents/register-credit-note.php
php examples/documents/register-retention.php
php examples/documents/batch.php
php examples/documents/status.php
php examples/documents/ride.php
```

## Variables de entorno

| Variable | Obligatorio | Default | Descripción |
|----------|-------------|---------|-------------|
| `FACTUS_EASY_EMAIL` | Sí | — | Email para autenticación |
| `FACTUS_EASY_PASSWORD` | Sí | — | Contraseña para autenticación |
| `FACTUS_EASY_DOWNLOAD_DIR` | No | `examples/` | Carpeta donde se guardan los RIDE PDF |

## Desarrollo local

```bash
git clone https://github.com/factus-easy/factus-easy-sdk.git
cd factus-easy-sdk
composer install && cp .env.test .env
# Editar .env con credenciales reales
php examples/companies/list.php
```

## Licencia

MIT
