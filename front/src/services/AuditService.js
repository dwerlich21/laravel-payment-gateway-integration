import BaseService from './BaseService';
import http from '@/http';

const AUDIT_FILTER = [
    {
        name: 'search',
        placeholder: 'Buscar por descrição...',
        col: '3',
        type: 'text'
    },
    {
        name: 'action',
        placeholder: 'Todas as ações',
        type: 'select',
        col: '2',
        options: [
            { value: 'created', label: 'Criado' },
            { value: 'updated', label: 'Atualizado' },
            { value: 'deleted', label: 'Excluido' },
            { value: 'login', label: 'Login' },
            { value: 'logout', label: 'Logout' },
            { value: 'login_failed', label: 'Login Falhou' },
            { value: 'exported_pdf', label: 'PDF Exportado' },
            { value: 'exported_excel', label: 'Excel Exportado' },
        ]
    },
    {
        name: 'model_type',
        placeholder: 'Todos os modelos',
        type: 'select',
        col: '2',
        options: [
            { value: 'User', label: 'Usuário' },
            { value: 'Goal', label: 'Meta' },
        ]
    },
];

const AUDIT_TABLE = [
    {
        order: 'created_at',
        column: 'Data/Hora',
    },
    {
        column: 'Usuário',
    },
    {
        order: 'action',
        column: 'Ação',
    },
    {
        column: 'Modelo',
    },
    {
        column: 'Descrição',
    },
    {
        column: 'IP',
    },
    {
        column: 'Detalhes',
    }
];

const AUDIT_KEYS = [
    'created_at',
    'user_name',
    'action',
    'model_type',
    'description',
    'ip_address',
    'details',
];

const AUDIT_ACTIONS = [];

export default class AuditService extends BaseService {
    constructor() {
        super('audit-logs');
    }

    /**
     * Retorna configuração de filtros
     */
    getFilterConfig() {
        return JSON.parse(JSON.stringify(AUDIT_FILTER));
    }

    /**
     * Retorna configuração da tabela
     */
    getTableConfig() {
        return AUDIT_TABLE;
    }

    /**
     * Retorna as chaves para a tabela
     */
    getTableKeys() {
        return AUDIT_KEYS;
    }

    /**
     * Retorna os tipos de ações disponiveis
     */
    getActionTypes() {
        return AUDIT_ACTIONS;
    }

    /**
     * Get available actions for filter
     */
    async getActions() {
        try {
            const response = await http.get(`${this.endpoint}/actions`);
            return response.data.data;
        } catch (error) {
            console.error('Error fetching actions:', error);
            return [];
        }
    }

    /**
     * Get available model types for filter
     */
    async getModelTypes() {
        try {
            const response = await http.get(`${this.endpoint}/model-types`);
            return response.data.data;
        } catch (error) {
            console.error('Error fetching model types:', error);
            return [];
        }
    }

    /**
     * Get audit statistics
     */
    async getStats() {
        try {
            const response = await http.get(`${this.endpoint}/stats`);
            return response.data.data;
        } catch (error) {
            console.error('Error fetching stats:', error);
            return null;
        }
    }

    /**
     * Format date for display
     */
    formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }
}
