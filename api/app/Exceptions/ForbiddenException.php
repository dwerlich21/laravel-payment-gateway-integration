<?php

namespace App\Exceptions;

class ForbiddenException extends ApiException
{
    protected int $statusCode = 403;

    /**
     * Cria uma nova exceção de acesso negado
     */
    public function __construct(string $message = 'Acesso negado', ?string $action = null, ?string $resource = null)
    {
        if ($action && $resource) {
            $message = "Você não tem permissão para {$action} {$resource}";
        }

        parent::__construct($message, $this->statusCode);
    }
}
