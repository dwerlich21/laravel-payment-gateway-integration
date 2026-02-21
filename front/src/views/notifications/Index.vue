<template>
    <Layout>
        <PageHeader title="Notificações" />

        <b-row>
            <b-col cols="12">
                <b-card no-body class="mb-3">
                    <b-card-header class="border-0">
                        <b-row class="align-items-center g-3">
                            <b-col sm="12" md="6">
                                <h5 class="card-title mb-0">
                                    <i class="ri-notification-3-line me-2"></i>
                                    Todas as Notificações
                                </h5>
                            </b-col>
                            <b-col sm="12" md="6" class="text-md-end">
                                <div class="d-flex gap-2 justify-content-md-end">
                                    <b-form-select
                                        v-model="filters.type"
                                        :options="typeOptions"
                                        size="sm"
                                        style="width: auto;"
                                        @update:model-value="onFilterChange"
                                    />
                                    <b-form-select
                                        v-model="filters.unread_only"
                                        :options="readOptions"
                                        size="sm"
                                        style="width: auto;"
                                        @update:model-value="onFilterChange"
                                    />
                                    <b-button
                                        variant="soft-secondary"
                                        size="sm"
                                        @click="markAllAsRead"
                                        :disabled="!hasUnread"
                                    >
                                        <i class="ri-check-double-line me-1"></i>
                                        Marcar todas como lidas
                                    </b-button>
                                </div>
                            </b-col>
                        </b-row>
                    </b-card-header>

                    <b-card-body class="p-0">
                        <!-- Loading -->
                        <div v-if="loading" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-else-if="notifications.length === 0" class="text-center py-5">
                            <div class="avatar-lg mx-auto mb-4">
                                <div class="avatar-title bg-light rounded-circle fs-24">
                                    <i class="ri-notification-off-line text-muted"></i>
                                </div>
                            </div>
                            <h5 class="mb-2">Nenhuma notificação encontrada</h5>
                            <p class="text-muted mb-0">
                                Quando você receber notificações, elas aparecerão aqui.
                            </p>
                        </div>

                        <!-- Notification list -->
                        <div v-else class="list-group list-group-flush">
                            <div
                                v-for="notification in notifications"
                                :key="notification.id"
                                class="list-group-item list-group-item-action p-3"
                                :class="{ 'bg-light-subtle': !notification.read }"
                            >
                                <div class="d-flex align-items-start gap-3">
                                    <!-- Icon -->
                                    <div class="flex-shrink-0">
                                        <div
                                            class="avatar-sm"
                                        >
                                            <span
                                                class="avatar-title rounded-circle fs-18"
                                                :class="`bg-${getTypeInfo(notification.type).color}-subtle text-${getTypeInfo(notification.type).color}`"
                                            >
                                                <i :class="getTypeInfo(notification.type).icon"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div>
                                                <h6 class="mb-0 fw-semibold">
                                                    {{ notification.title }}
                                                    <span
                                                        v-if="!notification.read"
                                                        class="badge bg-primary ms-2"
                                                    >
                                                        Nova
                                                    </span>
                                                </h6>
                                                <span
                                                    class="badge fs-10"
                                                    :class="`bg-${getTypeInfo(notification.type).color}-subtle text-${getTypeInfo(notification.type).color}`"
                                                >
                                                    {{ getTypeInfo(notification.type).label }}
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                <i class="ri-time-line me-1"></i>
                                                {{ formatDate(notification.created_at) }}
                                            </small>
                                        </div>

                                        <p class="text-muted mb-2">
                                            {{ notification.message }}
                                        </p>

                                        <!-- Additional data -->
                                        <div
                                            v-if="notification.data && Object.keys(notification.data).length"
                                            class="d-flex flex-wrap gap-2 mb-2"
                                        >
                                            <span
                                                v-if="notification.data.deadline"
                                                class="badge bg-light text-body"
                                            >
                                                <i class="ri-calendar-line me-1"></i>
                                                Prazo: {{ notification.data.deadline }}
                                            </span>
                                            <span
                                                v-if="notification.data.weight"
                                                class="badge bg-light text-body"
                                            >
                                                <i class="ri-scales-line me-1"></i>
                                                Peso: {{ notification.data.weight }}%
                                            </span>
                                            <span
                                                v-if="notification.data.days_remaining"
                                                class="badge bg-warning-subtle text-warning"
                                            >
                                                <i class="ri-alarm-warning-line me-1"></i>
                                                {{ notification.data.days_remaining }} dia(s) restante(s)
                                            </span>
                                        </div>

                                        <!-- Actions -->
                                        <div class="d-flex gap-2">
                                            <b-button
                                                v-if="!notification.read"
                                                variant="soft-primary"
                                                size="sm"
                                                @click="markAsRead(notification)"
                                            >
                                                <i class="ri-check-line me-1"></i>
                                                Marcar como lida
                                            </b-button>
                                            <b-button
                                                variant="soft-danger"
                                                size="sm"
                                                @click="deleteNotification(notification)"
                                            >
                                                <i class="ri-delete-bin-line me-1"></i>
                                                Excluir
                                            </b-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </b-card-body>

                    <!-- Pagination -->
                    <b-card-footer
                        v-if="pagination.last_page > 1"
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div class="text-muted">
                            Mostrando {{ notifications.length }} de {{ pagination.total }} notificações
                        </div>
                        <b-pagination
                            :model-value="pagination.current_page"
                            :total-rows="pagination.total"
                            :per-page="pagination.per_page"
                            class="mb-0 gap-2"
                            @update:model-value="onPageChange"
                        />
                    </b-card-footer>
                </b-card>
            </b-col>
        </b-row>
    </Layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useNotificationStore } from '@/stores/notification';
