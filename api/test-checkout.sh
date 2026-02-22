#!/bin/bash
#
# Script para testar o fluxo completo de checkout + webhook
# Uso: bash test-checkout.sh
#
# Pré-requisitos:
#   - php artisan serve rodando na porta 8000
#   - php artisan queue:listen --tries=1 rodando
#
# Nota: NÃO precisa de 'stripe listen' — o webhook é simulado
# diretamente via artisan tinker com o external_id correto.
#

API="http://localhost:8000/api/v1"
API_DIR="/home/dwerlich/projetos/laravel-payment-gateway-integration/api"
BOLD="\033[1m"
GREEN="\033[32m"
RED="\033[31m"
YELLOW="\033[33m"
CYAN="\033[36m"
RESET="\033[0m"

divider() {
    echo ""
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${RESET}"
    echo ""
}

# ──────────────────────────────────────────────────────────────
# 1. Listar produtos disponíveis
# ──────────────────────────────────────────────────────────────
divider
echo -e "${BOLD}[1/6] Listando produtos disponíveis...${RESET}"

PRODUCTS=$(curl -s "$API/checkout")
echo "$PRODUCTS" | python3 -m json.tool 2>/dev/null || echo "$PRODUCTS"

PRODUCT_ID=$(echo "$PRODUCTS" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data['data'][0]['id'])" 2>/dev/null)

if [ -z "$PRODUCT_ID" ]; then
    echo -e "${RED}Nenhum produto encontrado. Rode: php artisan db:seed${RESET}"
    exit 1
fi

PRODUCT_NAME=$(echo "$PRODUCTS" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data['data'][0]['name'])" 2>/dev/null)
PRODUCT_PRICE=$(echo "$PRODUCTS" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data['data'][0]['price'])" 2>/dev/null)

echo -e "${GREEN}Produto selecionado: $PRODUCT_NAME (ID: $PRODUCT_ID) - R\$ $PRODUCT_PRICE${RESET}"

# ──────────────────────────────────────────────────────────────
# 2. Criar checkout (sem autenticação)
# ──────────────────────────────────────────────────────────────
divider
echo -e "${BOLD}[2/6] Criando pedido via checkout (guest)...${RESET}"

CHECKOUT_RESPONSE=$(curl -s -X POST "$API/checkout" \
    -H "Content-Type: application/json" \
    -d "{
        \"product_id\": $PRODUCT_ID,
        \"gateway\": \"stripe\",
        \"quantity\": 1,
        \"payment_method\": \"credit_card\",
        \"customer_name\": \"Cliente Teste\",
        \"customer_email\": \"teste@exemplo.com\",
        \"customer_cpf_cnpj\": \"12345678901\",
        \"customer_phone\": \"11999999999\"
    }")

echo "$CHECKOUT_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$CHECKOUT_RESPONSE"

ORDER_ID=$(echo "$CHECKOUT_RESPONSE" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data['data']['id'])" 2>/dev/null)
EXTERNAL_ID=$(echo "$CHECKOUT_RESPONSE" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data['data']['external_id'] or 'null')" 2>/dev/null)
PAYMENT_URL=$(echo "$CHECKOUT_RESPONSE" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data['data']['payment_url'] or 'null')" 2>/dev/null)
ORDER_STATUS=$(echo "$CHECKOUT_RESPONSE" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data['data']['status'])" 2>/dev/null)

if [ -z "$ORDER_ID" ]; then
    echo -e "${RED}Falha ao criar pedido!${RESET}"
    exit 1
fi

echo ""
echo -e "${GREEN}Pedido criado com sucesso!${RESET}"
echo -e "  Order ID:    ${BOLD}$ORDER_ID${RESET}"
echo -e "  External ID: ${BOLD}$EXTERNAL_ID${RESET}"
echo -e "  Status:      ${BOLD}$ORDER_STATUS${RESET}"
echo -e "  Payment URL: ${BOLD}${PAYMENT_URL:0:80}...${RESET}"

