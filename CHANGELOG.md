# Changelog

## [0.1.0] - 2026-06-29

### Added
- Auth: login, register, logout
- Companies: list, create, update, uploadCertificate, uploadLogo
- Documents: register, registerBatch, status, downloadRide, downloadRideTo (streaming)
- Exceptions: AuthenticationException (401), NotFoundException (404), ConflictException (409), ValidationException (422), RateLimitException (429), FactusEasyException (base)
- Support: Idempotency helper (UUID v4)
- Support: SRI constants (TaxCodes, PercentageCodes, PaymentMethods)
- Support: X-Request-Id header for request tracing
- Dotenv integration for examples + FACTUS_EASY_DOWNLOAD_DIR
- Examples for every endpoint (auth, companies, documents)
- Composer scripts for running examples
- README with full documentation, error handling guide, and quickstart

### Changed
- User-Agent global: factus-easy-php-sdk/0.1.0
- HttpClient error handling with enriched context (response headers)
- Validation errors include field-level details via getErrors()

### Breaking
- PHP minimum version: ^8.3
- Base URL is fixed to https://factuseasy.kreativesofts.com (not configurable)
