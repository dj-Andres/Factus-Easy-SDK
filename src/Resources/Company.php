<?php

namespace FactusEasy\Sdk\Resources;

use FactusEasy\Sdk\HttpClient;

class Company
{
    public function __construct(
        private readonly HttpClient $http,
    ) {}

    public function list(): array
    {
        return $this->http->get('/api/companie/list');
    }

    public function create(array $data): array
    {
        return $this->http->post('/api/companie/register', $data);
    }

    public function update(string $ruc, array $data): array
    {
        return $this->http->put("/api/companie/update/{$ruc}", $data);
    }

    public function uploadCertificate(string $ruc, string $filePath, string $password): array
    {
        return $this->http->postMultipart(
            '/api/companie/certificate',
            [
                ['name' => 'ruc', 'contents' => $ruc],
                [
                    'name' => 'certify',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => basename($filePath),
                ],
                ['name' => 'password', 'contents' => $password],
            ],
        );
    }

    public function uploadLogo(string $ruc, string $filePath): array
    {
        return $this->http->postMultipart(
            '/api/companie/upload/logo',
            [
                ['name' => 'ruc', 'contents' => $ruc],
                [
                    'name' => 'logo',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => basename($filePath),
                ],
            ],
        );
    }
}
