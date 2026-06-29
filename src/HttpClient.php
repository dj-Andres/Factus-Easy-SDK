<?php

namespace FactusEasy\Sdk;

use FactusEasy\Sdk\Exceptions\AuthenticationException;
use FactusEasy\Sdk\Exceptions\ConflictException;
use FactusEasy\Sdk\Exceptions\FactusEasyException;
use FactusEasy\Sdk\Exceptions\NotFoundException;
use FactusEasy\Sdk\Exceptions\RateLimitException;
use FactusEasy\Sdk\Exceptions\ValidationException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    private const VERSION = '0.1.0';

    private readonly GuzzleClient $client;

    private ?string $token = null;

    private ?string $requestId = null;

    public function __construct(
        private readonly Config $config,
    ) {
        $guzzleConfig = $this->config->toGuzzleConfig();
        $guzzleConfig['headers']['User-Agent'] = 'factus-easy-php-sdk/'.self::VERSION;

        $this->client = new GuzzleClient($guzzleConfig);
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function clearToken(): void
    {
        $this->token = null;
    }

    public function setRequestId(?string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function get(string $uri, array $options = []): array
    {
        return $this->request('GET', $uri, $options);
    }

    public function post(string $uri, array $data = [], array $options = []): array
    {
        $options['json'] = $data;

        return $this->request('POST', $uri, $options);
    }

    public function postMultipart(string $uri, array $multipart, array $options = []): array
    {
        $options['multipart'] = $multipart;

        return $this->request('POST', $uri, $options);
    }

    public function put(string $uri, array $data = [], array $options = []): array
    {
        $options['json'] = $data;

        return $this->request('PUT', $uri, $options);
    }

    public function request(string $method, string $uri, array $options = []): array
    {
        $options = $this->addAuthHeader($options);
        $options = $this->addRequestIdHeader($options);

        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (GuzzleException $e) {
            throw new FactusEasyException(
                message: "HTTP request failed: {$e->getMessage()}",
                code: $e->getCode(),
                previous: $e,
                context: ['uri' => $uri, 'method' => $method],
            );
        }

        return $this->handleResponse($response);
    }

    public function requestRaw(string $method, string $uri, array $options = []): ResponseInterface
    {
        $options = $this->addAuthHeader($options);
        $options = $this->addRequestIdHeader($options);

        try {
            return $this->client->request($method, $uri, $options);
        } catch (GuzzleException $e) {
            throw new FactusEasyException(
                message: "HTTP request failed: {$e->getMessage()}",
                code: $e->getCode(),
                previous: $e,
                context: ['uri' => $uri, 'method' => $method],
            );
        }
    }

    private function addAuthHeader(array $options): array
    {
        if ($this->token !== null) {
            $options['headers']['Authorization'] = "Bearer {$this->token}";
        }

        return $options;
    }

    private function addRequestIdHeader(array $options): array
    {
        if ($this->requestId !== null) {
            $options['headers']['X-Request-Id'] = $this->requestId;
        }

        return $options;
    }

    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $contentType = $response->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new FactusEasyException(
                    message: 'Invalid JSON response from API',
                    code: $statusCode,
                    context: ['body' => $body],
                );
            }

            if ($statusCode >= 400) {
                $this->handleError($data, $statusCode, $response);
            }

            return $data;
        }

        if ($statusCode >= 400) {
            throw new FactusEasyException(
                message: "Request failed with status {$statusCode}",
                code: $statusCode,
                context: ['body' => $body],
            );
        }

        return [
            'status' => 'ok',
            'code' => $statusCode,
            'data' => $body,
        ];
    }

    private function handleError(array $data, int $statusCode, ?ResponseInterface $response = null): never
    {
        $message = $data['message'] ?? 'Unknown error';
        $code = $data['code'] ?? $statusCode;
        $context = $data;

        if ($response !== null) {
            $context['headers'] = $response->getHeaders();
        }

        if ($statusCode === 401) {
            throw new AuthenticationException($message, $code);
        }

        if ($statusCode === 404) {
            throw new NotFoundException($message, $code);
        }

        if ($statusCode === 409) {
            throw new ConflictException($message, $code);
        }

        if ($statusCode === 422) {
            $errors = $data['errors'] ?? [];

            throw new ValidationException($message, $code, null, $errors);
        }

        if ($statusCode === 429) {
            $retryAfter = null;

            if ($response !== null && $response->hasHeader('Retry-After')) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
            }

            throw new RateLimitException($message, $code, null, $retryAfter);
        }

        throw new FactusEasyException($message, $code, context: $context);
    }
}