# ──────────────────────────────────────────────────────────────
# 3. Verificar order no banco (antes do webhook)
# ──────────────────────────────────────────────────────────────
divider
echo -e "${BOLD}[3/6] Verificando order no banco (antes do webhook)...${RESET}"

DB_CHECK=$(cd "$API_DIR" && php artisan tinker --execute="
    \$o = App\Models\Order::find($ORDER_ID);
    echo json_encode([
        'id' => \$o->id,
        'user_id' => \$o->user_id,
        'status' => \$o->status,
        'external_id' => \$o->external_id,
        'gateway' => \$o->gateway,
        'total_amount' => \$o->total_amount,
        'customer_name' => \$o->customer_name,
    ]);
" 2>&1 | grep -v Warning | grep -v "^$")

echo "$DB_CHECK" | python3 -m json.tool 2>/dev/null || echo "$DB_CHECK"

USER_ID=$(echo "$DB_CHECK" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data['user_id'])" 2>/dev/null)
echo ""
if [ "$USER_ID" = "None" ] || [ "$USER_ID" = "null" ]; then
    echo -e "${GREEN}user_id = null (guest checkout OK)${RESET}"
else
    echo -e "${YELLOW}user_id = $USER_ID (compra autenticada)${RESET}"
fi

# ──────────────────────────────────────────────────────────────
# 4. Simular webhook checkout.session.completed
# ──────────────────────────────────────────────────────────────
divider
echo -e "${BOLD}[4/6] Simulando webhook checkout.session.completed...${RESET}"
echo -e "${YELLOW}Despachando ProcessPaymentWebhook com external_id=$EXTERNAL_ID${RESET}"
echo ""

WEBHOOK_RESULT=$(cd "$API_DIR" && php artisan tinker --execute="
    \$payload = [
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => '$EXTERNAL_ID',
                'object' => 'checkout.session',
                'status' => 'complete',
                'payment_status' => 'paid',
                'amount_total' => $(echo "$PRODUCT_PRICE * 100" | bc | cut -d. -f1),
                'currency' => 'brl',
                'created' => time(),
                'payment_intent' => 'pi_simulated_' . time(),
                'metadata' => ['order_id' => $ORDER_ID],
            ],
        ],
    ];

    // Despachar sincronamente para ver resultado imediato
    \$job = new App\Jobs\ProcessPaymentWebhook('stripe', \$payload);
    dispatch_sync(\$job);

    \$order = App\Models\Order::find($ORDER_ID);
    echo json_encode([
        'status' => \$order->status,
        'payments_count' => \$order->payments()->count(),
    ]);
" 2>&1 | grep -v Warning | grep -v "^$")

echo "$WEBHOOK_RESULT" | python3 -m json.tool 2>/dev/null || echo "$WEBHOOK_RESULT"

# ──────────────────────────────────────────────────────────────
# 5. Verificar status final da order
# ──────────────────────────────────────────────────────────────
divider
echo -e "${BOLD}[5/6] Verificando order após webhook...${RESET}"

