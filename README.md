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
$idempotencyKey = $factus->idempotency();
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
├── Exceptions/             ← Excepciones
│   ├── FactusEasyException.php      (base)
│   ├── AuthenticationException.php  (401)
│   ├── ValidationException.php      (422)
│   ├── NotFoundException.php        (404)
│   ├── ConflictException.php        (409)
│   └── RateLimitException.php       (429)
└── Support/                ← Utilidades
    ├── Idempotency.php     → generación de UUID v4
    ├── TaxCodes.php        → códigos de impuestos SRI
    ├── PercentageCodes.php → códigos de porcentajes SRI
    └── PaymentMethods.php  → formas de pago SRI
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
use FactusEasy\Sdk\Support\TaxCodes;
use FactusEasy\Sdk\Support\PercentageCodes;
use FactusEasy\Sdk\Support\PaymentMethods;

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
                    'codigo' => TaxCodes::IVA,
                    'codigoPorcentaje' => PercentageCodes::IVA_15,
                    'tarifa' => 15.00,
                    'baseImponible' => 10.00,
                    'valor' => 1.50,
                ],
            ],
        ],
    ],
    'pagos' => [
        ['formaPago' => PaymentMethods::EFECTIVO, 'total' => 11.50],
    ],
    'notificaciones' => [
        'email' => null,
        'webhook_url' => null,
    ],
];

$response = $factus->document()->register($payload, $factus->idempotency());
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
    'detalles' => [[
        'codigoPrincipal' => 'P001',
        'descripcion' => 'Producto A - Nota de crédito',
        'cantidad' => 1,
        'precioUnitario' => 10.00,
        'descuento' => 0,
        'precioTotalSinImpuesto' => 10.00,
        'impuestos' => [[
            'codigo' => TaxCodes::IVA,
            'codigoPorcentaje' => PercentageCodes::IVA_15,
            'tarifa' => 15.00,
            'baseImponible' => 10.00,
            'valor' => 1.50,
        ]],
    ]],
    'notificaciones' => ['email' => null, 'webhook_url' => null],
];

$response = $factus->document()->register($payload, $factus->idempotency());
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
    'detalles' => [[
        'codigo' => 1,
        'codigoRetencion' => 303,
        'baseImponible' => 100.00,
        'porcentajeRetener' => 1.00,
        'valorRetenido' => 1.00,
        'codDocSustento' => '01',
        'numDocSustento' => '001-001-000000001',
        'fechaEmisionSustento' => date('d/m/Y'),
    ]],
    'notificaciones' => ['email' => null, 'webhook_url' => null],
];

$response = $factus->document()->register($payload, $factus->idempotency());
```

### Registrar lote de documentos

```php
$payload = [
    'ruc' => '1234567890001',
    'tipo' => '01',
    'documentos' => [[
        'id_externo' => 'batch-001',
        'factura' => [...],
        'detalles' => [...],
        'pagos' => [...],
        'notificaciones' => [...],
    ]],
];

$response = $factus->document()->registerBatch($payload, $factus->idempotency());
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
```

### Descargar RIDE PDF

```php
// En memoria
$pdfContent = $factus->document()->downloadRide($accessKey, $ruc);
file_put_contents('factura.pdf', $pdfContent);

// Directo a disco (streaming, sin cargar en RAM)
$factus->document()->downloadRideTo($accessKey, $ruc, '/ruta/factura.pdf');
```

## Manejo de errores

```php
use FactusEasy\Sdk\Exceptions\ValidationException;
use FactusEasy\Sdk\Exceptions\AuthenticationException;
use FactusEasy\Sdk\Exceptions\NotFoundException;
use FactusEasy\Sdk\Exceptions\ConflictException;
use FactusEasy\Sdk\Exceptions\RateLimitException;
use FactusEasy\Sdk\Exceptions\FactusEasyException;

