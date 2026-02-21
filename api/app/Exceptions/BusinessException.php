<?php

namespace App\Exceptions;

class BusinessException extends ApiException
{
    protected int $statusCode = 400;

    /**
     * Cria uma nova exceção de regra de negócio
     */
    public function __construct(string $message, array $details = [])
    {
        parent::__construct($message, $this->statusCode, $details);
    }
}
