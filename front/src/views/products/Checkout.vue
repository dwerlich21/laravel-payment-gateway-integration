<script setup>
import {ref, computed, onMounted, watch} from 'vue';
import {useRoute} from 'vue-router';
import {loadStripe} from '@stripe/stripe-js';
import Layout from '@/components/layouts/main.vue';
import PageHeader from '@/components/layouts/page-header.vue';
import ProductService from '@/services/ProductService';
import OrderService from '@/services/OrderService';

const route = useRoute();
const productService = new ProductService();
const orderService = new OrderService();

const product = ref(null);
const loading = ref(true);
const submitting = ref(false);
const orderResult = ref(null);
const paymentError = ref(null);
const pendingOrder = ref(null);

// Stripe
let stripe = null;
let cardElement = null;
const stripeReady = ref(false);

const form = ref({
    gateway: 'stripe',
    payment_method: 'credit_card',
    quantity: 1,
    customer_name: '',
    customer_email: '',
    customer_cpf_cnpj: '',
    customer_phone: '',
});

const title = 'Checkout';
const breadcrumbs = [
    {text: 'Produtos', href: '/'},
    {text: 'Checkout', active: true},
];

const availablePaymentMethods = computed(() => {
    if (form.value.gateway === 'stripe') {
        return [{value: 'credit_card', label: 'Cartao de Credito', icon: 'mdi-credit-card-outline'}];
    }
    return [
        {value: 'credit_card', label: 'Cartao de Credito', icon: 'mdi-credit-card-outline'},
        {value: 'boleto', label: 'Boleto Bancario', icon: 'mdi-barcode'},
        {value: 'pix', label: 'PIX', icon: 'mdi-qrcode'},
    ];
});

const showCardFields = computed(() => {
    return form.value.payment_method === 'credit_card';
});

const isStripeCard = computed(() => {
    return form.value.gateway === 'stripe' && form.value.payment_method === 'credit_card';
});

function onGatewayChange() {
    if (form.value.gateway === 'stripe') {
        form.value.payment_method = 'credit_card';
    } else {
        form.value.payment_method = 'pix';
    }
}

function formatPrice(price) {
    return parseFloat(price).toFixed(2).replace('.', ',');
}

function applyCpfCnpjMask(event) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        value = value.substring(0, 14);
        value = value.replace(/(\d{2})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1/$2');
        value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
    }
    form.value.customer_cpf_cnpj = value;
}

function applyPhoneMask(event) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length <= 10) {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{4})(\d{1,4})$/, '$1-$2');
    } else {
        value = value.substring(0, 11);
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{5})(\d{1,4})$/, '$1-$2');
    }
    form.value.customer_phone = value;
}

function mountStripeCard() {
    if (!stripe || !isStripeCard.value) return;

    const elements = stripe.elements();
    cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#495057',
                fontFamily: '"Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                '::placeholder': { color: '#aab7c4' },
            },
            invalid: { color: '#dc3545' },
        },
        hidePostalCode: true,
    });

    const container = document.getElementById('card-element');
    if (container) {
        cardElement.mount('#card-element');
        stripeReady.value = true;
    }
}

function unmountStripeCard() {
    if (cardElement) {
        cardElement.unmount();
        cardElement = null;
        stripeReady.value = false;
    }
}

watch(isStripeCard, (newVal) => {
    if (newVal) {
        setTimeout(mountStripeCard, 100);
    } else {
        unmountStripeCard();
    }
});

