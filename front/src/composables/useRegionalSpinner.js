import { ref, computed } from 'vue';

// Estado global dos spinners regionais
const spinners = ref(new Map());

// Exporta o estado para acesso externo
export const spinnersState = spinners;

export function useRegionalSpinner() {
    
    /**
     * Cria ou atualiza um spinner regional
     * @param {string} id - ID único do spinner
     * @param {Object} options - Opções do spinner
     */
    function showSpinner(id, options = {}) {
        const defaultOptions = {
            show: true,
            message: 'Carregando...',
            size: 'md',
            overlay: true,
            blur: true,
            zIndex: 10,
            position: 'absolute',
            fullHeight: false
        };
        
        const spinnerOptions = { ...defaultOptions, ...options, show: true };
        spinners.value.set(id, spinnerOptions);
    }
    
    /**
     * Oculta um spinner regional
     * @param {string} id - ID do spinner
     */
    function hideSpinner(id) {
        if (spinners.value.has(id)) {
            const currentOptions = spinners.value.get(id);
            spinners.value.set(id, { ...currentOptions, show: false });
            
            // Remove o spinner após a animação
            setTimeout(() => {
                spinners.value.delete(id);
            }, 300);
        }
    }
    
    /**
     * Obtém as opções de um spinner específico
     * @param {string} id - ID do spinner
     * @returns {Object} - Opções do spinner
     */
    function getSpinner(id) {
        return computed(() => spinners.value.get(id) || { show: false });
    }
    
    /**
     * Verifica se um spinner está ativo
     * @param {string} id - ID do spinner
     * @returns {boolean} - True se estiver ativo
     */
    function isSpinnerActive(id) {
        return computed(() => {
            const spinner = spinners.value.get(id);
            return spinner ? spinner.show : false;
        });
    }
    
    /**
     * Atualiza a mensagem de um spinner ativo
     * @param {string} id - ID do spinner
     * @param {string} message - Nova mensagem
     */
    function updateSpinnerMessage(id, message) {
        if (spinners.value.has(id)) {
            const currentOptions = spinners.value.get(id);
            spinners.value.set(id, { ...currentOptions, message });
        }
    }
    
    /**
     * Oculta todos os spinners regionais
     */
    function hideAllSpinners() {
        spinners.value.forEach((options, id) => {
            hideSpinner(id);
        });
    }
    
    /**
     * Obtém a contagem de spinners ativos
     * @returns {number} - Número de spinners ativos
     */
    function getActiveSpinnersCount() {
        return computed(() => {
            return Array.from(spinners.value.values()).filter(spinner => spinner.show).length;
        });
    }
    
    /**
     * Spinner para tabelas e listas
     * @param {string} id - ID do spinner
     * @param {string} message - Mensagem opcional
     */
    function showTableSpinner(id, message = 'Carregando dados...') {
        showSpinner(id, {
            message,
            size: 'md',
            overlay: true,
            blur: true,
            fullHeight: true
        });
    }
    
    /**
     * Spinner para operações de filtro
     * @param {string} id - ID do spinner
     * @param {string} message - Mensagem opcional
     */
    function showFilterSpinner(id, message = 'Aplicando filtros...') {
        showSpinner(id, {
            message,
            size: 'sm',
            overlay: true,
            blur: false
        });
    }
    
    /**
     * Spinner para paginação
     * @param {string} id - ID do spinner
     * @param {string} message - Mensagem opcional
     */
    function showPaginationSpinner(id, message = 'Carregando página...') {
        showSpinner(id, {
            message,
            size: 'sm',
            overlay: true,
            blur: true
        });
    }
    
    /**
     * Spinner para kanban
     * @param {string} id - ID do spinner
     * @param {string} message - Mensagem opcional
     */
    function showKanbanSpinner(id, message = 'Carregando kanban...') {
        showSpinner(id, {
            message,
            size: 'md',
            overlay: true,
            blur: true,
            fullHeight: true
        });
    }
    
    /**
     * Spinner para botões e ações pequenas
     * @param {string} id - ID do spinner
     * @param {string} message - Mensagem opcional
     */
    function showButtonSpinner(id, message = 'Processando...') {
        showSpinner(id, {
            message,
            size: 'xs',
            overlay: false,
            blur: false
        });
    }
    
    return {
        // Métodos básicos
        showSpinner,
        hideSpinner,
        getSpinner,
        isSpinnerActive,
        updateSpinnerMessage,
        hideAllSpinners,
        getActiveSpinnersCount,
        
        // Métodos especializados
        showTableSpinner,
        showFilterSpinner,
        showPaginationSpinner,
        showKanbanSpinner,
        showButtonSpinner
    };
}

// Função utilitária para cleanup automático
export function useSpinnerAutoCleanup(id, timeout = 30000) {
    const { hideSpinner } = useRegionalSpinner();
    
    // Cleanup automático após timeout
    const cleanupTimeout = setTimeout(() => {
        hideSpinner(id);
    }, timeout);
    
    return {
        cleanup: () => {
            clearTimeout(cleanupTimeout);
            hideSpinner(id);
        }
    };
}