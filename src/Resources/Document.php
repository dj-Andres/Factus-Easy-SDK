<?php

namespace FactusEasy\Sdk\Resources;

use FactusEasy\Sdk\HttpClient;

class Document
{
    public function __construct(
        private readonly HttpClient $http,
    ) {}

    public function register(array $data, ?string $idempotencyKey = null): array
    {
        $options = [];

        if ($idempotencyKey !== null) {
            $options['headers']['Idempotency-Key'] = $idempotencyKey;
        }

        return $this->http->post('/api/document/register', $data, $options);
    }

    public function registerBatch(array $data, ?string $idempotencyKey = null): array
    {
        $options = [];

        if ($idempotencyKey !== null) {
            $options['headers']['Idempotency-Key'] = $idempotencyKey;
        }

        return $this->http->post('/api/document/batch/register', $data, $options);
    }

    public function status(array $filters): array
    {
        return $this->http->get('/api/document/status', [
            'query' => $filters,
        ]);
    }

    public function downloadRide(string $accessKey, string $ruc): string
    {
        $response = $this->http->requestRaw('GET', "/api/document/{$accessKey}/ride", [
            'query' => ['ruc' => $ruc],
        ]);

        return (string) $response->getBody();
    }

    public function downloadRideTo(string $accessKey, string $ruc, string $path): string
    {
        $response = $this->http->requestRaw('GET', "/api/document/{$accessKey}/ride", [
            'query' => ['ruc' => $ruc],
            'sink' => $path,
        ]);

        return $path;
    }
}