async function submitOrder() {
    submitting.value = true;
    orderResult.value = null;
    paymentError.value = null;

    try {
        let order;
        let clientSecret;

        if (pendingOrder.value) {
            order = pendingOrder.value.order;
            clientSecret = pendingOrder.value.clientSecret;
        } else {
            const data = {
                product_id: product.value.id,
                quantity: form.value.quantity,
                gateway: form.value.gateway,
                payment_method: form.value.payment_method,
                customer_name: form.value.customer_name,
                customer_email: form.value.customer_email,
                customer_cpf_cnpj: form.value.customer_cpf_cnpj.replace(/\D/g, ''),
                customer_phone: form.value.customer_phone.replace(/\D/g, ''),
            };

            const result = await orderService.checkout(data);
            order = result.data;
            clientSecret = order?.client_secret;
        }

        if (isStripeCard.value && clientSecret && stripe && cardElement) {
            const {error, paymentIntent} = await stripe.confirmCardPayment(clientSecret, {
                payment_method: {
                    card: cardElement,
                    billing_details: {
                        name: form.value.customer_name,
                        email: form.value.customer_email,
                    },
                },
            });

            if (error) {
                paymentError.value = error.message;
                pendingOrder.value = {order, clientSecret};
                orderResult.value = {
                    ...order,
                    payment_status: 'failed',
                };
            } else if (paymentIntent.status === 'succeeded') {
                pendingOrder.value = null;
                await orderService.confirmPayment(order.id, {
                    customer_name: form.value.customer_name,
                    customer_email: form.value.customer_email,
                    customer_cpf_cnpj: form.value.customer_cpf_cnpj.replace(/\D/g, ''),
                    customer_phone: form.value.customer_phone.replace(/\D/g, ''),
                });
                orderResult.value = {
                    ...order,
                    payment_status: 'paid',
                };
            } else {
                pendingOrder.value = null;
                orderResult.value = {
                    ...order,
                    payment_status: paymentIntent.status,
                };
            }
        } else {
            pendingOrder.value = null;
            orderResult.value = order;
        }
    } catch (error) {
        console.error('Erro no checkout:', error);
    } finally {
        submitting.value = false;
    }
}

