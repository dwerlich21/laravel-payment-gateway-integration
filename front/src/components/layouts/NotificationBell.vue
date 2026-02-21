<script setup>
import { onMounted, onUnmounted, computed } from 'vue';
import { useNotificationStore } from '@/stores/notification';
import { useRouter } from 'vue-router';

const notificationStore = useNotificationStore();
const router = useRouter();

// Computed
const unreadCount = computed(() => notificationStore.unreadCount);
const hasUnread = computed(() => notificationStore.hasUnread);
const formattedCount = computed(() => notificationStore.formattedUnreadCount);
const recentNotifications = computed(() => notificationStore.recentNotifications);
const loading = computed(() => notificationStore.loading);

// Methods
const fetchNotifications = async () => {
    await notificationStore.fetchNotifications({ limit: 5 });
};

const markAsRead = async (notification) => {
    if (!notification.read) {
        await notificationStore.markAsRead(notification.id);
    }
};

const markAllAsRead = async () => {
    await notificationStore.markAllAsRead();
};

const viewAll = () => {
    router.push('/notificações');
};

const getTypeInfo = (type) => {
    return notificationStore.getTypeInfo(type);
};

const formatDate = (date) => {
    return notificationStore.formatDate(date);
};

// Lifecycle
onMounted(() => {
    notificationStore.startPolling(30000);
    fetchNotifications();
});

onUnmounted(() => {
    notificationStore.stopPolling();
});
</script>

<template>
    <div class="dropdown topbar-head-dropdown ms-1 header-item">
        <button
            type="button"
            class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
            id="notification-dropdown"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
            @click="fetchNotifications"
        >
            <i class="bx bx-bell fs-22"></i>
            <span
                v-if="hasUnread"
                class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger"
            >
                {{ formattedCount }}
                <span class="visually-hidden">unread notifications</span>
            </span>
        </button>

        <div
            class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
            aria-labelledby="notification-dropdown"
        >
            <div class="dropdown-head bg-primary bg-pattern rounded-top">
                <div class="p-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-0 fs-16 fw-semibold text-white">Notificações</h6>
                        </div>
                        <div class="col-auto dropdown-tabs">
                            <span class="badge bg-light-subtle text-body fs-13">
                                {{ unreadCount }} novas
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="notification-list" style="max-height: 300px; overflow-y: auto;">
                <!-- Loading -->
                <div v-if="loading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else-if="recentNotifications.length === 0" class="text-center py-4">
                    <div class="avatar-sm mx-auto mb-3">
                        <span class="avatar-title bg-light rounded-circle fs-24">
                            <i class="ri-notification-off-line text-muted"></i>
                        </span>
                    </div>
                    <p class="text-muted mb-0">Nenhuma notificação</p>
                </div>

                <!-- Notification items -->
                <template v-else>
                    <div
                        v-for="notification in recentNotifications"
                        :key="notification.id"
                        class="text-reset notification-item d-block dropdown-item position-relative"
                        :class="{ 'bg-light-subtle': !notification.read }"
                        @click="markAsRead(notification)"
                    >
                        <div class="d-flex">
                            <div class="avatar-xs me-3 flex-shrink-0">
                                <span
                                    class="avatar-title rounded-circle fs-16"
                                    :class="`bg-${getTypeInfo(notification.type).color}-subtle text-${getTypeInfo(notification.type).color}`"
                                >
                                    <i :class="getTypeInfo(notification.type).icon"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                        {{ notification.title }}
                                    </h6>
                                    <span
                                        v-if="!notification.read"
                                        class="badge bg-primary rounded-pill"
                                        style="width: 8px; height: 8px; padding: 0;"
                                    ></span>
                                </div>
                                <p class="mb-1 fs-12 text-muted">
                                    {{ notification.message }}
                                </p>
                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                    <span>
                                        <i class="mdi mdi-clock-outline"></i>
                                        {{ formatDate(notification.created_at) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="notification-actions p-2 border-top" v-if="recentNotifications.length > 0">
                <div class="d-flex justify-content-between">
                    <button
                        type="button"
                        class="btn btn-sm btn-soft-secondary"
                        @click="markAllAsRead"
                        :disabled="!hasUnread"
                    >
                        <i class="ri-check-double-line align-middle me-1"></i>
                        Marcar todas como lidas
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-soft-primary"
                        @click="viewAll"
                    >
                        Ver todas
                        <i class="ri-arrow-right-s-line align-middle ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.notification-item {
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05) !important;
}

.dropdown-menu-lg {
    min-width: 320px;
}

.topbar-badge {
    top: 5px;
    right: 5px;
}

.notification-list::-webkit-scrollbar {
    width: 5px;
}

.notification-list::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: 3px;
}
</style>
