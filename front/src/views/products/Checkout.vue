<template>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Checkout</h4>
            </div>
        </div>
    </div>

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
            <router-link to="/produtos" class="btn btn-soft-primary mt-2">Voltar aos produtos</router-link>
        </div>
    </div>

    <div v-else class="row">
        <!-- Formulario de checkout -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-credit-card-outline me-1"/>
                        Selecione o Gateway de Pagamento
                    </h5>
                </div>
                <div class="card-body">
                    <form @submit.prevent="submitOrder">
                        <!-- Gateway selection -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="form-check card-radio">
                                    <input
                                        id="gateway-stripe"
                                        v-model="form.gateway"
                                        type="radio"
                                        name="gateway"
                                        value="stripe"
                                        class="form-check-input"
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

                        <!-- Quantidade -->
                        <div class="mb-4">
                            <label for="quantity" class="form-label">Quantidade</label>
                            <input
                                id="quantity"
                                v-model.number="form.quantity"
                                type="number"
                                class="form-control"
                                min="1"
                                max="99"
                                style="max-width: 120px;"
                                required
                            />
                        </div>

                        <!-- Botoes -->
                        <div class="d-flex align-items-center gap-3">
                            <router-link to="/produtos" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"/>
                                Voltar
                            </router-link>
                            <button
                                type="submit"
                                class="btn btn-success"
                                :disabled="submitting"
                            >
                                <i class="mdi mdi-check me-1"/>
                                Confirmar Pedido
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resultado do pedido -->
            <div v-if="orderResult" class="card border-success">
                <div class="card-body text-center py-4">
                    <i class="mdi mdi-check-circle text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Pedido #{{ orderResult.data?.id }} criado!</h5>
                    <p class="text-muted mb-2">
                        Status: <span class="badge bg-warning-subtle text-warning">{{ orderResult.data?.status }}</span>
                        &middot; Gateway: <span class="badge bg-info-subtle text-info">{{ orderResult.data?.gateway }}</span>
                    </p>
                    <p class="text-muted">
                        O pagamento esta sendo processado em background pelo job <code>CreatePaymentCharge</code>.
                    </p>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <router-link to="/produtos" class="btn btn-soft-primary">
                            Comprar outro produto
                        </router-link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumo do pedido -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Resumo do Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
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
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        R$ {{ formatPrice(product.price * form.quantity) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-top pt-3 mt-3">
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
                        <div class="d-flex align-items-center text-muted fs-12">
                            <i class="mdi mdi-shield-check-outline me-1"/>
                            Pagamento processado de forma segura
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {ref, onMounted} from 'vue';
import {useRoute} from 'vue-router';
import ProductService from '@/services/ProductService';
import OrderService from '@/services/OrderService';

const route = useRoute();
const productService = new ProductService();
const orderService = new OrderService();

const product = ref(null);
const loading = ref(true);
const submitting = ref(false);
const orderResult = ref(null);

const form = ref({
    gateway: 'stripe',
    quantity: 1,
});

function formatPrice(price) {
    return parseFloat(price).toFixed(2).replace('.', ',');
}

async function submitOrder() {
    submitting.value = true;
    orderResult.value = null;
    try {
        const result = await orderService.checkout({
            product_id: product.value.id,
            quantity: form.value.quantity,
            gateway: form.value.gateway,
        });
        orderResult.value = result;
    } catch (error) {
        console.error('Erro no checkout:', error);
    } finally {
        submitting.value = false;
    }
}

onMounted(async () => {
    try {
        const products = await productService.getAll();
        const productId = parseInt(route.params.id);
        product.value = products.find(p => p.id === productId) || null;
    } finally {
        loading.value = false;
    }
});
</script>
