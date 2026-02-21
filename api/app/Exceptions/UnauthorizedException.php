<?php

namespace App\Exceptions;

class UnauthorizedException extends ApiException
{
    protected int $statusCode = 401;

    /**
     * Cria uma nova exceção de não autorizado
     */
    public function __construct(string $message = 'Não autorizado')
    {
        parent::__construct($message, $this->statusCode);
    }
}
