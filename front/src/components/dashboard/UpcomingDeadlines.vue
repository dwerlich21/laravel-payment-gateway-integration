<template>
    <b-card no-body class="card-equal-height card-height-100">
        <b-card-header class="align-items-center d-flex border-0">
            <h6 class="card-title mb-0 flex-grow-1">
                <i class="ri-calendar-event-line align-middle me-1 text-danger"></i>
                Próximos Prazos
            </h6>
            <div class="flex-shrink-0">
                <span v-if="urgentCount > 0" class="badge bg-danger-subtle text-danger">
                    {{ urgentCount }} urgente{{ urgentCount > 1 ? 's' : '' }}
                </span>
            </div>
        </b-card-header>
        <b-card-body class="pt-0">
            <div v-if="loading" class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
            <div v-else-if="deadlines.length === 0" class="d-flex flex-column justify-content-center align-items-center text-muted" style="min-height: 200px;">
                <i class="ri-calendar-check-line fs-1 mb-2"></i>
                <span>Nenhum prazo proximo</span>
            </div>
            <div v-else class="table-responsive">
                <table class="table table-borderless table-sm align-middle mb-0">
                    <tbody>
                        <tr v-for="deadline in deadlines.slice(0, 5)" :key="deadline.id">
                            <td style="width: 50px;">
                                <div
                                    class="avatar-xs d-flex align-items-center justify-content-center rounded-circle"
                                    :class="`bg-${getDeadlineClass(deadline.days_left)}-subtle`"
                                >
                                    <span
                                        class="fw-semibold fs-12"
                                        :class="`text-${getDeadlineClass(deadline.days_left)}`"
                                    >
                                        {{ deadline.days_left }}d
                                    </span>
                                </div>
                            </td>
                            <td>
                                <h6 class="mb-0 text-truncate" style="max-width: 180px;">
                                    {{ deadline.milestone_description }}
                                </h6>
                                <small class="text-muted">
                                    {{ deadline.responsible?.name || 'Sem responsavel' }}
                                </small>
                            </td>
                            <td class="text-end">
                                <span class="text-muted fs-12">
                                    {{ formatDate(deadline.deadline) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="deadlines.length > 0" class="mt-3 text-center">
                <router-link to="/metas" class="text-muted text-decoration-underline fs-13">
                    Ver todas as metas <i class="ri-arrow-right-s-line align-middle"></i>
                </router-link>
            </div>
        </b-card-body>
    </b-card>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import http from '@/http';

const loading = ref(true);
const deadlines = ref([]);

const loadData = async () => {
    loading.value = true;
    try {
        const response = await http.get('reports/upcoming-deadlines');
        deadlines.value = response.data.data || [];
    } catch (error) {
        console.error('Error loading deadlines:', error);
    }
    loading.value = false;
};

const getDeadlineClass = (daysLeft) => {
    if (daysLeft <= 1) return 'danger';
    if (daysLeft <= 3) return 'warning';
    return 'info';
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('pt-BR');
};

const urgentCount = computed(() => deadlines.value.filter(d => d.days_left <= 3).length);

onMounted(() => {
    loadData();
});
</script>
