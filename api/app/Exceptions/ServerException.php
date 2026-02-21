<?php

namespace App\Exceptions;

class ServerException extends ApiException
{
    protected int $statusCode = 500;

    /**
     * Cria uma nova exceção de erro do servidor
     */
    public function __construct(string $message = 'Erro interno do servidor', ?\Exception $previous = null)
    {
        parent::__construct($message, $this->statusCode, null, $previous);
    }
}
