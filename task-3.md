Implemente os dois gateways no projeto laravel-payment-gateway-integration:

StripeGatewayService implementando PaymentGatewayInterface:
- createCharge: usar Stripe Checkout Session (stripe/stripe-php)
- getChargeStatus: buscar PaymentIntent por ID
- normalizeWebhookPayload: mapear evento checkout.session.completed para o formato padrão
- validateWebhookSignature: usar Stripe::constructEvent() com webhook secret
- Status mapping: 'complete' → 'paid', 'expired' → 'failed', 'open' → 'pending'

AsaasGatewayService implementando PaymentGatewayInterface:
- createCharge: criar cobrança via POST /payments na API Asaas sandbox
- getChargeStatus: GET /payments/{id}
- normalizeWebhookPayload: mapear evento PAYMENT_RECEIVED para o formato padrão
- validateWebhookSignature: validar header asaas-access-token
- Status mapping: 'RECEIVED' → 'paid', 'OVERDUE' → 'failed', 'PENDING' → 'pending'

PaymentManager:
- método gateway(string $name): PaymentGatewayInterface
- resolve via app container (config em config/payment.php)
- default gateway configurável via .env PAYMENT_DEFAULT_GATEWAY=stripe

Todas as chamadas externas devem ter try/catch lançando PaymentException customizada.
Comentários em português em cada método explicando o que faz.