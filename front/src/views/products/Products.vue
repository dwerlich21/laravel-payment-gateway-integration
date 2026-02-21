<script setup>
import {ref, onMounted} from 'vue';
import {useRouter} from 'vue-router';
import Layout from '@/components/layouts/main.vue';
import PageHeader from '@/components/layouts/page-header.vue';
import ProductService from '@/services/ProductService';

const router = useRouter();
const productService = new ProductService();
const products = ref([]);
const loading = ref(true);

const title = 'Produtos';

function formatPrice(price) {
    return parseFloat(price).toFixed(2).replace('.', ',');
}

function goToCheckout(product) {
    router.push({name: 'checkout', params: {id: product.id}});
}

onMounted(async () => {
    try {
        products.value = await productService.getAll();
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <Layout>
        <PageHeader :title="title" />

        <div v-if="loading" class="row">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
        </div>

        <div v-else-if="products.length === 0" class="row">
            <div class="col-12 text-center py-5">
                <i class="mdi mdi-package-variant-closed text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">Nenhum produto disponivel</h5>
            </div>
        </div>

        <div v-else class="row">
            <div
                v-for="product in products"
                :key="product.id"
                class="col-xl-4 col-md-6"
            >
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-20">
                                    <i class="mdi mdi-package-variant"/>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="card-title mb-1">{{ product.name }}</h5>
                            </div>
                        </div>

                        <p class="text-muted mb-3" style="min-height: 48px;">
                            {{ product.description || 'Sem descricao' }}
                        </p>

                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="text-success mb-0">
                                R$ {{ formatPrice(product.price) }}
                            </h4>
                            <button
                                class="btn btn-primary"
                                @click="goToCheckout(product)"
                            >
                                <i class="mdi mdi-cart-outline me-1"/>
                                Comprar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
