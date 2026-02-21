<template>
    <b-card no-body class="card-equal-height card-height-100">
        <b-card-header class="align-items-center d-flex border-0">
            <h6 class="card-title mb-0 flex-grow-1">
                <i class="ri-trophy-line align-middle me-1 text-warning"></i>
                Ranking
            </h6>
        </b-card-header>
        <b-card-body class="pt-0">
            <div v-if="loading" class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
            <div v-else-if="performers.length === 0" class="d-flex flex-column justify-content-center align-items-center text-muted" style="min-height: 200px;">
                <i class="ri-trophy-line fs-1 mb-2"></i>
                <span>Sem dados disponiveis</span>
            </div>
            <div v-else class="table-responsive">
                <table class="table table-borderless table-sm align-middle mb-0">
                    <tbody>
                        <tr v-for="(performer, index) in performers.slice(0, 8)" :key="performer.id">
                            <td style="width: 50px;">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i :class="[getMedalIcon(index), getMedalClass(index), 'fs-4']"></i>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <div class="avatar-xs bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                            <span class="text-primary fw-medium fs-12">
                                                {{ performer.name?.charAt(0)?.toUpperCase() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">{{ performer.name }}</h6>
                                        <small class="text-muted">{{ performer.position || 'Colaborador' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <h6 class="mb-0 text-success">{{ performer.completion_rate }}%</h6>
                                <small class="text-muted">
                                    {{ performer.completed_goals }}/{{ performer.total_goals }}
                                </small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="performers.length > 0" class="mt-3 text-center">
                <router-link to="/relatorios/usuarios" class="text-muted text-decoration-underline fs-13">
                    Ver relatorio completo <i class="ri-arrow-right-s-line align-middle"></i>
                </router-link>
            </div>
        </b-card-body>
    </b-card>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import http from '@/http';

const loading = ref(true);
const performers = ref([]);

const loadData = async () => {
    loading.value = true;
    try {
        const response = await http.get('reports/top-performers');
        performers.value = response.data.data || [];
    } catch (error) {
        console.error('Error loading top performers:', error);
    }
    loading.value = false;
};

const getMedalIcon = (index) => {
    if (index === 0) return 'ri-medal-line';
    if (index === 1) return 'ri-medal-2-line';
    if (index === 2) return 'ri-award-line';
    return 'ri-user-line';
};

const getMedalClass = (index) => {
    if (index === 0) return 'text-warning';
    if (index === 1) return 'text-secondary';
    if (index === 2) return 'text-danger';
    return 'text-muted';
};

onMounted(() => {
    loadData();
});
</script>
