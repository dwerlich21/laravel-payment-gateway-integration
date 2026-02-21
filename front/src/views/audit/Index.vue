<template>
    <Crud
        :title="title"
        :title-pluralize="titlePluralize"
        :filter="filterConfig"
        :keys="tableKeys"
        :table="tableConfig"
        :actions="actionTypes"
        :url="url"
        :endpoint="endpoint"
        :session="session"
        :service="auditService"
        :hide-add-button="true"
    >
        <!-- Stats Cards -->
        <template #before-card>
            <b-row
                v-if="canStats"
                class="g-3 mb-4 row-equal-height"
            >
                <b-col
                    class="col-equal-height"
                    sm="6"
                    lg="3"
                >
                    <b-card>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-4">
                                    <i class="ri-file-list-3-line"></i>
                                </span>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Total de Logs</p>
                                <h4 class="mb-0">{{ stats?.total || 0 }}</h4>
                            </div>
                        </div>
                    </b-card>
                </b-col>
                <b-col
                    class="col-equal-height"
                    sm="6"
                    lg="3"
                >
                    <b-card>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-4">
                                    <i class="ri-calendar-check-line"></i>
                                </span>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Hoje</p>
                                <h4 class="mb-0">{{ stats?.today || 0 }}</h4>
                            </div>
                        </div>
                    </b-card>
                </b-col>
                <b-col
                    class="col-equal-height"
                    sm="6"
                    lg="3"
                >
                    <b-card>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <span class="avatar-title bg-info-subtle text-info rounded-circle fs-4">
                                    <i class="ri-calendar-line"></i>
                                </span>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Esta Semana</p>
                                <h4 class="mb-0">{{ stats?.this_week || 0 }}</h4>
                            </div>
                        </div>
                    </b-card>
                </b-col>
                <b-col
                    class="col-equal-height"
                    sm="6"
                    lg="3"
                >
                    <b-card>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-4">
                                    <i class="ri-calendar-2-line"></i>
                                </span>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Este Mes</p>
                                <h4 class="mb-0">{{ stats?.this_month || 0 }}</h4>
                            </div>
                        </div>
                    </b-card>
                </b-col>
            </b-row>
        </template>

        <!-- Template para Data/Hora -->
        <template #created_at="{ value }">
            <span class="text-nowrap">{{ auditService.formatDate(value.created_at) }}</span>
        </template>

        <!-- Template para Usuário -->
        <template #user_name="{ value }">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="avatar-xs me-2">
                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                        {{ value.user_name ? value.user_name.charAt(0).toUpperCase() : '?' }}
                    </span>
                </div>
                {{ value.user_name || 'Sistema' }}
            </div>
        </template>

        <!-- Template para Ação -->
        <template #action="{ value }">
            <span
                class="badge"
                :class="`bg-${value.action_color}-subtle`"
            >
                {{ value.action_label }}
            </span>
        </template>

        <!-- Template para Modelo -->
        <template #model_type="{ value }">
            <span
                v-if="value.short_model_type"
                class="badge bg-secondary-subtle"
            >
                {{ value.model_type_label }}
            </span>
            <span v-else>-</span>
        </template>

        <!-- Template para Descrição -->
        <template #description="{ value }">
            <div
                class="text-truncate mx-auto"
                style="max-width: 250px;"
                :title="value.description"
            >
                {{ value.description || '-' }}
            </div>
        </template>

        <!-- Template para IP -->
        <template #ip_address="{ value }">
            <small class="text-muted">{{ value.ip_address || '-' }}</small>
        </template>

        <!-- Template para Detalhes -->
        <template #details="{ value }">
            <b-button
                v-if="(value.old_values || value.new_values) && canDetails"
                variant="soft-primary"
                size="sm"
                @click="openDetailModal(value)"
            >
                <i class="ri-eye-line"></i>
            </b-button>
            <span
                v-else
                class="text-muted"
            >-
            </span>
        </template>
    </Crud>

    <!-- Detail Modal -->
    <b-modal
        v-model="showDetailModal"
        title="Detalhes do Log"
        size="lg"
        hide-footer
    >
        <div v-if="selectedLog">
            <div class="mb-4">
                <b-row class="g-3 row-equal-height">
                    <b-col
                        class="col-equal-height"
                        md="6"
                    >
                        <p class="mb-1 text-muted">Data/Hora</p>
                        <p class="mb-0 fw-medium">{{ auditService.formatDate(selectedLog.created_at) }}</p>
                    </b-col>
                    <b-col
                        class="col-equal-height"
                        md="6"
                    >
                        <p class="mb-1 text-muted">Usuário</p>
                        <p class="mb-0 fw-medium">{{ selectedLog.user_name || 'Sistema' }}</p>
                    </b-col>
                    <b-col
                        class="col-equal-height"
                        md="6"
                    >
                        <p class="mb-1 text-muted">Ação</p>
                        <span
                            class="badge"
                            :class="`bg-${selectedLog.action_color}-subtle`"
                        >
                            {{ selectedLog.action_label }}
                        </span>
                    </b-col>
                    <b-col
                        class="col-equal-height"
                        md="6"
                    >
                        <p class="mb-1 text-muted">Modelo</p>
                        <p class="mb-0 fw-medium">
                            {{ selectedLog.short_model_type || '-' }}
                            <span
                                v-if="selectedLog.model_id"
                                class="text-muted"
                            >(ID: {{ selectedLog.model_id }})
                            </span>
                        </p>
                    </b-col>
                    <b-col
                        class="col-equal-height"
                        cols="12"
                    >
                        <p class="mb-1 text-muted">Descrição</p>
                        <p class="mb-0">{{ selectedLog.description || '-' }}</p>
                    </b-col>
                    <b-col
                        class="col-equal-height"
                        md="6"
                    >
                        <p class="mb-1 text-muted">IP</p>
                        <p class="mb-0">{{ selectedLog.ip_address || '-' }}</p>
                    </b-col>
                    <b-col
                        class="col-equal-height"
                        md="6"
                    >
                        <p class="mb-1 text-muted">User Agent</p>
                        <p
                            class="mb-0 text-truncate"
                            :title="selectedLog.user_agent"
                        >
                            {{ selectedLog.user_agent || '-' }}
                        </p>
                    </b-col>
                </b-row>
            </div>

            <hr/>

            <DiffViewer
                :old-values="selectedLog.old_values"
                :new-values="selectedLog.new_values"
                :changed-fields="selectedLog.changed_fields"
            />
        </div>
    </b-modal>