DB_FINAL=$(cd "$API_DIR" && php artisan tinker --execute="
    \$o = App\Models\Order::find($ORDER_ID);
    \$p = \$o->payments()->first();
    echo json_encode([
        'order_id' => \$o->id,
        'status' => \$o->status,
        'external_id' => \$o->external_id,
        'payment' => \$p ? [
            'id' => \$p->id,
            'status' => \$p->status,
            'amount' => \$p->amount,
            'gateway' => \$p->gateway,
            'paid_at' => \$p->paid_at,
        ] : null,
    ]);
" 2>&1 | grep -v Warning | grep -v "^$")

echo "$DB_FINAL" | python3 -m json.tool 2>/dev/null || echo "$DB_FINAL"

FINAL_STATUS=$(echo "$DB_FINAL" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data['status'])" 2>/dev/null)

echo ""
if [ "$FINAL_STATUS" = "paid" ]; then
    echo -e "${GREEN}${BOLD}SUCESSO! Pedido #$ORDER_ID atualizado para 'paid'!${RESET}"
elif [ "$FINAL_STATUS" = "pending" ]; then
    echo -e "${YELLOW}${BOLD}Status ainda 'pending'. Verifique os logs abaixo.${RESET}"
else
    echo -e "${RED}${BOLD}Status: $FINAL_STATUS${RESET}"
fi

# ──────────────────────────────────────────────────────────────
# 6. Simular webhook de falha (pagamento recusado)
# ──────────────────────────────────────────────────────────────
divider
echo -e "${BOLD}[6/6] Testando pagamento recusado (novo pedido)...${RESET}"

FAIL_RESPONSE=$(curl -s -X POST "$API/checkout" \
    -H "Content-Type: application/json" \
    -d "{
        \"product_id\": $PRODUCT_ID,
        \"gateway\": \"stripe\",
        \"quantity\": 1,
        \"payment_method\": \"credit_card\",
        \"customer_name\": \"Cliente Falha\",
        \"customer_email\": \"falha@exemplo.com\",
        \"customer_cpf_cnpj\": \"99999999999\",
        \"customer_phone\": \"11888888888\"
    }")

FAIL_ORDER_ID=$(echo "$FAIL_RESPONSE" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data['data']['id'])" 2>/dev/null)
FAIL_EXTERNAL_ID=$(echo "$FAIL_RESPONSE" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data['data']['external_id'] or 'null')" 2>/dev/null)

echo -e "Pedido #$FAIL_ORDER_ID criado (external_id: ${FAIL_EXTERNAL_ID:0:40}...)"
echo -e "${YELLOW}Simulando webhook com session.expired (pagamento recusado)...${RESET}"

FAIL_RESULT=$(cd "$API_DIR" && php artisan tinker --execute="
    \$payload = [
        'type' => 'checkout.session.expired',
        'data' => [
            'object' => [
                'id' => '$FAIL_EXTERNAL_ID',
                'object' => 'checkout.session',
                'status' => 'expired',
                'payment_status' => 'unpaid',
                'amount_total' => $(echo "$PRODUCT_PRICE * 100" | bc | cut -d. -f1),
                'currency' => 'brl',
                'created' => time(),
                'payment_intent' => null,
                'metadata' => ['order_id' => $FAIL_ORDER_ID],
            ],
        ],
    ];

    dispatch_sync(new App\Jobs\ProcessPaymentWebhook('stripe', \$payload));

    \$order = App\Models\Order::find($FAIL_ORDER_ID);
    echo json_encode([
        'order_id' => \$order->id,
        'status' => \$order->status,
    ]);
" 2>&1 | grep -v Warning | grep -v "^$")

echo "$FAIL_RESULT" | python3 -m json.tool 2>/dev/null || echo "$FAIL_RESULT"

FAIL_STATUS=$(echo "$FAIL_RESULT" | python3 -c "import sys,json; data=json.load(sys.stdin); print(data['status'])" 2>/dev/null)

echo ""
if [ "$FAIL_STATUS" = "failed" ]; then
    echo -e "${GREEN}${BOLD}SUCESSO! Pedido #$FAIL_ORDER_ID marcado como 'failed'!${RESET}"
elif [ "$FAIL_STATUS" = "pending" ]; then
    echo -e "${YELLOW}${BOLD}Status ainda 'pending'.${RESET}"
else
    echo -e "${RED}${BOLD}Status: $FAIL_STATUS${RESET}"
fi

# ──────────────────────────────────────────────────────────────
# Logs recentes
# ──────────────────────────────────────────────────────────────
divider
echo -e "${BOLD}Logs relevantes desta execução:${RESET}"
echo ""
grep -E "\[(CreatePaymentCharge|Webhook|ProcessPaymentWebhook)\]" "$API_DIR/storage/logs/laravel.log" | tail -20
divider
