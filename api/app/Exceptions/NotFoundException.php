<?php

namespace App\Exceptions;

class NotFoundException extends ApiException
{
    protected int $statusCode = 404;

    /**
     * Cria uma nova exceção de registro não encontrado
     *
     * @param  mixed  $id
     */
    public function __construct(string $message = 'Registro não encontrado', ?string $model = null, $id = null)
    {
        if ($model && $id) {
            $message = "Registro de {$model} com ID {$id} não foi encontrado";
        } elseif ($model) {
            $message = "Registro de {$model} não foi encontrado";
        }

        parent::__construct($message, $this->statusCode);
    }
}