</template>

<script setup>
import {ref, onMounted} from 'vue';
import Crud from "@/components/base/Crud.vue";
import DiffViewer from '@/components/audit/DiffViewer.vue';
import AuditService from '@/services/AuditService';
import {usePermissions} from "@/utils/permissions.js";

// Inicialização
const auditService = new AuditService();
const session = 'AuditLogs';
const title = 'Log de Auditoria';
const titlePluralize = 'Logs de Auditoria';
const endpoint = 'audit-logs';
const url = 'audit-logs';

// Configurações obtidas do servico
const filterConfig = ref(auditService.getFilterConfig());
const tableConfig = auditService.getTableConfig();
const tableKeys = auditService.getTableKeys();
const actionTypes = auditService.getActionTypes();

// Stats
const stats = ref(null);

// Modal de detalhes
const showDetailModal = ref(false);
const selectedLog = ref(null);

// Carrega estatísticas
const loadStats = async () => {
    if (canStats.value) stats.value = await auditService.getStats();
};

// Abre modal de detalhes
const openDetailModal = (log) => {
    selectedLog.value = log;
    showDetailModal.value = true;
};

// Permissões
const canStats = ref(false);
const canDetails = ref(false);

onMounted(async () => {
    canStats.value = await usePermissions.hasPermission(`audit-logs.stats`);
    canDetails.value = await usePermissions.hasPermission(`audit-logs.show`);
    await loadStats();
});
</script>

<style scoped>
.avatar-sm {
    width: 2.5rem;
    height: 2.5rem;
}

.avatar-xs {
    width: 1.75rem;
    height: 1.75rem;
}

.avatar-title {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.bg-purple-subtle {
    background-color: rgba(111, 66, 193, 0.1) !important;
}

.text-purple {
    color: #6f42c1 !important;
}
</style>
