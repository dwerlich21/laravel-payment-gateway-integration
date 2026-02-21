Crie os controllers e jobs para laravel-payment-gateway-integration:

CheckoutController:
- index(): lista produtos ativos
- store(Request $request): valida product_id e gateway,
  cria Order com status pending,
  dispara CreatePaymentCharge job,
  retorna order com link de pagamento

WebhookController:
- handleStripe(Request $request): valida assinatura, dispara ProcessPaymentWebhook
- handleAsaas(Request $request): valida assinatura, dispara ProcessPaymentWebhook
- ambos retornam 200 imediatamente (antes de processar — boa prática de webhook)

CreatePaymentCharge Job:
- recebe Order
- chama PaymentManager::gateway($order->gateway)->createCharge()
- salva o external_id na Order
- em caso de falha: marca order como failed e loga o erro

ProcessPaymentWebhook Job:
- recebe gateway e payload raw
- chama normalizeWebhookPayload()
- encontra Order pelo external_id
- cria registro em Payment com raw e normalized payload
- atualiza status da Order
- em caso de Order não encontrada: loga e descarta silenciosamente

Rotas:
POST /checkout → CheckoutController@store
GET /checkout → CheckoutController@index
POST /webhooks/stripe → WebhookController@handleStripe (sem middleware auth)
POST /webhooks/asaas → WebhookController@handleAsaas (sem middleware auth)

Adicione rate limiting de 60req/min nos webhooks.