onMounted(async () => {
    try {
        const [products, checkoutConfig] = await Promise.all([
            productService.getAll(),
            orderService.getCheckoutConfig(),
        ]);

        const productId = parseInt(route.params.id);
        product.value = products.find(p => p.id === productId) || null;

        if (checkoutConfig.stripe_publishable_key) {
            stripe = await loadStripe(checkoutConfig.stripe_publishable_key);
            setTimeout(mountStripeCard, 100);
        }
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <Layout>
        <PageHeader :title="title" :items="breadcrumbs" />

        <div v-if="loading" class="row">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
        </div>

        <div v-else-if="!product" class="row">
            <div class="col-12 text-center py-5">
                <i class="mdi mdi-alert-circle-outline text-warning" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">Produto nao encontrado</h5>
                <router-link to="/" class="btn btn-soft-primary mt-2">Voltar aos produtos</router-link>
            </div>
        </div>

        <div v-else class="row">
            <!-- Formulario de checkout -->
            <div class="col-xl-8">
                <form @submit.prevent="submitOrder">
                    <!-- Gateway selection -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="mdi mdi-credit-card-outline me-1"/>
                                Gateway de Pagamento
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check card-radio">
                                        <input
                                            id="gateway-stripe"
                                            v-model="form.gateway"
                                            type="radio"
                                            name="gateway"
                                            value="stripe"
                                            class="form-check-input"
                                            @change="onGatewayChange"
                                        />
                                        <label class="form-check-label" for="gateway-stripe">
                                            <span class="d-flex align-items-center mb-2">
                                                <i class="mdi mdi-credit-card-multiple fs-20 text-primary me-2"/>
                                                <span class="fs-14 fw-semibold">Stripe</span>
                                            </span>
                                            <span class="text-muted fw-normal d-block">
                                                Cartao de credito internacional. Pagamento instantaneo.
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check card-radio">
                                        <input
                                            id="gateway-asaas"
                                            v-model="form.gateway"
                                            type="radio"
                                            name="gateway"
                                            value="asaas"
                                            class="form-check-input"
                                            @change="onGatewayChange"
                                        />
                                        <label class="form-check-label" for="gateway-asaas">
                                            <span class="d-flex align-items-center mb-2">
                                                <i class="mdi mdi-barcode fs-20 text-success me-2"/>
                                                <span class="fs-14 fw-semibold">Asaas</span>
                                            </span>
                                            <span class="text-muted fw-normal d-block">
                                                Boleto, PIX ou cartao. Gateway nacional.
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment method selection -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="mdi mdi-wallet-outline me-1"/>
                                Meio de Pagamento
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div
                                    v-for="method in availablePaymentMethods"
                                    :key="method.value"
                                    class="col-md-4"
                                >
                                    <div class="form-check card-radio">
                                        <input
                                            :id="'method-' + method.value"
                                            v-model="form.payment_method"
                                            type="radio"
                                            name="payment_method"
                                            :value="method.value"
                                            class="form-check-input"
                                        />
                                        <label class="form-check-label text-center" :for="'method-' + method.value">
                                            <i :class="'mdi ' + method.icon + ' fs-24 d-block mb-1'"/>
                                            <span class="fs-13 fw-medium">{{ method.label }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer data -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="mdi mdi-account-outline me-1"/>
                                Dados do Comprador
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="customer_name" class="form-label">Nome completo</label>
                                    <input
                                        id="customer_name"
                                        v-model="form.customer_name"
                                        type="text"
                                        class="form-control"
                                        placeholder="Nome completo"
                                        required
                                    />
                                </div>
                                <div class="col-md-6">
                                    <label for="customer_email" class="form-label">E-mail</label>
                                    <input
                                        id="customer_email"
                                        v-model="form.customer_email"
                                        type="email"
                                        class="form-control"
                                        placeholder="email@exemplo.com"
                                        required
                                    />
                                </div>
                                <div class="col-md-6">
                                    <label for="customer_cpf_cnpj" class="form-label">CPF/CNPJ</label>
                                    <input
                                        id="customer_cpf_cnpj"
                                        :value="form.customer_cpf_cnpj"
                                        type="text"
                                        class="form-control"
                                        placeholder="000.000.000-00"
                                        maxlength="18"
                                        required
                                        @input="applyCpfCnpjMask"
                                    />
                                </div>
                                <div class="col-md-6">
                                    <label for="customer_phone" class="form-label">Telefone</label>
                                    <input
                                        id="customer_phone"
                                        :value="form.customer_phone"
                                        type="text"
                                        class="form-control"
                                        placeholder="(00) 00000-0000"
                                        maxlength="15"
                                        required
                                        @input="applyPhoneMask"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stripe Card Element -->
                    <div v-if="isStripeCard" class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="mdi mdi-credit-card-outline me-1"/>
                                Dados do Cartao
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="card-element" class="form-control" style="padding: 12px; height: auto;"></div>
                        </div>
                    </div>

                    <!-- Non-Stripe card fields (Asaas credit card) -->
                    <div v-else-if="showCardFields" class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="mdi mdi-credit-card-outline me-1"/>
                                Dados do Cartao
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="card_number" class="form-label">Numero do cartao</label>
                                    <input
                                        id="card_number"
                                        type="text"
                                        class="form-control"
                                        placeholder="0000 0000 0000 0000"
                                        maxlength="19"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <label for="card_holder" class="form-label">Nome no cartao</label>
                                    <input
                                        id="card_holder"
                                        type="text"
                                        class="form-control"
                                        placeholder="Como esta no cartao"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <label for="card_expiry" class="form-label">Validade</label>
                                    <input
                                        id="card_expiry"
                                        type="text"
                                        class="form-control"
                                        placeholder="MM/AA"
                                        maxlength="5"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <label for="card_cvv" class="form-label">CVV</label>
                                    <input
                                        id="card_cvv"
                                        type="text"
                                        class="form-control"
                                        placeholder="000"
                                        maxlength="4"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boleto info -->
                    <div v-if="form.payment_method === 'boleto'" class="card border-info mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-barcode fs-24 text-info me-3"/>
                                <div>
                                    <h6 class="mb-1">Pagamento por Boleto Bancario</h6>
                                    <p class="text-muted mb-0 fs-13">
                                        Apos confirmar o pedido, o boleto sera gerado e enviado para o e-mail informado.
                                        O prazo de compensacao e de ate 3 dias uteis.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PIX info -->
                    <div v-if="form.payment_method === 'pix'" class="card border-success mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-qrcode fs-24 text-success me-3"/>
                                <div>
                                    <h6 class="mb-1">Pagamento por PIX</h6>
                                    <p class="text-muted mb-0 fs-13">
                                        Apos confirmar o pedido, um QR Code PIX sera gerado para pagamento instantaneo.
                                        A confirmacao e automatica.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quantidade -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 w-100 justify-content-between">
                                <div>
                                    <label for="quantity" class="form-label mb-0">Quantidade</label>
                                    <input
                                        id="quantity"
                                        v-model.number="form.quantity"
                                        type="number"
                                        class="form-control"
                                        min="1"
                                        max="99"
                                        style="width: 100px;"
                                        required
                                    />
                                </div>
                                <div class="d-flex align-items-center gap-3 mt-auto justify-content-end">
                                    <router-link to="/" class="btn btn-light">
                                        <i class="mdi mdi-arrow-left me-1"/>
                                        Voltar
                                    </router-link>
                                    <button
                                        type="submit"
                                        class="btn btn-success"
                                        :disabled="submitting"
                                    >
                                        <span v-if="submitting" class="spinner-border spinner-border-sm me-1"/>
                                        <i v-else class="mdi mdi-check me-1"/>
                                        Confirmar Pedido
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botoes -->
                </form>

                <!-- Resultado do pedido — Pagamento aprovado -->
                <div v-if="orderResult && orderResult.payment_status === 'paid'" class="card border-success">
                    <div class="card-body text-center py-4">
                        <i class="mdi mdi-check-circle text-success" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Pedido #{{ orderResult.id }} — Pagamento Aprovado!</h5>
                        <p class="text-muted mb-2">
                            Status:
                            <span class="badge bg-success-subtle text-success">Pago</span>
                            &middot; Gateway:
                            <span class="badge bg-info-subtle text-info">{{ orderResult.gateway }}</span>
                            &middot; Meio:
                            <span class="badge bg-primary-subtle text-primary">{{ orderResult.payment_method }}</span>
                        </p>
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <router-link to="/" class="btn btn-soft-primary">
                                Comprar outro produto
                            </router-link>
                        </div>
                    </div>
                </div>

                <!-- Resultado do pedido — Pagamento falhou -->
                <div v-else-if="orderResult && orderResult.payment_status === 'failed'" class="card border-danger">
                    <div class="card-body text-center py-4">
                        <i class="mdi mdi-close-circle text-danger" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Pedido #{{ orderResult.id }} — Pagamento Recusado</h5>
                        <p class="text-danger mb-2" v-if="paymentError">
                            {{ paymentError }}
                        </p>
                        <p class="text-muted mb-2">
                            Status:
                            <span class="badge bg-danger-subtle text-danger">Falhou</span>
                            &middot; Gateway:
                            <span class="badge bg-info-subtle text-info">{{ orderResult.gateway }}</span>
                        </p>
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <router-link to="/" class="btn btn-soft-primary">
                                Tentar novamente
                            </router-link>
                        </div>
                    </div>
                </div>

                <!-- Resultado do pedido — Outros gateways / status pendente -->
                <div v-else-if="orderResult" class="card border-warning">
                    <div class="card-body text-center py-4">
                        <i class="mdi mdi-clock-outline text-warning" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Pedido #{{ orderResult.id }} criado!</h5>
                        <p class="text-muted mb-2">
                            Status:
                            <span class="badge bg-warning-subtle text-warning">{{ orderResult.status }}</span>
                            &middot; Gateway:
                            <span class="badge bg-info-subtle text-info">{{ orderResult.gateway }}</span>
                            &middot; Meio:
                            <span class="badge bg-primary-subtle text-primary">{{ orderResult.payment_method }}</span>
                        </p>
                        <p class="text-muted">
                            O pagamento esta sendo processado.
                        </p>
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <router-link to="/" class="btn btn-soft-primary">
                                Comprar outro produto
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumo do pedido -->
            <div class="col-xl-4">
                <div class="card sticky-top mb-3" style="top: 140px;">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Resumo do Pedido</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                    <i class="mdi mdi-package-variant"/>
                                </span>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-1">{{ product.name }}</h6>
                                <p class="text-muted mb-0 fs-12">
                                    R$ {{ formatPrice(product.price) }} x {{ form.quantity }}
                                </p>
                            </div>
                            <div class="ms-auto text-end">
                                <span class="fw-semibold">R$ {{ formatPrice(product.price * form.quantity) }}</span>
                            </div>
                        </div>

                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span>R$ {{ formatPrice(product.price * form.quantity) }}</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold fs-16">
                                <span>Total</span>
                                <span class="text-success">R$ {{ formatPrice(product.price * form.quantity) }}</span>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top">
                            <div class="d-flex align-items-center text-muted fs-12 mb-2">
                                <i class="mdi mdi-shield-check-outline me-1"/>
                                Pagamento processado de forma segura
                            </div>
                            <div class="d-flex align-items-center text-muted fs-12">
                                <i class="mdi mdi-lock-outline me-1"/>
                                Dados protegidos com criptografia
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
