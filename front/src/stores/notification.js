import { defineStore } from 'pinia';
import NotificationService from '@/services/NotificationService';

const notificationService = new NotificationService();

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        notifications: [],
        unreadCount: 0,
        loading: false,
        pollingInterval: null,
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
        },
    }),

    getters: {
        hasUnread: (state) => state.unreadCount > 0,

        recentNotifications: (state) => state.notifications.slice(0, 5),

        formattedUnreadCount: (state) => {
            if (state.unreadCount > 99) return '99+';
            return state.unreadCount.toString();
        },
    },

    actions: {
        /**
         * Fetch unread count from API
         */
        async fetchUnreadCount() {
            this.unreadCount = await notificationService.getUnreadCount();
        },

        /**
         * Fetch notifications list
         */
        async fetchNotifications(params = {}) {
            this.loading = true;
            try {
                const result = await notificationService.getNotifications(params);
                this.notifications = result.data || [];
                this.pagination = {
                    current_page: result.current_page || 1,
                    last_page: result.last_page || 1,
                    per_page: result.per_page || 10,
                    total: result.total || 0,
                };
            } catch (error) {
                console.error('Error fetching notifications:', error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Mark a single notification as read
         */
        async markAsRead(id) {
            const success = await notificationService.markAsRead(id);
            if (success) {
                const notification = this.notifications.find(n => n.id === id);
                if (notification && !notification.read) {
                    notification.read = true;
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }
            }
            return success;
        },

        /**
         * Mark all notifications as read
         */
        async markAllAsRead() {
            const success = await notificationService.markAllAsRead();
            if (success) {
                this.notifications.forEach(n => n.read = true);
                this.unreadCount = 0;
            }
            return success;
        },

        /**
         * Delete a notification
         */
        async deleteNotification(id) {
            const success = await notificationService.deleteNotification(id);
            if (success) {
                const notification = this.notifications.find(n => n.id === id);
                if (notification && !notification.read) {
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }
                this.notifications = this.notifications.filter(n => n.id !== id);
            }
            return success;
        },

        /**
         * Start polling for new notifications
         */
        startPolling(intervalMs = 30000) {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
            }

            // Initial fetch
            this.fetchUnreadCount();

            // Set up polling
            this.pollingInterval = setInterval(() => {
                this.fetchUnreadCount();
            }, intervalMs);
        },

        /**
         * Stop polling
         */
        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        },

        /**
         * Get type info helper
         */
        getTypeInfo(type) {
            return notificationService.getTypeInfo(type);
        },

        /**
         * Format date helper
         */
        formatDate(date) {
            return notificationService.formatDate(date);
        },
    },
});
