<?php

namespace FactusEasy\Sdk\Exceptions;

class ConflictException extends FactusEasyException
{
    public function __construct(
        string $message = 'Conflict - the request conflicts with the current state',
        int $code = 409,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
