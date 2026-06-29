<?php

namespace FactusEasy\Sdk;

use FactusEasy\Sdk\Resources\Auth;
use FactusEasy\Sdk\Resources\Company;
use FactusEasy\Sdk\Resources\Document;
use FactusEasy\Sdk\Support\Idempotency;

class FactusEasy
{
    private readonly Config $config;

    private readonly HttpClient $http;

    private ?Auth $auth = null;

    private ?Company $company = null;

    private ?Document $document = null;

    public function __construct(array $options = [])
    {
        $this->config = new Config($options);
        $this->http = new HttpClient($this->config);
    }

    public function auth(): Auth
    {
        if ($this->auth === null) {
            $this->auth = new Auth($this->http);
        }

        return $this->auth;
    }

    public function company(): Company
    {
        if ($this->company === null) {
            $this->company = new Company($this->http);
        }

        return $this->company;
    }

    public function document(): Document
    {
        if ($this->document === null) {
            $this->document = new Document($this->http);
        }

        return $this->document;
    }

    public function idempotency(): string
    {
        return Idempotency::new();
    }

    public function setRequestId(string $requestId): void
    {
        $this->http->setRequestId($requestId);
    }

    public function setToken(string $token): void
    {
        $this->http->setToken($token);
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getHttpClient(): HttpClient
    {
        return $this->http;
    }
}
