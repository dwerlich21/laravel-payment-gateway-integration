<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentException;
use Illuminate\Http\Request;

/**
 * Serviço de integração com o Stripe.
 *
 * Implementa a interface de gateway para o Stripe. A implementação real
 * das chamadas à API será feita nas próximas tasks — por enquanto os
 * métodos contêm stubs que indicam a pendência de implementação.
 */
class StripeGatewayService implements PaymentGatewayInterface
{
    /**
     * Cria uma cobrança no Stripe.
     *
     * Na implementação final, usará a API do Stripe para criar um
     * Payment Intent ou Checkout Session e retornará os dados relevantes.
     */
    public function createCharge(array $data): array
    {
        throw new PaymentException('Implementação Stripe pendente: createCharge');
    }

    /**
     * Consulta o status de uma cobrança no Stripe pelo ID externo.
     *
     * Na implementação final, consultará a API do Stripe para obter
     * o status atual do Payment Intent.
     */
    public function getChargeStatus(string $externalId): string
    {
        throw new PaymentException('Implementação Stripe pendente: getChargeStatus');
    }

    /**
     * Normaliza o payload do webhook recebido do Stripe.
     *
     * O Stripe envia eventos via webhook com uma estrutura própria.
     * Este método converterá o payload para o formato interno padronizado.
     */
    public function normalizeWebhookPayload(array $payload): array
    {
        throw new PaymentException('Implementação Stripe pendente: normalizeWebhookPayload');
    }

    /**
     * Valida a assinatura do webhook do Stripe.
     *
     * Usa o webhook secret configurado para verificar o header
     * Stripe-Signature e garantir a autenticidade da requisição.
     */
    public function validateWebhookSignature(Request $request): bool
    {
        throw new PaymentException('Implementação Stripe pendente: validateWebhookSignature');
    }
}
