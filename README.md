# Payment Gateway Integration

![Build](https://github.com/YOUR_USER/laravel-payment-gateway-integration/actions/workflows/tests.yml/badge.svg)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)
![Laravel](https://img.shields.io/badge/Laravel-12-red)
![Vue](https://img.shields.io/badge/Vue-3-green)

Multi-gateway payment integration for Laravel, supporting **Stripe** and **Asaas** with a unified interface. Handles checkout, asynchronous webhook processing, and automatic order status updates.

Integração multi-gateway de pagamento para Laravel, com suporte a **Stripe** e **Asaas** por meio de uma interface unificada. Gerencia checkout, processamento assíncrono de webhooks e atualização automática de status de pedidos.

---

## Architecture / Arquitetura

```
┌──────────┐     ┌─────────┐     ┌─────────┐     ┌───────────┐     ┌────────┐
│  Client   │────▶│Checkout │────▶│ Gateway │────▶│  Webhook  │────▶│ Update │
│ (Browser) │     │  Order  │     │ Charge  │     │ Normalize │     │ Order  │
└──────────┘     └─────────┘     └─────────┘     └───────────┘     └────────┘
                      │                                │
                      ▼                                ▼
                 ┌─────────┐                    ┌───────────┐
                 │  orders │                    │ payments  │
                 │  table  │                    │  table    │
                 └─────────┘                    └───────────┘
```

**Flow / Fluxo:** `POST /checkout` → Create Order / Criar Pedido → Dispatch charge to gateway / Despachar cobrança ao gateway → Gateway sends webhook / Gateway envia webhook → Normalize payload / Normalizar payload → Update order status / Atualizar status do pedido

## Gateway Comparison / Comparativo de Gateways

| Feature / Recurso               | Stripe                                 | Asaas                                   |
|----------------------------------|----------------------------------------|-----------------------------------------|
| Checkout endpoint                | Stripe Checkout Session API            | REST API `POST /payments`               |
| Authentication / Autenticação    | Secret key via SDK                     | Header `access_token`                   |
| Webhook validation / Validação   | HMAC signature (`Stripe-Signature`)    | Token comparison (`asaas-access-token`) |
| Status mapping / Mapeamento      | `complete` → paid, `expired` → failed | `CONFIRMED` → paid, `OVERDUE` → failed |
| Currency / Moeda                 | Cents (e.g., 2999 = R$29.99)          | Reais (e.g., 29.99)                    |

## Project Structure / Estrutura do Projeto

```
api/     — Laravel 12 backend (PHP 8.2+, Sanctum, MySQL)
front/   — Vue 3 SPA (Composition API, Pinia, Bootstrap 5, Vite)
```

```
api/
├── app/
│   ├── Contracts/           # PaymentGatewayInterface
│   ├── Http/Controllers/    # CheckoutController, WebhookController
│   ├── Jobs/                # CreatePaymentCharge, ProcessPaymentWebhook
│   ├── Models/              # Order, Payment, Product
│   └── Services/Payment/    # StripeGatewayService, AsaasGatewayService, PaymentManager
├── config/payment.php       # Gateway configuration / Configuração dos gateways
├── database/migrations/     # orders, payments, products tables
└── tests/                   # Unit & Feature tests
front/
├── src/
│   ├── components/base/     # Crud.vue, TableLists.vue, ModalForm.vue
│   ├── composables/         # useCrud, messages, masks, setSessions
│   ├── http/                # Axios (withCredentials: true)
│   ├── router/              # Vue Router with auth guards
│   ├── services/            # BaseService, ProductService, OrderService
│   ├── stores/              # Pinia (auth, layout, notification)
│   └── views/               # Login, Dashboard, Checkout, Users
```

## Getting Started / Primeiros Passos

### Prerequisites / Pré-requisitos

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+ & Yarn

### Installation / Instalação

```bash
# Clone
git clone https://github.com/YOUR_USER/laravel-payment-gateway-integration.git
cd laravel-payment-gateway-integration

# Backend
cd api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed

# Frontend
cd ../front
yarn install
```

### Docker Compose

```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
```

### Running / Executando

```bash
# Full stack (from api/ directory / a partir do diretório api/)
composer dev

# Or separately / Ou separadamente:
cd api && php artisan serve          # API on :8000
cd front && yarn dev                 # SPA on :8080
```

### Running Tests / Executando Testes

```bash
cd api
php artisan test
```

## Local Webhook Testing / Teste Local de Webhooks

### Stripe CLI

```bash
stripe listen --forward-to localhost:8000/api/v1/webhooks/stripe
# Copy the webhook signing secret to STRIPE_WEBHOOK_SECRET in .env
# Copie o segredo de assinatura do webhook para STRIPE_WEBHOOK_SECRET no .env
```

### ngrok (Asaas)

```bash
ngrok http 8000
# Set the ngrok URL as webhook endpoint in the Asaas dashboard
# Defina a URL do ngrok como endpoint de webhook no painel do Asaas
```

## Environment Variables / Variáveis de Ambiente

| Variable / Variável         | Description / Descrição                                          | Default  |
|-----------------------------|------------------------------------------------------------------|----------|
| `PAYMENT_DEFAULT_GATEWAY`   | Default gateway / Gateway padrão                                 | `stripe` |
| `STRIPE_SECRET_KEY`         | Stripe API secret key / Chave secreta da API Stripe              |          |
| `STRIPE_PUBLISHABLE_KEY`    | Stripe publishable key / Chave publicável do Stripe              |          |
| `STRIPE_WEBHOOK_SECRET`     | Stripe webhook signing secret / Segredo de assinatura do webhook |          |
| `ASAAS_API_KEY`             | Asaas API key / Chave da API Asaas                               |          |
| `ASAAS_WEBHOOK_TOKEN`       | Asaas webhook validation token / Token de validação do webhook   |          |
| `ASAAS_SANDBOX`             | Use Asaas sandbox / Usar ambiente sandbox do Asaas               | `true`   |

## API Endpoints

| Method | Endpoint                    | Description / Descrição                                  |
|--------|-----------------------------|----------------------------------------------------------|
| GET    | `/api/v1/checkout`          | List available products / Listar produtos disponíveis    |
| POST   | `/api/v1/checkout`          | Create order + charge / Criar pedido + cobrança          |
| POST   | `/api/v1/webhooks/stripe`   | Receive Stripe webhooks / Receber webhooks do Stripe     |
| POST   | `/api/v1/webhooks/asaas`    | Receive Asaas webhooks / Receber webhooks do Asaas       |

## License / Licença

MIT
