<template>
    <b-card
        no-body
        class="card-equal-height card-height-100"
    >
        <b-card-header class="align-items-center d-flex border-0">
            <h6 class="card-title mb-0 flex-grow-1">
                <i class="ri-history-line align-middle me-1 text-info"></i>
                Atividade Recente
            </h6>
            <div class="flex-shrink-0">
                <span class="badge bg-info-subtle text-info">
                    {{ activities.length }} registros
                </span>
            </div>
        </b-card-header>
        <b-card-body class="pt-0">
            <div
                v-if="loading"
                class="d-flex justify-content-center align-items-center"
                style="min-height: 200px;"
            >
                <div
                    class="spinner-border spinner-border-sm text-primary"
                    role="status"
                >
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
            <div
                v-else-if="activities.length === 0"
                class="d-flex flex-column justify-content-center align-items-center text-muted"
                style="min-height: 200px;"
            >
                <i class="ri-history-line fs-1 mb-2"></i>
                <span>Nenhuma atividade recente</span>
            </div>
            <div
                v-else
                class="acitivity-timeline acitivity-main"
            >
                <div
                    v-for="activity in activities.slice(0, 5)"
                    :key="activity.id"
                    class="acitivity-item d-flex"
                >
                    <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                        <div
                            class="avatar-title rounded-circle"
                            :class="getActivityBadgeClass(activity.action_color)"
                        >
                            <i :class="getActivityIcon(activity.action_color)"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 lh-base">{{ activity.action_label }}</h6>
                        <p
                            class="text-muted mb-1 fs-13 text-truncate"
                            style="max-width: 200px;"
                            :title="activity.description"
                        >
                            {{ activity.description || 'Sem descricao' }}
                        </p>
                        <div class="d-flex justify-content-between w-100">
                            <span class="text-muted mb-0">
                                {{ activity.user_name || 'Sistema' }}
                            </span>
                            <small class="text-muted mb-0 mt-auto">
                                {{ activity.time_ago }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div
                v-if="activities.length > 0"
                class="mt-3 text-center"
            >
                <router-link
                    to="/audit-logs"
                    class="text-muted text-decoration-underline fs-13"
                >
                    Ver todas as atividades
                    <i class="ri-arrow-right-s-line align-middle"></i>
                </router-link>
            </div>
        </b-card-body>
    </b-card>
</template>

<script setup>
import {ref, onMounted} from 'vue';
import http from '@/http';

const loading = ref(true);
const activities = ref([]);

const loadData = async () => {
    loading.value = true;
    try {
        const response = await http.get('reports/recent-activity');
        activities.value = response.data.data || [];
    } catch (error) {
        console.error('Error loading recent activity:', error);
    }
    loading.value = false;
};

const getActivityBadgeClass = (color) => {
    const colorMap = {
        success: 'bg-success-subtle',
        info: 'bg-info-subtle',
        danger: 'bg-danger-subtle',
        warning: 'bg-warning-subtle',
        primary: 'bg-primary-subtle',
        secondary: 'bg-secondary-subtle',
    };
    return colorMap[color] || 'bg-secondary-subtle';
};

const getActivityIcon = (color) => {
    const iconMap = {
        success: 'ri-checkbox-circle-line',
        info: 'ri-information-line',
        danger: 'ri-error-warning-line',
        warning: 'ri-alert-line',
        primary: 'ri-add-circle-line',
        secondary: 'ri-edit-line',
    };
    return iconMap[color] || 'ri-checkbox-blank-circle-line';
};

onMounted(() => {
    loadData();
});
</script>
