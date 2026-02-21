import BaseService from './BaseService';
import http from '@/http';
import { notifyError } from "@/composables/messages";

const USER_FORM = {
    id: 0,
    basicInfo: {
        name: '',
        email: '',
        cpf: '',
        type: '',          // Nível de acesso
        position: '',      // Cargo
        phone: '',
        password: '',      // Senha
        img: null,         // Imagem blob
        imgUrl: '',        // URL da imagem
    },
    address: {
        zipCode: '',       // CEP
        uf: '',            // Estado
        city: '',          // Cidade
        neighborhood: '',  // Bairro
        address: '',       // Logradouro
        number: '',        // Número
        complement: '',    // Complemento
    },
    permissions: [],       // Permissões (array de IDs)
    active: true,          // Status ativo
};

const USER_FILTER = [
    {
        name: 'name',
        placeholder: 'Nome',
        col: '3',
        type: 'text'
    },
    {
        name: 'email',
        placeholder: 'E-mail',
        col: '3',
        type: 'text'
    },
    {
        name: 'active',
        placeholder: 'Todos os status',
        type: 'select',
        col: '3',
        options: [
            {value: '1', label: 'Ativo'},
            {value: '0', label: 'Inativo'},
        ]
    },
];

const USER_TABLE = [
    {
        column: 'check',
    },
    {
        order: 'name',
        column: 'Nome',
    },
    {
        column: 'E-mail',
    },
    {
        column: 'Telefone',
    },
    {
        column: 'Status',
    },
    {
        column: 'Ações',
    }
];

const USER_KEYS = [
    'check',
    'name',
    'email',
    'phone',
    'change_active',
    'actions',
];

const USER_ACTIONS = ['page', 'delete'];

export default class UserService extends BaseService {
    constructor() {
        super('users');
    }

    /**
     * Retorna o objeto de formulário padrão para usuários
     * @returns {Object} - Objeto com valores padrão
     */
    getDefaultFormData() {
        return JSON.parse(JSON.stringify(USER_FORM));
    }

    /**
     * Retorna configuração de filtros para usuários
     * @returns {Array} - Array de configurações de filtros
     */
    getFilterConfig() {
        return JSON.parse(JSON.stringify(USER_FILTER));
    }

    /**
     * Retorna configuração da tabela para usuários
     * @returns {Array} - Array de configurações de colunas
     */
    getTableConfig() {
        return USER_TABLE;
    }

    /**
     * Retorna as chaves para a tabela de usuários
     * @returns {Array} - Array de chaves
     */
    getTableKeys() {
        return USER_KEYS;
    }

    /**
     * Retorna os tipos de ações disponíveis para usuários
     * @returns {Array} - Array de tipos de ações
     */
    getActionTypes() {
        return USER_ACTIONS;
    }

    /**
     * Busca cargos disponíveis
     * @returns {Promise} - Promise com a lista de cargos
     */
    async getPositions() {
        try {
            const response = await http.get('positions');
            return response.data.message;
        } catch (error) {
            console.error('Erro ao carregar cargos:', error);
            notifyError('Erro ao carregar cargos');
            throw error;
        }
    }

    /**
     * Busca permissões disponíveis
     * @returns {Promise} - Promise com a lista de permissões
     */
    async getPermissions() {
        try {
            const response = await http.get('permissions');
            return response.data.data;
        } catch (error) {
            console.error('Erro ao carregar permissões:', error);
            notifyError('Erro ao carregar permissões');
            throw error;
        }
    }

    /**
     * Gera um apelido baseado no nome do usuário
     * @param {String} name - Nome completo
     * @returns {String} - Iniciais do nome
     */
    generateNickname(name) {
        if (!name) return '';

        return name
            .split(' ')
            .map(part => part.charAt(0))
            .join('')
            .toUpperCase()
            .substring(0, 2);
    }

    /**
     * Função para gerar cor de fundo para o avatar baseado no nome
     */
    generateAvatarColor(name) {
        const lightModeColors = [
            'bg-primary text-white',
            'bg-success text-white',
            'bg-warning text-dark',
            'bg-danger text-white',
            'bg-info text-white',
            'bg-secondary text-white',
        ];

        const darkModeColors = [
            'bg-primary text-white',
            'bg-success text-white',
            'bg-warning text-dark',
            'bg-danger text-white',
            'bg-info text-white',
            'bg-secondary text-white',
        ];

        // Soma dos valores dos caracteres para gerar um índice consistente
        const sum = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
        const colorIndex = sum % lightModeColors.length;

        // Verifica o tema atual do sistema
        const isDarkMode = document.documentElement.getAttribute('data-layout-mode') === 'dark';
        return isDarkMode ? darkModeColors[colorIndex] : lightModeColors[colorIndex];
    }
}