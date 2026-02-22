# Payment Gateway Integration

![Build](https://github.com/YOUR_USER/laravel-payment-gateway-integration/actions/workflows/tests.yml/badge.svg)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)
![Laravel](https://img.shields.io/badge/Laravel-12-red)

Multi-gateway payment integration for Laravel, supporting **Stripe** and **Asaas** with a unified interface. Handles checkout, asynchronous webhook processing, and automatic order status updates.

## Architecture

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

**Flow:** `POST /checkout` → Create Order → Dispatch charge to gateway → Gateway sends webhook → Normalize payload → Update order status

## Gateway Comparison

| Feature              | Stripe                                 | Asaas                                   |
|----------------------|----------------------------------------|-----------------------------------------|
| Checkout endpoint    | Stripe Checkout Session API            | REST API `POST /payments`               |
| Authentication       | Secret key via SDK                     | `access_token` header                   |
| Webhook validation   | HMAC signature (`Stripe-Signature`)    | Token comparison (`asaas-access-token`) |
| Status mapping       | `complete` → paid, `expired` → failed | `CONFIRMED` → paid, `OVERDUE` → failed |
| Currency             | Amount in cents (e.g., 2999 = R$29.99) | Amount in reais (e.g., 29.99)           |

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+ & Yarn

### Installation

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

### Running

```bash
# Full stack (from api/ directory)
composer dev

# Or separately:
cd api && php artisan serve          # API on :8000
cd front && yarn dev                 # SPA on :8080
```

### Running Tests

```bash
cd api
php artisan test
```

## Local Webhook Testing

### Stripe CLI

```bash
stripe listen --forward-to localhost:8000/api/v1/webhooks/stripe
# Copy the webhook signing secret to STRIPE_WEBHOOK_SECRET in .env
```

### ngrok (for Asaas)

```bash
ngrok http 8000
# Set the ngrok URL as webhook endpoint in the Asaas dashboard
```

## Environment Variables

| Variable                  | Description                           | Default  |
|---------------------------|---------------------------------------|----------|
| `PAYMENT_DEFAULT_GATEWAY` | Default payment gateway               | `stripe` |
| `STRIPE_SECRET_KEY`       | Stripe API secret key                 |          |
| `STRIPE_PUBLISHABLE_KEY`  | Stripe publishable key                |          |
| `STRIPE_WEBHOOK_SECRET`   | Stripe webhook signing secret         |          |
| `ASAAS_API_KEY`           | Asaas API key                         |          |
| `ASAAS_WEBHOOK_TOKEN`     | Asaas webhook validation token        |          |
| `ASAAS_SANDBOX`           | Use Asaas sandbox environment         | `true`   |

## API Endpoints

| Method | Endpoint                    | Description                |
|--------|-----------------------------|----------------------------|
| GET    | `/api/v1/checkout`          | List available products    |
| POST   | `/api/v1/checkout`          | Create order + charge      |
| POST   | `/api/v1/webhooks/stripe`   | Receive Stripe webhooks    |
| POST   | `/api/v1/webhooks/asaas`    | Receive Asaas webhooks     |

## Project Structure

```
api/
├── app/
│   ├── Contracts/           # PaymentGatewayInterface
│   ├── Http/Controllers/    # CheckoutController, WebhookController
│   ├── Jobs/                # CreatePaymentCharge, ProcessPaymentWebhook
│   ├── Models/              # Order, Payment, Product
│   └── Services/Payment/    # StripeGatewayService, AsaasGatewayService, PaymentManager
├── config/payment.php       # Gateway configuration
├── database/migrations/     # orders, payments, products tables
└── tests/                   # Unit & Feature tests
front/
├── src/                     # Vue 3 SPA
```

## License

MIT
