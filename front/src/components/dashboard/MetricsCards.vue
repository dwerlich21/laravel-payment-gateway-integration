<template>
    <!-- Loading State -->
    <div
        v-if="loading"
        class="text-center py-5"
    >
        <div
            class="spinner-border text-primary"
            role="status"
        >
            <span class="visually-hidden">Carregando...</span>
        </div>
    </div>

    <template v-else>
        <!-- Metrics Cards Row 1 -->

        <!-- Total de Metas -->
        <b-col
            class="col-equal-height"
            sm="6"
            xl="4"
        >
            <b-card
                no-body
                class="card-animate card-equal-height overflow-hidden"
            >
                <b-card-header class="border-0 d-flex align-items-center">
                    <h6 class="card-title text-uppercase fw-semibold text-muted mb-0 flex-grow-1">Total de Metas</h6>
                    <span class="text-primary fs-12">
                        <i class="ri-arrow-right-up-line align-middle"></i>
                        {{ metrics.goals_per_user || 0 }}/usuário
                    </span>
                </b-card-header>
                <b-card-body class="pt-0">
                    <div class="d-flex align-items-end justify-content-between">
                        <div>
                            <h4 class="fs-22 fw-semibold">
                                <span>{{ metrics.total_goals || 0 }}</span>
                            </h4>
                            <router-link
                                to="/metas"
                                class="text-decoration-underline text-muted fs-13"
                            >
                                Ver todas as metas
                            </router-link>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                <i class="ri-focus-3-line text-primary"></i>
                            </span>
                        </div>
                    </div>
                </b-card-body>
            </b-card>
        </b-col>

        <!-- Metas Concluidas -->
        <b-col
            class="col-equal-height"
            sm="6"
            xl="4"
        >
            <b-card
                no-body
                class="card-animate card-equal-height overflow-hidden"
            >
                <b-card-header class="border-0 d-flex align-items-center">
                    <h6 class="card-title text-uppercase fw-semibold text-muted mb-0 flex-grow-1">Concluidas</h6>
                    <span class="text-success fs-12">
                        <i class="ri-arrow-right-up-line align-middle"></i>
                        {{ metrics.completion_rate || 0 }}%
                    </span>
                </b-card-header>
                <b-card-body class="pt-0">
                    <div class="d-flex align-items-end justify-content-between">
                        <div>
                            <h4 class="fs-22 fw-semibold">
                                <span>{{ metrics.completed_goals || 0 }}</span>
                            </h4>
                            <span class="badge bg-success-subtle text-success">
                                <i class="ri-checkbox-circle-line align-middle me-1"></i>
                                Finalizadas
                            </span>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle rounded fs-3">
                                <i class="ri-checkbox-circle-line text-success"></i>
                            </span>
                        </div>
                    </div>
                </b-card-body>
            </b-card>
        </b-col>

        <!-- Em Andamento -->
        <b-col
            class="col-equal-height"
            sm="6"
            xl="4"
        >
            <b-card
                no-body
                class="card-animate card-equal-height overflow-hidden"
            >
                <b-card-header class="border-0 d-flex align-items-center">
                    <h6 class="card-title text-uppercase fw-semibold text-muted mb-0 flex-grow-1">Em Andamento</h6>
                    <span class="text-info fs-12">
                        <i class="ri-time-line align-middle"></i>
                        {{ metrics.pending_goals || 0 }} pend.
                    </span>
                </b-card-header>
                <b-card-body class="pt-0">
                    <div class="d-flex align-items-end justify-content-between">
                        <div>
                            <h4 class="fs-22 fw-semibold">
                                <span>{{ metrics.in_progress_goals || 0 }}</span>
                            </h4>
                            <span class="badge bg-info-subtle text-info">
                                <i class="ri-loader-4-line align-middle me-1"></i>
                                Ativas
                            </span>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle rounded fs-3">
                                <i class="ri-loader-4-line text-info"></i>
                            </span>
                        </div>
                    </div>
                </b-card-body>
            </b-card>
        </b-col>

        <!-- Metas Atrasadas -->
        <b-col
            class="col-equal-height"
            sm="6"
            xl="4"
        >
            <b-card
                no-body
                class="card-animate card-equal-height overflow-hidden"
            >
                <b-card-header class="border-0 d-flex align-items-center">
                    <h6 class="card-title text-uppercase fw-semibold text-muted mb-0 flex-grow-1">Atrasadas</h6>
                    <span class="text-danger fs-12">
                        <i class="ri-arrow-right-down-line align-middle"></i>
                        Urgente
                    </span>
                </b-card-header>
                <b-card-body class="pt-0">
                    <div class="d-flex align-items-end justify-content-between">
                        <div>
                            <h4 class="fs-22 fw-semibold">
                                <span>{{ metrics.overdue_goals || 0 }}</span>
                            </h4>
                            <span class="badge bg-danger-subtle text-danger">
                                <i class="ri-alarm-warning-line align-middle me-1"></i>
                                Requer atenção
                            </span>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-danger-subtle rounded fs-3">
                                <i class="ri-alarm-warning-line text-danger"></i>
                            </span>
                        </div>
                    </div>
                </b-card-body>
            </b-card>
        </b-col>

        <!-- Desempenho Médio -->
        <b-col
            class="col-equal-height"
            sm="6"
            xl="4"
        >
            <b-card
                no-body
                class="card-animate card-equal-height overflow-hidden"
            >
                <b-card-header class="border-0 d-flex align-items-center">
                    <h6 class="card-title text-uppercase fw-semibold text-muted mb-0 flex-grow-1">Desempenho</h6>
                    <span class="text-warning fs-12">
                        <i class="ri-bar-chart-box-line align-middle"></i>
                        Média
                    </span>
                </b-card-header>
                <b-card-body class="pt-0">
                    <div class="d-flex align-items-end justify-content-between">
                        <div>
                            <h4 class="fs-22 fw-semibold">
                                <span>{{ metrics.avg_achievement || 0 }}</span>
                                %
                            </h4>
                            <div class="progress progress-sm">
                                <div
                                    class="progress-bar bg-warning"
                                    role="progressbar"
                                    :style="{ width: `${metrics.avg_achievement || 0}%` }"
                                ></div>
                            </div>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle rounded fs-3">
                                <i class="ri-percent-line text-warning"></i>
                            </span>
                        </div>
                    </div>
                </b-card-body>
            </b-card>
        </b-col>

        <!-- Canceladas -->
        <b-col
            class="col-equal-height"
            sm="6"
            xl="4"
        >
            <b-card
                no-body
                class="card-animate card-equal-height overflow-hidden"
            >
                <b-card-header class="border-0 d-flex align-items-center">
                    <h6 class="card-title text-uppercase fw-semibold text-muted mb-0 flex-grow-1">Canceladas</h6>
                    <span class="text-muted fs-12">
                        <i class="ri-close-circle-line align-middle"></i>
                        Total
                    </span>
                </b-card-header>
                <b-card-body class="pt-0">
                    <div class="d-flex align-items-end justify-content-between">
                        <div>
                            <h4 class="fs-22 fw-semibold">
                                <span>{{ metrics.cancelled_goals || 0 }}</span>
                            </h4>
                            <span class="badge bg-light text-muted">
                                <i class="ri-close-circle-line align-middle me-1"></i>
                                Metas canceladas
                            </span>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-light rounded fs-3">
                                <i class="ri-close-circle-line text-muted"></i>
                            </span>
                        </div>
                    </div>
                </b-card-body>
            </b-card>
        </b-col>
    </template>
</template>

<script setup>
import {ref, onMounted} from 'vue';
import http from '@/http';

const loading = ref(true);
const metrics = ref({});

const loadMetrics = async () => {
    loading.value = true;
    try {
        const response = await http.get('reports/dashboard');
        metrics.value = response.data.data || {};
    } catch (error) {
        console.error('Error loading metrics:', error);
    }
    loading.value = false;
};

onMounted(() => {
    loadMetrics();
});
</script>