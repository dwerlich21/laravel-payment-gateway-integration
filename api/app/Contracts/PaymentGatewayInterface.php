<?php

namespace App\Contracts;

use Illuminate\Http\Request;

/**
 * Interface para gateways de pagamento.
 *
 * Define o contrato que todos os gateways (Stripe, Asaas, etc.) devem seguir,
 * garantindo uma API uniforme independente do provedor utilizado.
 */
interface PaymentGatewayInterface
{
    /**
     * Cria uma cobrança no gateway de pagamento.
     *
     * Recebe os dados do pedido e retorna um array com as informações
     * da cobrança criada (external_id, status, url de pagamento, etc.).
     */
    public function createCharge(array $data): array;

    /**
     * Consulta o status de uma cobrança no gateway pelo ID externo.
     *
     * Retorna o status normalizado da cobrança (pending, paid, failed, refunded).
     */
    public function getChargeStatus(string $externalId): string;

    /**
     * Normaliza o payload do webhook recebido do gateway.
     *
     * Cada gateway envia webhooks em formatos diferentes. Este método
     * converte o payload para um formato padronizado interno.
     */
    public function normalizeWebhookPayload(array $payload): array;

    /**
     * Valida a assinatura do webhook recebido do gateway.
     *
     * Verifica se a requisição realmente veio do gateway, usando
     * a chave secreta do webhook para validar a assinatura.
     */
    public function validateWebhookSignature(Request $request): bool;
}
