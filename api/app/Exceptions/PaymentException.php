<?php

namespace App\Exceptions;

class PaymentException extends ApiException
{
    protected int $statusCode = 422;

    /**
     * Cria uma nova exceção de pagamento
     */
    public function __construct(string $message, array $details = [])
    {
        parent::__construct($message, $this->statusCode, $details);
    }
}
