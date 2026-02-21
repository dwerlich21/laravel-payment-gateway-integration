<?php

namespace App\Traits;

use App\Exceptions\BusinessException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ServerException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

trait ExceptionHandlerTrait
{
    /**
     * Executa uma função e trata exceções automaticamente
     *
     * @param  callable  $callback  Função a ser executada
     * @param  string  $defaultMessage  Mensagem padrão de erro
     * @param  bool  $useTransaction  Se deve usar transação automática
     *
     * @throws ValidationException|BusinessException|NotFoundException|UnauthorizedException|ForbiddenException|ServerException
     * @throws Throwable
     */
    protected function handleExceptions(callable $callback, string $defaultMessage = 'Erro interno', bool $useTransaction = false): JsonResponse
    {
        if ($useTransaction) {
            DB::beginTransaction();
        }

        try {
            $result = $callback();

            if ($useTransaction) {
                DB::commit();
            }

            return $result;

        } catch (ValidationException|BusinessException|NotFoundException|UnauthorizedException|ForbiddenException $e) {
            if ($useTransaction) {
                DB::rollBack();
            }
            throw $e;
        } catch (\Exception $e) {
            if ($useTransaction) {
                DB::rollBack();
            }

            Log::error($defaultMessage, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new ServerException($defaultMessage, $e);
        }
    }

    /**
     * Executa função COM transação automática
     *
     * @param  callable  $callback  Função a ser executada
     * @param  string  $defaultMessage  Mensagem padrão de erro
     */
    protected function handleWithTransaction(callable $callback, string $defaultMessage = 'Erro interno'): JsonResponse
    {
        return $this->handleExceptions($callback, $defaultMessage, true);
    }

    /**
     * Executa função SEM transação automática
     *
     * @param  callable  $callback  Função a ser executada
     * @param  string  $defaultMessage  Mensagem padrão de erro
     */
    protected function handleWithoutTransaction(callable $callback, string $defaultMessage = 'Erro interno'): JsonResponse
    {
        return $this->handleExceptions($callback, $defaultMessage, false);
    }

    /**
     * Retorna resposta de sucesso padronizada
     *
     * @param  mixed|null  $data  Dados a serem retornados
     * @param  string  $message  Mensagem de sucesso
     * @param  int  $status  Código de status HTTP
     */
    protected function successResponse(mixed $data = null, string $message = 'Operação realizada com sucesso', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
