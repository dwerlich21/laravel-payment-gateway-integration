import {notifyError} from "./messages.js";
import {getUrl} from "@/composables/functions";
import {endLoading} from "@/composables/spinners";

/**
 * Função que converte json e FormData
 * @param formData json com campos do formulário
 * @return FormData
 */
export function toFormData(formData) {
    let data = new FormData();

    // Adicionar automaticamente todas as propriedades de this.formData
    Object.keys(formData).forEach(key => {

        if (formData[key] != null && formData[key] !== '' && formData[key] !== 'null') {
            // Adicionar ao FormData se for uma string, número ou booleano
            if (typeof formData[key] === 'string' || typeof formData[key] === 'number') {
                data.append(key, formData[key]);
            } else if (typeof formData[key] === 'boolean') {
                data.append(key, formData[key] ? '1' : '0');
            } else if (formData[key] instanceof File) {
                // Adicionar ao FormData se for um arquivo
                data.append(key, formData[key]);
            } else {
                // Tratar como array/objeto
                data = toFormDataArray(data, key, formData[key]);
            }
        }

    });

    return data;
}

/**
 * Função para inserir os sub-arrays no FormData de origem
 * @param data
 * @param key
 * @param array
 * @return {*}
 */
function toFormDataArray(data, key, array) {

    Object.keys(array).forEach(k => {
        if (array[k] != null && array[k] !== '' && array[k] !== 'null') {
            if (typeof array[k] === 'string' || typeof array[k] === 'number') {
                data.append(`${key}[${k}]`, array[k]);
            } else if (typeof array[k] === 'boolean') {
                data.append(`${key}[${k}]`, array[k] ? '1' : '0');
            } else if (array[k] instanceof File) {
                data.append(`${key}[${k}]`, array[k]);
            } else {
                data = toFormDataArray(data, `${key}[${k}]`, array[k]);
            }
        }

    });

    return data;
}

/**
 * Função de validação de Formulários
 * @param id  do formulário
 * @return {boolean}
 */
export function ValidateForm(id) {

    const form = document.getElementById(id);
    form.classList.add('was-validated');
    const inputs = form.getElementsByClassName('form-control');

    for (let i = 0; i < inputs.length; i++) {
        inputs[i].classList.remove('is-invalid')
    }

    for (let i = 0; i < inputs.length; i++) {
        if (inputs[i].value === '' &&
            (inputs[i].getAttribute('required') === '' || inputs[i].getAttribute('required') === true)) {
            notifyError('Preencha todos os campos obrigatórios!')
            console.log(inputs[i].name)
            return false;
        }
    }

    return true;
}

/**
 * Função reset da listagem
 * @param session sessão da página
 */
export async function resetTable(session) {
    const searchUrl = getUrl(session);
}

/**
 * Função reset de Formulário e fechar modal
 * @param id  do formulário
 * @param button id do botão para desabilitar
 */
export async function resetModal(id, button) {
    document.getElementById('form').classList.remove('was-validated');
    endLoading(id, button);
}

/**
 * Função responsável por enviar os dados a store e tratar o resultado
 * @param url que vai receber os dados
 * @param formData dados do formulário já tratados
 * @param form id do formulário que vai sofrer opacidade
 * @param button id do botão para desabilitar
 * @param session sessão da página
 * @param reset se deve resetar a tabela após o envio
 */
export async function insertORUpdate(url, formData, form, button, session, reset = true) {
    const data = {
        url,
        formData
    }

    if (result) {
        await resetModal(form, button);
        if (reset) await resetTable(session);
    }

    return result;
}
