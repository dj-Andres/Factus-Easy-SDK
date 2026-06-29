<?php

namespace FactusEasy\Sdk\Exceptions;

class RateLimitException extends FactusEasyException
{
    public function __construct(
        string $message = 'Too many requests - rate limit exceeded',
        int $code = 429,
        ?\Throwable $previous = null,
        private readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
