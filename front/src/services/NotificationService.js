import BaseService from './BaseService';
import http from '@/http';

const NOTIFICATION_TYPE_LABELS = {
    goal_assigned: { label: 'Nova Meta', color: 'primary', icon: 'ri-task-line' },
    goal_deadline: { label: 'Prazo Próximo', color: 'warning', icon: 'ri-alarm-warning-line' },
    goal_completed: { label: 'Meta Concluída', color: 'success', icon: 'ri-checkbox-circle-line' },
};

const NOTIFICATION_FILTER = [
    {
        name: 'type',
        placeholder: 'Todos os tipos',
        type: 'select',
        col: '3',
        options: [
            { value: '', label: 'Todos os tipos' },
            { value: 'goal_assigned', label: 'Nova Meta' },
            { value: 'goal_deadline', label: 'Prazo Próximo' },
            { value: 'goal_completed', label: 'Meta Concluída' },
        ]
    },
    {
        name: 'unread_only',
        placeholder: 'Todas',
        type: 'select',
        col: '2',
        options: [
            { value: '', label: 'Todas' },
            { value: '1', label: 'Não lidas' },
        ]
    },
];

export default class NotificationService extends BaseService {
    constructor() {
        super('notifications');
    }

    /**
     * Get unread notifications count
     * @returns {Promise<number>}
     */
    async getUnreadCount() {
        try {
            const response = await http.get(`${this.endpoint}/unread-count`);
            return response.data.data.count || 0;
        } catch (error) {
            console.error('Error fetching unread count:', error);
            return 0;
        }
    }

    /**
     * Mark a notification as read
     * @param {number} id - Notification ID
     * @returns {Promise<boolean>}
     */
    async markAsRead(id) {
        try {
            await http.put(`${this.endpoint}/${id}/read`);
            return true;
        } catch (error) {
            console.error('Error marking notification as read:', error);
            return false;
        }
    }

    /**
     * Mark all notifications as read
     * @returns {Promise<boolean>}
     */
    async markAllAsRead() {
        try {
            await http.put(`${this.endpoint}/mark-all-read`);
            return true;
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            return false;
        }
    }

    /**
     * Get notifications with filters
     * @param {Object} params - Query parameters
     * @returns {Promise<Object>}
     */
    async getNotifications(params = {}) {
        try {
            const response = await http.get(this.endpoint, { params });
            return response.data.data || { data: [], total: 0 };
        } catch (error) {
            console.error('Error fetching notifications:', error);
            return { data: [], total: 0 };
        }
    }

    /**
     * Delete a notification
     * @param {number} id - Notification ID
     * @returns {Promise<boolean>}
     */
    async deleteNotification(id) {
        try {
            await http.delete(`${this.endpoint}/${id}`);
            return true;
        } catch (error) {
            console.error('Error deleting notification:', error);
            return false;
        }
    }

    /**
     * Get type info (label, color, icon)
     * @param {string} type - Notification type
     * @returns {Object}
     */
    getTypeInfo(type) {
        return NOTIFICATION_TYPE_LABELS[type] || {
            label: 'Notificação',
            color: 'secondary',
            icon: 'ri-notification-3-line'
        };
    }

    /**
     * Get filter configuration
     * @returns {Array}
     */
    getFilterConfig() {
        return JSON.parse(JSON.stringify(NOTIFICATION_FILTER));
    }

    /**
     * Format date for display
     * @param {string} date - ISO date string
     * @returns {string}
     */
    formatDate(date) {
        if (!date) return '';
        const d = new Date(date);
        const now = new Date();
        const diff = now - d;

        // Less than 1 minute
        if (diff < 60000) {
            return 'Agora mesmo';
        }

        // Less than 1 hour
        if (diff < 3600000) {
            const minutes = Math.floor(diff / 60000);
            return `${minutes} min atrás`;
        }

        // Less than 24 hours
        if (diff < 86400000) {
            const hours = Math.floor(diff / 3600000);
            return `${hours}h atrás`;
        }

        // Less than 7 days
        if (diff < 604800000) {
            const days = Math.floor(diff / 86400000);
            return `${days}d atrás`;
        }

        // More than 7 days - show full date
        return d.toLocaleDateString('pt-BR');
    }
}
