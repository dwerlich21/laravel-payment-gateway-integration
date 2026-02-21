<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Serviço de integração com o Asaas.
 *
 * Implementa a interface de gateway utilizando a API REST do Asaas
 * para criação de cobranças (boleto, pix, cartão) e consulta de status.
 */
class AsaasGatewayService implements PaymentGatewayInterface
{
    private string $apiKey;

    private string $webhookToken;

    private string $baseUrl;

    public function __construct(string $apiKey, string $webhookToken, bool $sandbox = true)
    {
        $this->apiKey = $apiKey;
        $this->webhookToken = $webhookToken;
        $this->baseUrl = $sandbox
            ? 'https://sandbox.asaas.com/api/v3'
            : 'https://api.asaas.com/api/v3';
    }

    /**
     * Cria uma cobrança no Asaas.
     *
     * Envia POST para /payments com os dados do cliente, tipo de cobrança,
     * valor e descrição. Retorna o ID externo, status e URL do boleto/invoice.
     */
    public function createCharge(array $data): array
    {
        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
            ])->post("{$this->baseUrl}/payments", [
                'customer' => $data['customer'],
                'billingType' => $data['billing_type'] ?? 'UNDEFINED',
                'value' => $data['amount'],
                'description' => $data['description'] ?? 'Pagamento',
                'dueDate' => $data['due_date'] ?? now()->addDays(3)->format('Y-m-d'),
            ]);

            if ($response->failed()) {
                $errors = $response->json('errors.0.description', 'Erro desconhecido');
                throw new PaymentException("Erro na API do Asaas: {$errors}");
            }

            $body = $response->json();

            return [
                'external_id' => $body['id'],
                'status' => 'pending',
                'invoice_url' => $body['invoiceUrl'] ?? null,
            ];
        } catch (PaymentException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new PaymentException("Erro ao criar cobrança no Asaas: {$e->getMessage()}");
        }
    }

    /**
     * Consulta o status de uma cobrança no Asaas pelo ID externo.
     *
     * Mapeia os status do Asaas para o formato interno:
     * RECEIVED/CONFIRMED → paid, OVERDUE/REFUNDED → failed, demais → pending.
     */
    public function getChargeStatus(string $externalId): string
    {
        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
            ])->get("{$this->baseUrl}/payments/{$externalId}");

            if ($response->failed()) {
                throw new PaymentException('Erro ao consultar cobrança no Asaas.');
            }

            $status = $response->json('status');

            return $this->mapAsaasStatus($status);
        } catch (PaymentException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new PaymentException("Erro ao consultar status no Asaas: {$e->getMessage()}");
        }
    }

    /**
     * Normaliza o payload do webhook do Asaas para o formato interno.
     *
     * Extrai dados de $payload['payment']: ID externo, status normalizado,
     * valor e data de pagamento.
     */
    public function normalizeWebhookPayload(array $payload): array
    {
        try {
            $payment = $payload['payment'] ?? [];

            return [
                'external_id' => $payment['id'] ?? null,
                'status' => $this->mapAsaasStatus($payment['status'] ?? ''),
                'amount' => $payment['value'] ?? 0,
                'paid_at' => $payment['paymentDate'] ?? null,
            ];
        } catch (\Exception $e) {
            throw new PaymentException("Erro ao normalizar webhook do Asaas: {$e->getMessage()}");
        }
    }

    /**
     * Valida a assinatura do webhook do Asaas.
     *
     * Compara o header asaas-access-token da requisição com o
     * webhook_token configurado para verificar a autenticidade.
     */
    public function validateWebhookSignature(Request $request): bool
    {
        $token = $request->header('asaas-access-token', '');

        return hash_equals($this->webhookToken, $token);
    }

    /**
     * Mapeia o status do Asaas para o formato interno padronizado.
     */
    private function mapAsaasStatus(string $status): string
    {
        return match ($status) {
            'RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH' => 'paid',
            'OVERDUE', 'REFUNDED', 'REFUND_REQUESTED', 'CHARGEBACK' => 'failed',
            default => 'pending',
        };
    }
}
