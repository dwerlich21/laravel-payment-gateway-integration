Crie os testes PHPUnit e README para laravel-payment-gateway-integration:

Testes (tests/Unit e tests/Feature):

PaymentNormalizationTest:
- test_stripe_payload_normalizes_correctly()
- test_asaas_payload_normalizes_correctly()
- test_normalized_format_has_required_fields()

WebhookTest:
- test_stripe_webhook_with_invalid_signature_returns_401()
- test_asaas_webhook_with_invalid_signature_returns_401()
- test_valid_stripe_webhook_dispatches_job()
- test_valid_asaas_webhook_dispatches_job()
- test_webhook_returns_200_before_processing() (Queue::fake())

CheckoutTest:
- test_can_create_order_with_stripe()
- test_can_create_order_with_asaas()
- test_invalid_product_returns_422()

Use Http::fake() para mockar as APIs externas nos testes.

README.md em inglês com:
- Badges: build status, PHP version, Laravel version
- Diagrama ASCII do fluxo completo (Order → Gateway → Webhook → Normalize → Update)
- Tabela comparando Stripe vs Asaas (endpoint, auth method, webhook validation)
- Como rodar com Docker Compose
- Como testar webhooks localmente com Stripe CLI / ngrok
- .env.example com todas as variáveis necessárias comentadas

GitHub Actions (.github/workflows/tests.yml):
- Trigger: push e pull_request na branch main
- PHP 8.2, MySQL service
- composer install, php artisan migrate, php artisan test