<?php

namespace App\Exceptions;

use Exception;

abstract class ApiException extends Exception
{
    /**
     * Status code HTTP da resposta
     */
    protected int $statusCode = 500;

    /**
     * Dados adicionais do erro
     *
     * @var mixed
     */
    protected $errorData = null;

    /**
     * Construtor da exceção
     *
     * @param  mixed  $errorData
     */
    public function __construct(string $message = '', int $statusCode = 0, $errorData = null, ?Exception $previous = null)
    {
        if ($statusCode !== 0) {
            $this->statusCode = $statusCode;
        }

        $this->errorData = $errorData;

        parent::__construct($message, $this->statusCode, $previous);
    }

    /**
     * Retorna o status code da exceção
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Retorna os dados adicionais do erro
     *
     * @return mixed
     */
    public function getErrorData()
    {
        return $this->errorData;
    }

    /**
     * Renderiza a exceção como resposta JSON
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render()
    {
        $response = [
            'success' => false,
            'message' => $this->getMessage() ?: 'Erro ao processar solicitação',
            'code' => $this->statusCode,
        ];

        if ($this->errorData !== null) {
            $response['errors'] = $this->errorData;
        }

        return response()->json($response, $this->statusCode);
    }
}
