Contexto: uma mini loja com integração a múltiplos gateways de pagamento (Stripe e Asaas),
demonstrando abstração, normalização de dados e processamento de webhooks.

Estrutura de pastas que quero:
- app/Contracts/PaymentGatewayInterface.php
- app/Services/Payment/StripeGatewayService.php
- app/Services/Payment/AsaasGatewayService.php
- app/Services/Payment/PaymentManager.php (resolve qual gateway usar)
- app/Jobs/ProcessPaymentWebhook.php
- app/Jobs/CreatePaymentCharge.php
- app/Models/Order.php
- app/Models/Payment.php
- app/Models/Product.php

O PaymentGatewayInterface deve ter os métodos:
- createCharge(array $data): array
- getChargeStatus(string $externalId): string
- normalizeWebhookPayload(array $payload): array (retorna formato padronizado)
- validateWebhookSignature(Request $request): bool

O formato normalizado de pagamento deve ser:
{
  external_id, gateway, status, amount, fees, net_amount,
  paid_at, customer_email, metadata
}

Use comentários em português explicando cada decisão arquitetural.