import { notifySuccess } from '@/composables/messages';
import NotificationService from '@/services/NotificationService';
import Layout from '@/components/layouts/main.vue';
import PageHeader from '@/components/layouts/page-header.vue';

// Service
const notificationService = new NotificationService();

// Store (apenas para atualizar unreadCount global)
const notificationStore = useNotificationStore();

// State
const loading = ref(false);
const notifications = ref([]);
const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
});
const filters = reactive({
    type: '',
    unread_only: '',
});

const typeOptions = [
    { value: '', text: 'Todos os tipos' },
    { value: 'goal_assigned', text: 'Nova Meta' },
    { value: 'goal_deadline', text: 'Prazo Próximo' },
    { value: 'goal_completed', text: 'Meta Concluida' },
];

const readOptions = [
    { value: '', text: 'Todas' },
    { value: '1', text: 'Não lidas' },
];

// Computed
const hasUnread = computed(() => notifications.value.some(n => !n.read));

// Methods
const fetchNotifications = async (page = 1) => {
    loading.value = true;
    try {
        const params = {
            limit: 10,
            page,
        };

        if (filters.type) params.type = filters.type;
        if (filters.unread_only) params.unread_only = filters.unread_only;

        const result = await notificationService.getNotifications(params);

        notifications.value = result.data || [];
        pagination.current_page = result.current_page || 1;
        pagination.last_page = result.last_page || 1;
        pagination.per_page = result.per_page || 10;
        pagination.total = result.total || 0;
    } catch (error) {
        console.error('Error fetching notifications:', error);
    } finally {
        loading.value = false;
    }
};

const onFilterChange = () => {
    fetchNotifications(1);
};

const onPageChange = (page) => {
    fetchNotifications(page);
};

const markAsRead = async (notification) => {
    const success = await notificationService.markAsRead(notification.id);
    if (success) {
        notification.read = true;
        notificationStore.fetchUnreadCount();
        notifySuccess('Notificação marcada como lida');
    }
};

const markAllAsRead = async () => {
    const success = await notificationService.markAllAsRead();
    if (success) {
        notifications.value.forEach(n => n.read = true);
        notificationStore.fetchUnreadCount();
        notifySuccess('Todas as notificações foram marcadas como lidas');
    }
};

const deleteNotification = async (notification) => {
    const success = await notificationService.deleteNotification(notification.id);
    if (success) {
        if (!notification.read) {
            notificationStore.fetchUnreadCount();
        }
        notifications.value = notifications.value.filter(n => n.id !== notification.id);
        notifySuccess('Notificação excluída com sucesso');
    }
};

const getTypeInfo = (type) => {
    return notificationService.getTypeInfo(type);
};

const formatDate = (date) => {
    return notificationService.formatDate(date);
};

// Lifecycle
onMounted(() => {
    fetchNotifications();
});
</script>

<style scoped>
.list-group-item {
    transition: background-color 0.2s;
}

.list-group-item:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.03);
}

.avatar-sm {
    width: 2.5rem;
    height: 2.5rem;
}

.avatar-lg {
    width: 5rem;
    height: 5rem;
}

.avatar-title {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
</style>
