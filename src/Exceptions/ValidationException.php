<?php

namespace FactusEasy\Sdk\Exceptions;

class ValidationException extends FactusEasyException
{
    public function __construct(
        string $message = 'Validation failed',
        int $code = 422,
        ?\Throwable $previous = null,
        private readonly array $errors = [],
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
