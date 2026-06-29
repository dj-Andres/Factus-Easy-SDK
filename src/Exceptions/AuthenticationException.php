<?php

namespace FactusEasy\Sdk\Exceptions;

class AuthenticationException extends FactusEasyException
{
    public function __construct(
        string $message = 'Authentication failed',
        int $code = 401,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
