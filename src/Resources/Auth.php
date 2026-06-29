<?php

namespace FactusEasy\Sdk\Resources;

use FactusEasy\Sdk\HttpClient;

class Auth
{
    public function __construct(
        private readonly HttpClient $http,
    ) {}

    public function login(string $email, string $password): string
    {
        $response = $this->http->post('/api/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $token = $response['data'] ?? '';

        $this->http->setToken($token);

        return $token;
    }

    public function register(
        string $name,
        string $email,
        string $password,
        string $passwordConfirmation,
    ): array {
        $response = $this->http->post('/api/register', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ]);

        $data = $response['data'] ?? [];
        $token = $data['token'] ?? '';

        $this->http->setToken($token);

        return $data;
    }

    public function logout(): bool
    {
        $response = $this->http->post('/api/logout');

        $this->http->clearToken();

        return ($response['status'] ?? '') === 'ok';
    }
}
