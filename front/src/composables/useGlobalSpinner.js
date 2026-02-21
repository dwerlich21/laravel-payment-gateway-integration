// composables/useGlobalSpinner.js
// Composable para controlar o spinner global

import { ref, reactive } from 'vue';

// Estado global do spinner
const spinnerState = reactive({
    show: false, // controla se o spinner está visível
    message: 'Carregando...', // mensagem principal
    subMessage: '', // submensagem opcional
    overlay: true, // se deve mostrar overlay
    zIndex: 9999 // z-index do spinner
});

// Contador de spinners ativos (para múltiplas operações simultâneas)
const activeSpinners = ref(0);

export const useGlobalSpinner = () => {

    /**
     * Mostra o spinner global
     * @param {Object} options - Opções do spinner
     * @param {string} options.message - Mensagem principal
     * @param {string} options.subMessage - Submensagem opcional
     * @param {boolean} options.overlay - Se deve mostrar overlay
     * @param {number} options.zIndex - Z-index customizado
     */
    const showSpinner = (options = {}) => {
        activeSpinners.value++;

        // Aplica opções ou usa valores padrão
        spinnerState.message = options.message || 'Carregando...';
        spinnerState.subMessage = options.subMessage || '';
        spinnerState.overlay = options.overlay !== undefined ? options.overlay : true;
        spinnerState.zIndex = options.zIndex || 9999;
        spinnerState.show = true;
    };

    /**
     * Esconde o spinner global
     */
    const hideSpinner = () => {
        activeSpinners.value = Math.max(0, activeSpinners.value - 1);

        // Só esconde se não há mais spinners ativos
        if (activeSpinners.value === 0) {
            spinnerState.show = false;
        }
    };

    /**
     * Força o fechamento do spinner (para casos de erro)
     */
    const forceHideSpinner = () => {
        activeSpinners.value = 0;
        spinnerState.show = false;
    };

    /**
     * Atualiza apenas a mensagem do spinner
     * @param {string} message - Nova mensagem
     * @param {string} subMessage - Nova submensagem
     */
    const updateSpinnerMessage = (message, subMessage = '') => {
        if (spinnerState.show) {
            spinnerState.message = message;
            spinnerState.subMessage = subMessage;
        }
    };

    /**
     * Wrapper para operações async com spinner automático
     * @param {Function} asyncOperation - Função async para executar
     * @param {Object} options - Opções do spinner
     * @returns {Promise} - Resultado da operação
     */
    const withSpinner = async (asyncOperation, options = {}) => {
        showSpinner(options);

        try {
            const result = await asyncOperation();
            hideSpinner();
            return result;
        } catch (error) {
            hideSpinner();
            throw error;
        }
    };

    /**
     * Spinner específico para formulários
     * @param {Object} options - Opções específicas para formulários
     */
    const showFormSpinner = (options = {}) => {
        showSpinner({
            message: options.message || 'Salvando dados...',
            subMessage: options.subMessage || 'Por favor, aguarde',
            ...options
        });
    };

    /**
     * Spinner específico para carregamento de dados
     * @param {Object} options - Opções específicas para dados
     */
    const showDataSpinner = (options = {}) => {
        showSpinner({
            message: options.message || 'Carregando dados...',
            subMessage: options.subMessage || '',
            ...options
        });
    };

    /**
     * Spinner específico para exclusão
     * @param {Object} options - Opções específicas para exclusão
     */
    const showDeleteSpinner = (options = {}) => {
        showSpinner({
            message: options.message || 'Excluindo...',
            subMessage: options.subMessage || 'Esta ação não pode ser desfeita',
            ...options
        });
    };

    /**
     * Spinner para operações de upload
     * @param {Object} options - Opções específicas para upload
     */
    const showUploadSpinner = (options = {}) => {
        showSpinner({
            message: options.message || 'Enviando arquivo...',
            subMessage: options.subMessage || 'Não feche esta janela',
            ...options
        });
    };

    /**
     * Verifica se o spinner está ativo
     * @returns {boolean} - Se o spinner está visível
     */
    const isSpinnerActive = () => spinnerState.show;

    /**
     * Obtém o estado atual do spinner
     * @returns {Object} - Estado completo do spinner
     */
    const getSpinnerState = () => ({ ...spinnerState });

    return {
        // Estado reativo
        spinnerState,

        // Métodos básicos
        showSpinner,
        hideSpinner,
        forceHideSpinner,
        updateSpinnerMessage,

        // Métodos helpers
        withSpinner,
        showFormSpinner,
        showDataSpinner,
        showDeleteSpinner,
        showUploadSpinner,

        // Getters
        isSpinnerActive,
        getSpinnerState
    };
};