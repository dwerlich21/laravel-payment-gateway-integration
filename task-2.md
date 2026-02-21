Crie as migrations e models para o projeto laravel-payment-gateway-integration:

products: id, name, description, price, active, timestamps
orders: id, user_id (nullable), product_id, quantity, total_amount,
        status (pending/paid/failed/refunded), gateway (stripe/asaas), timestamps
payments: id, order_id, external_id, gateway, status, amount, fees,
          net_amount, paid_at, raw_payload (json), normalized_payload (json), timestamps

No Model Order:
- belongs to Product
- has many Payments
- método scopePending(), scopePaid()
- método isPaid(): bool

No Model Payment:
- belongs to Order
- cast raw_payload e normalized_payload como array
- método isSuccessful(): bool

Adicione seeders com 3 produtos fictícios (ex: Plano Basic $9.99, Plano Pro $29.99, Plano Enterprise $99.99).