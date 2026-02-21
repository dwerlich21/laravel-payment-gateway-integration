<?php

namespace App\Exceptions;

class ValidationException extends ApiException
{
    protected int $statusCode = 422;

    /**
     * Cria uma nova exceção de validação
     */
    public function __construct(array $errors, string $message = 'Dados inválidos')
    {
        parent::__construct($message, $this->statusCode, $errors);
    }
}
