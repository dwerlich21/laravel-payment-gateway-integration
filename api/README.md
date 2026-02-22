# API — Laravel 12 Backend

Laravel 12 REST API with multi-gateway payment integration (Stripe & Asaas), cookie-based authentication via Sanctum, and a Repository-Service-Controller architecture.

API REST em Laravel 12 com integração multi-gateway de pagamento (Stripe e Asaas), autenticação via cookies com Sanctum e arquitetura Repository-Service-Controller.

---

## Requirements / Pré-requisitos

- PHP 8.2+
- Composer
- MySQL 8.0+

## Installation / Instalação

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

## Running / Executando

```bash
# Full stack (recommended / recomendado)
composer dev          # Laravel :8000 + queue + pail + Vite

# API only / Somente API
php artisan serve     # http://localhost:8000
```

## Tests / Testes

```bash
php artisan test                      # All tests / Todos os testes
php artisan test --filter=TestName    # Single test / Teste específico
```

### Test Suite / Suíte de Testes

| Test / Teste                              | Type / Tipo | Description / Descrição                                                                            |
|-------------------------------------------|-------------|-----------------------------------------------------------------------------------------------------|
| `PaymentNormalizationTest`                | Unit        | Validates `normalizeWebhookPayload()` for Stripe and Asaas / Valida normalização dos payloads       |
| `WebhookTest`                             | Feature     | Webhook signature validation and job dispatch / Validação de assinatura e despacho de jobs           |
| `CheckoutTest`                            | Feature     | Order creation via checkout endpoint / Criação de pedidos via endpoint de checkout                   |

## Architecture / Arquitetura

### Repository-Service-Controller Pattern

```
Request → Controller → Service → Repository → Model → Database
                         ↕
                    FormRequest
                   (validation)
```

- **Controller** — thin, delegates to Service. Base Controller provides standard CRUD actions.
- **Service** — business logic, data preparation, job dispatching.
- **Repository** — data access layer wrapping Eloquent.
- **FormRequest** — validation rules with `applyTransformations()`.

> Controllers finos, delegam para o Service. Services contêm lógica de negócio. Repositories encapsulam o acesso a dados.

### Payment Integration / Integração de Pagamento

```
┌────────────────┐     ┌──────────────────┐     ┌──────────────────┐
│ PaymentManager │────▶│ PaymentGateway   │────▶│ Stripe / Asaas   │
│  (resolver)    │     │   Interface      │     │   Service        │
└────────────────┘     └──────────────────┘     └──────────────────┘
```

All gateways implement `PaymentGatewayInterface` with 4 methods:
Todos os gateways implementam `PaymentGatewayInterface` com 4 métodos:

| Method / Método             | Description / Descrição                                          |
|-----------------------------|------------------------------------------------------------------|
| `createCharge(array)`       | Create a charge / Criar cobrança                                 |
| `getChargeStatus(string)`   | Query charge status / Consultar status da cobrança               |
| `normalizeWebhookPayload()` | Normalize webhook payload / Normalizar payload do webhook        |
| `validateWebhookSignature()`| Validate webhook signature / Validar assinatura do webhook       |

### Authentication / Autenticação

Cookie-based authentication with Laravel Sanctum:
Autenticação baseada em cookies com Laravel Sanctum:

```
Login → Sanctum tokens → HttpOnly cookies (access 15min + refresh 7d)
         ↓
CookieToTokenMiddleware → auth:sanctum → is.active → permission
```

### Asynchronous Jobs / Jobs Assíncronos

| Job                      | Description / Descrição                                                |
|--------------------------|------------------------------------------------------------------------|
| `CreatePaymentCharge`    | Creates charge on gateway / Cria cobrança no gateway                   |
| `ProcessPaymentWebhook`  | Normalizes payload, updates order / Normaliza payload, atualiza pedido |

### Models / Modelos

| Model     | Description / Descrição                                              |
|-----------|----------------------------------------------------------------------|
| `Product` | Items available for purchase / Itens disponíveis para compra         |
| `Order`   | Purchase records with status tracking / Registros de compra          |
| `Payment` | Payment records from webhooks / Registros de pagamento via webhooks  |

## Key Directories / Diretórios Principais

```
app/
├── Contracts/               # PaymentGatewayInterface
├── Exceptions/              # ApiException, PaymentException, ValidationException
├── Http/
│   ├── Controllers/Api/     # CheckoutController, WebhookController, OrderController
│   └── Middleware/           # CookieToToken, RefreshToken, CheckPermission
├── Jobs/                    # CreatePaymentCharge, ProcessPaymentWebhook
├── Models/                  # Order, Payment, Product, User
├── Repositories/            # BaseRepository, OrderRepository
├── Services/
│   ├── Payment/             # StripeGatewayService, AsaasGatewayService, PaymentManager
│   ├── OrderService.php
│   └── BaseService.php
└── Traits/                  # ExceptionHandlerTrait, Auditable
config/
└── payment.php              # Gateway configuration / Configuração dos gateways
database/
├── migrations/              # products, orders, payments tables
└── seeders/                 # ProductSeeder
routes/
└── api.php                  # All API routes / Todas as rotas da API
tests/
├── Unit/                    # PaymentNormalizationTest
└── Feature/                 # WebhookTest, CheckoutTest
```

## API Routes / Rotas da API

All routes are prefixed with `/api/v1/`.
Todas as rotas possuem o prefixo `/api/v1/`.

### Public / Públicas

| Method | Endpoint              | Description / Descrição                              |
|--------|-----------------------|------------------------------------------------------|
| POST   | `/login`              | Authenticate user / Autenticar usuário               |
| POST   | `/forgot-password`    | Request password reset / Solicitar redefinição        |
| POST   | `/recover-password`   | Reset password / Redefinir senha                     |
| GET    | `/products`           | List products / Listar produtos                      |
| GET    | `/checkout`           | List products for checkout / Listar para checkout    |
| POST   | `/checkout`           | Create order + charge / Criar pedido + cobrança      |

### Webhooks

| Method | Endpoint              | Description / Descrição                              |
|--------|-----------------------|------------------------------------------------------|
| POST   | `/webhooks/stripe`    | Receive Stripe webhooks / Receber webhooks Stripe    |
| POST   | `/webhooks/asaas`     | Receive Asaas webhooks / Receber webhooks Asaas      |

### Authenticated / Autenticadas

| Method | Endpoint              | Description / Descrição                              |
|--------|-----------------------|------------------------------------------------------|
| POST   | `/logout`             | Logout / Sair                                        |
| GET    | `/me`                 | Current user / Usuário atual                         |
| GET    | `/orders`             | List orders / Listar pedidos                         |
| POST   | `/orders`             | Create order (authenticated) / Criar pedido (auth)   |

## Code Style / Estilo de Código

```bash
vendor/bin/pint              # Laravel Pint (PSR-12)
```

## License / Licença

MIT
