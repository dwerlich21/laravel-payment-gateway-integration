<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentException;
use Illuminate\Http\Request;

/**
 * Serviço de integração com o Asaas.
 *
 * Implementa a interface de gateway para o Asaas. A implementação real
 * das chamadas à API será feita nas próximas tasks — por enquanto os
 * métodos contêm stubs que indicam a pendência de implementação.
 */
class AsaasGatewayService implements PaymentGatewayInterface
{
    /**
     * Cria uma cobrança no Asaas.
     *
     * Na implementação final, usará a API do Asaas para criar uma
     * cobrança (boleto, pix ou cartão) e retornará os dados relevantes.
     */
    public function createCharge(array $data): array
    {
        throw new PaymentException('Implementação Asaas pendente: createCharge');
    }

    /**
     * Consulta o status de uma cobrança no Asaas pelo ID externo.
     *
     * Na implementação final, consultará a API do Asaas para obter
     * o status atual da cobrança.
     */
    public function getChargeStatus(string $externalId): string
    {
        throw new PaymentException('Implementação Asaas pendente: getChargeStatus');
    }

    /**
     * Normaliza o payload do webhook recebido do Asaas.
     *
     * O Asaas envia notificações via webhook com uma estrutura própria.
     * Este método converterá o payload para o formato interno padronizado.
     */
    public function normalizeWebhookPayload(array $payload): array
    {
        throw new PaymentException('Implementação Asaas pendente: normalizeWebhookPayload');
    }

    /**
     * Valida a assinatura do webhook do Asaas.
     *
     * Usa o token de webhook configurado para verificar a autenticidade
     * da requisição recebida do Asaas.
     */
    public function validateWebhookSignature(Request $request): bool
    {
        throw new PaymentException('Implementação Asaas pendente: validateWebhookSignature');
    }
}