try {
    $factus->company()->create([...]);
} catch (ValidationException $e) {
    echo $e->getMessage();
    print_r($e->getErrors());   // errores por campo
} catch (AuthenticationException $e) {
    echo 'Token inválido: ' . $e->getMessage();
} catch (NotFoundException $e) {
    echo 'Recurso no encontrado: ' . $e->getMessage();
} catch (ConflictException $e) {
    echo 'Conflicto de idempotencia: ' . $e->getMessage();
} catch (RateLimitException $e) {
    echo 'Demasiadas solicitudes. Reintentar en ' . ($e->getRetryAfter() ?? '?') . 's';
} catch (FactusEasyException $e) {
    echo 'Error: ' . $e->getMessage();
    print_r($e->getContext());
}
```

| Excepción | Código HTTP | Cuándo ocurre |
|-----------|-------------|---------------|
| `AuthenticationException` | 401 | Token inválido o expirado |
| `NotFoundException` | 404 | Recurso no encontrado |
| `ConflictException` | 409 | Idempotencia duplicada o conflicto |
| `ValidationException` | 422 | Datos inválidos (`getErrors()` por campo) |
| `RateLimitException` | 429 | Demasiadas solicitudes (`getRetryAfter()`) |
| `FactusEasyException` | 4xx/5xx | Otros errores (`getContext()` con detalles) |

## Errores frecuentes

### 401 — Token inválido o expirado
- El token tiene validez de 24h. Vuelve a llamar a `auth()->login()` o renueva con `setToken()`.
- Verifica que email y password en `.env` sean correctos.

### 422 — Error de validación
- Revisa `$e->getErrors()` que devuelve un array con los errores por campo.
- Causas comunes: fecha debe ser hoy (`d/m/Y`), impuesto/porcentaje no existe en catálogo, secuencial duplicado, RUC sin certificado.

### 409 — Conflicto de idempotencia
- El `Idempotency-Key` ya fue usado con otro payload o la misma solicitud ya está en proceso.
- Genera una nueva key con `$factus->idempotency()` o espera a que finalice el proceso anterior.

### 429 — Rate limit
- Has superado el límite de solicitudes por minuto. Revisa `$e->getRetryAfter()` para saber cuándo reintentar.

### 404 — Recurso no encontrado
- Verifica que el RUC exista, la empresa esté activa y el access key sea correcto de 49 dígitos.

## Utilidades

### Idempotency

Genera claves de idempotencia para proteger contra duplicados:

```php
$key = $factus->idempotency();        // vía helper
$key = Idempotency::new();            // vía clase estática
// Ejemplo: "5973bb92-826c-40e9-8fab-adb0de003364"
```

### Request ID (trazabilidad)

```php
$factus->setRequestId('mi-correlativo-001');
// Envía header X-Request-Id a la API para correlacionar logs
```

### Constantes SRI

Evita magic strings en los payloads:

```php
use FactusEasy\Sdk\Support\TaxCodes;
use FactusEasy\Sdk\Support\PercentageCodes;
use FactusEasy\Sdk\Support\PaymentMethods;

TaxCodes::IVA                  // '2'
TaxCodes::ICE                  // '3'
TaxCodes::IRBPNR               // '5'

PercentageCodes::IVA_0         // '0'
PercentageCodes::IVA_12        // '2'
PercentageCodes::IVA_14        // '3'
PercentageCodes::IVA_15        // '4'

PaymentMethods::EFECTIVO       // '01'
PaymentMethods::TRANSFERENCIA  // '24'
PaymentMethods::TARJETA_CREDITO // '19'
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
| `document()->register()` | `POST /api/document/register` | Sí |
| `document()->registerBatch()` | `POST /api/document/batch/register` | Sí |
| `document()->status()` | `GET /api/document/status` | Sí |
| `document()->downloadRide()` | `GET /api/document/{accessKey}/ride` | Sí |
| `document()->downloadRideTo()` | `GET /api/document/{accessKey}/ride` | Sí |

## Variables de entorno

| Variable | Obligatorio | Default | Descripción |
|----------|-------------|---------|-------------|
| `FACTUS_EASY_EMAIL` | Sí | — | Email para autenticación |
| `FACTUS_EASY_PASSWORD` | Sí | — | Contraseña para autenticación |
| `FACTUS_EASY_DOWNLOAD_DIR` | No | `examples/` | Carpeta donde se guardan los RIDE PDF |

## Scripts disponibles

```bash
composer example:login       # php examples/auth/login.php
composer example:register    # php examples/auth/register.php
composer example:companies   # php examples/companies/list.php
composer example:invoice     # php examples/documents/register-invoice.php
composer example:status      # php examples/documents/status.php
composer example:ride        # php examples/documents/ride.php
composer example:certificate # php examples/companies/certificate.php
```

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
