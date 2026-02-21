<?php

namespace App\Exceptions;

use Exception;

class IntegrationException extends Exception
{
    protected string $source;
    protected array $context;

    public function __construct(
        string $message,
        string $source = '',
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->source = $source;
        $this->context = $context;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
