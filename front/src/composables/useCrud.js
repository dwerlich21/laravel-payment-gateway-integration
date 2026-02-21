import { ref, reactive, onMounted } from 'vue';
import { setSessions } from '@/composables/setSessions';
import { notifyError, notifySuccess } from '@/composables/messages';
import { startLoading, endLoading } from '@/composables/spinners';
import { Forbidden } from '@/composables/functions';

/**
 * Hook para gerenciar operações CRUD
 * @param {Object} service - Instância do serviço a ser usado
 * @param {Object} defaultFormData - Objeto com valores padrão para o formulário
 * @param {String} sessionName - Nome da sessão para armazenar filtros e paginação
 * @param {Object} options - Opções adicionais
 * @returns {Object} - Métodos e propriedades para gerenciar CRUD
 */
export default function useCrud(service, defaultFormData, sessionName, options = {}) {
    // Estado
    const formData = ref(JSON.parse(JSON.stringify(defaultFormData)));
    const isLoading = ref(false);
    const isModalOpen = ref(false);
    const viewType = ref(localStorage.getItem(`views${sessionName}`) || 'table');
    const showFilters = ref(false);
    const filterData = reactive({});

    // Inicialização
    onMounted(() => {
            setSessions(sessionName);

        // Carrega dados adicionais se houver callbacks
        if (typeof options.onMounted === 'function') {
            options.onMounted();
        }
    });


    /**
     * Salva o registro (cria ou atualiza)
     */
    const saveData = async () => {
        try {
            startLoading('form', 'save');

            // Validação customizada
            if (typeof options.validateForm === 'function') {
                const isValid = options.validateForm(formData.value);
                if (!isValid) {
                    endLoading('form', 'save');
                    return;
                }
            }

            // Pré-processamento de dados
            if (typeof options.prepareData === 'function') {
                options.prepareData(formData.value);
            }

            // Salva os dados
            await service.save(formData.value, sessionName);

            // Pós-processamento
            if (typeof options.afterSave === 'function') {
                options.afterSave();
            }

            notifySuccess('Operação realizada com sucesso!');
        } catch (error) {
            console.error('Erro ao salvar dados:', error);
            notifyError(error.response?.data || 'Erro ao salvar dados');
            Forbidden(error);
        } finally {
            endLoading('form', 'save');
        }
    };

    /**
     * Remove um registro
     * @param {Number} id - ID do registro a ser removido
     */
    const deleteData = async (id) => {
        try {
            await service.delete(id, sessionName);

            // Callback após deletar
            if (typeof options.afterDelete === 'function') {
                options.afterDelete();
            }

            notifySuccess('Registro excluído com sucesso!');
        } catch (error) {
            console.error('Erro ao excluir registro:', error);
            notifyError(error.response?.data || 'Erro ao excluir registro');
            Forbidden(error);
        }
    };

    /**
     * Altera o status de um registro
     * @param {Number} id - ID do registro
     */
    const changeStatus = async (id) => {
        try {
            await service.changeStatus(id, sessionName);

            // Callback após alteração de status
            if (typeof options.afterChangeStatus === 'function') {
                options.afterChangeStatus();
            }
        } catch (error) {
            console.error('Erro ao alterar status:', error);
            notifyError(error.response?.data || 'Erro ao alterar status');
            Forbidden(error);
        }
    };



    return {
        // Estado
        formData,
        isLoading,
        isModalOpen,
        viewType,
        showFilters,
        filterData,

        // Métodos
        saveData,
        deleteData,
        changeStatus,
    };
}