import http from '@/http';

import {notifyError, notifySuccess} from "@/composables/messages";
import {toFormData} from "@/composables/cruds";
import {useGlobalSpinner} from '@/composables/useGlobalSpinner';
import {encodeId} from "@/composables/functions.js";

export default class BaseService {
    constructor(endpoint, config = {}) {
        this.spinner = useGlobalSpinner();
        this.endpoint = endpoint;
        this.formData = config.formData || {}; // Dados padrão do formulário
        this.filterConfig = config.filterConfig || []; // Configuração dos filtros
        this.tableConfig = config.tableConfig || []; // Configuração da tabela
        this.tableKeys = config.tableKeys || []; // Chaves da tabela
        this.actionTypes = config.actionTypes || []; // Tipos de ações disponíveis
        this.metadata = config.metadata || {}; // Ações permitidas na timeline
    }

    async index(session) {
        try {
            const obj = JSON.parse(localStorage.getItem(session));
            const response = await http.get(this.endpoint, {
                params: {...obj.params}
            });
            return response.data.data;
        } catch (error) {
            console.error(`Erro ao buscar ${this.endpoint}:`, error);
            notifyError(error.response?.data || 'Erro ao carregar dados');
            throw error;
        } finally {
            setTimeout(() => {
                this.spinner.hideSpinner();
            }, 350);
        }
    }

    /**
     * Busca um registro pelo ID
     * @param {Number} id - ID do registro
     * @returns {Promise} - Promise com o resultado
     */
    async getById(id) {
        this.spinner.showDataSpinner({message: 'Buscando informações...', zIndex: 99});
        try {
            const response = await http.get(`${this.endpoint}/${id}/`);
            return response.data.data;
        } catch (error) {
            console.error(`Erro ao buscar ${this.endpoint}/ por ID:`, error);
            notifyError(error.response?.data || 'Erro ao carregar dados');
            throw error;
        } finally {
            setTimeout(() => {
                this.spinner.hideSpinner();
            }, 350);
        }
    }

    /**
     * Salva um registro (cria ou atualiza)
     * @param {Object} data - Dados a serem salvos
     * @returns {Promise} - Promise com o resultado
     */
    async save(data) {
        try {
            this.spinner.showDataSpinner({message: 'Salvando...', zIndex: 99});
            let url = this.endpoint;
            if (data.id > 0) {
                data._method = 'PUT';
                url += `/${encodeId(data.id)}`;
            }

            const formData = toFormData(data);

            let response;
            response = await http.post(url, formData);

            return response.data;
        } catch (error) {
            notifyError(error.response?.data || 'Erro ao salvar dados');
            throw error.response;
        } finally {
            setTimeout(() => {
                this.spinner.hideSpinner();
            }, 350);
        }
    }

    /**
     * Altera o status de um registro
     * @param {Number} id - ID do registro
     * @param {String} sessionName - Nome da sessão para atualizar a lista após a alteração
     * @returns {Promise} - Promise com o resultado
     */
    async changeStatus(id, sessionName) {
        try {
            const response = await http.put(`${this.endpoint}/${id}/`);

            notifySuccess(response.data.message || 'Status alterado com sucesso!');

            // Atualiza a lista após alterar status
            if (sessionName) {
                const url = this._getUrl(sessionName);
                await this.apiStore.getApi(url);
            }

            return response.data;
        } catch (error) {
            console.error(`Erro ao alterar status de ${this.endpoint}/:`, error);
            notifyError(error.response?.data || 'Erro ao alterar status');
            throw error;
        }
    }

    /**
     * Remove um registro
     * @param {Number} id - ID do registro
     * @param {String} sessionName - Nome da sessão para atualizar a lista após a remoção
     * @returns {Promise} - Promise com o resultado
     */
    async delete(id, sessionName) {
        try {
            const response = await http.delete(`${this.endpoint}/${id}/`);

            notifySuccess(response.data.message || 'Registro excluído com sucesso!');

            // Atualiza a lista após deletar
            if (sessionName) {
                const url = this._getUrl(sessionName);
                await this.apiStore.getApi(url);
            }

            return response.data;
        } catch (error) {
            console.error(`Erro ao excluir ${this.endpoint}/:`, error);
            notifyError(error.response?.data || 'Erro ao excluir registro');
            throw error;
        }
    }

