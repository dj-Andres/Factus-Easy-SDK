<?php

namespace FactusEasy\Sdk;

class Config
{
    private array $options;

    private const DEFAULTS = [
        'base_url' => 'https://factuseasy.kreativesofts.com',
        'timeout' => 30,
        'connect_timeout' => 10,
        'verify' => true,
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ],
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge(self::DEFAULTS, $options);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    public function getBaseUrl(): string
    {
        return rtrim($this->get('base_url', ''), '/');
    }

    public function getHeaders(): array
    {
        return $this->get('headers', []);
    }

    public function getTimeout(): int
    {
        return $this->get('timeout', 30);
    }

    public function getConnectTimeout(): int
    {
        return $this->get('connect_timeout', 10);
    }

    public function shouldVerify(): bool
    {
        return $this->get('verify', true);
    }

    public function toGuzzleConfig(): array
    {
        return [
            'base_uri' => $this->getBaseUrl(),
            'timeout' => $this->getTimeout(),
            'connect_timeout' => $this->getConnectTimeout(),
            'verify' => $this->shouldVerify(),
            'headers' => $this->getHeaders(),
            'http_errors' => false,
        ];
    }
}