    /**
     * Busca endereço com base no CEP informado
     * @param {String} zipCode - CEP a ser consultado
     * @returns {Promise} - Promise com os dados do endereço
     */
    async getAddressByZipCode(zipCode) {
        try {
            const cleanZipCode = zipCode.replace(/\D/g, '');

            if (cleanZipCode.length === 8) {
                if (!/^[0-9]{8}$/.test(cleanZipCode)) {
                    throw new Error('CEP inválido');
                }

                const response = await fetch(`https://viacep.com.br/ws/${cleanZipCode}/json/`);
                const data = await response.json();

                if (data.erro) {
                    throw new Error('CEP não encontrado');
                }

                return {
                    uf: data.uf,
                    city: data.localidade,
                    neighborhood: data.bairro,
                    address: data.logradouro,
                    complement: data.complemento
                };
            }
        } catch (error) {
            console.error('Erro ao buscar endereço:', error);
            notifyError('Erro ao buscar endereço. Verifique o CEP informado.');
            throw error;
        }
    }

    /**
     * Busca categorias disponíveis para contatos
     * @returns {Promise} - Promise com a lista de categorias
     */
    async getCategories(type) {
        try {
            const response = await http.get(`categories/${type}`);
            return response.data.data;
        } catch (error) {
            console.error('Erro ao carregar categorias:', error);
            notifyError('Erro ao carregar categorias');
            throw error;
        }
    }

    async fetchGet(endpoint, params = {}) {
        try {
            const response = await http.get(endpoint, {
                params
            });
            return response.data;
        } catch (error) {
            console.error('Erro ao carregar categorias:', error);
            notifyError('Erro ao carregar categorias');
            throw error;
        }
    }

    /**
     * Exclui múltiplos registros em massa
     * @param {Array} ids - IDs dos registros a serem excluidos
     * @returns {Promise} - Promise com o resultado
     */
    async bulkDelete(ids) {
        try {
            this.spinner.showDataSpinner({message: 'Excluindo registros...', zIndex: 99});
            const response = await http.post(`${this.endpoint}/bulk-delete`, { ids });
            notifySuccess(response.data.message || 'Registros excluidos com sucesso!');
            return response.data;
        } catch (error) {
            console.error(`Erro ao excluir ${this.endpoint}/:`, error);
            notifyError(error.response?.data || 'Erro ao excluir registros');
            throw error;
        } finally {
            setTimeout(() => {
                this.spinner.hideSpinner();
            }, 350);
        }
    }

    /**
     * Altera o status ativo de múltiplos registros em massa
     * @param {Array} ids - IDs dos registros
     * @param {Boolean} active - Novo status ativo
     * @returns {Promise} - Promise com o resultado
     */
    async bulkChangeActive(ids, active) {
        try {
            this.spinner.showDataSpinner({message: 'Alterando status...', zIndex: 99});
            const response = await http.post(`${this.endpoint}/bulk-change-active`, { ids, active });
            notifySuccess(response.data.message || 'Status alterado com sucesso!');
            return response.data;
        } catch (error) {
            console.error(`Erro ao alterar status de ${this.endpoint}/:`, error);
            notifyError(error.response?.data || 'Erro ao alterar status');
            throw error;
        } finally {
            setTimeout(() => {
                this.spinner.hideSpinner();
            }, 350);
        }
    }

    /**
     * Retorna o objeto de formulário padrão
     * @returns {Object} - Objeto com valores padrão
     */
    getDefaultFormData() {
        return JSON.parse(JSON.stringify(this.formData));
    }

    /**
     * Retorna configuração de filtros
     * @returns {Array} - Array de configurações de filtros
     */
    getFilterConfig() {
        return JSON.parse(JSON.stringify(this.filterConfig));
    }

    /**
     * Retorna configuração da tabela
     * @returns {Array} - Array de configurações de colunas
     */
    getTableConfig() {
        return this.tableConfig;
    }

    /**
     * Retorna as chaves para a tabela
     * @returns {Array} - Array de chaves
     */
    getTableKeys() {
        return this.tableKeys;
    }

    /**
     * Retorna os tipos de ações disponíveis na listagem
     * @returns {Array} - Array de tipos de ações
     */
    getActionTypes() {
        return this.actionTypes;
    }
}