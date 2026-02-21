// composables/convertVariables.js

/**
 * Converte string para PascalCase
 * @param {string} str - String para converter
 * @returns {string} - String em PascalCase
 */
export function toPascalCase(str) {
    if (!str || typeof str !== 'string') return '';

    return str
        .replace(/_/g, ' ') // Substitui underscores por espaços
        .replace(/([a-z])([A-Z])/g, '$1 $2') // Adiciona espaço antes de maiúsculas
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join('');
}

/**
 * Converte string para camelCase
 * @param {string} str - String para converter
 * @returns {string} - String em camelCase
 */
export function toCamelCase(str) {
    if (!str || typeof str !== 'string') return '';

    const pascalCase = toPascalCase(str);
    return pascalCase.charAt(0).toLowerCase() + pascalCase.slice(1);
}

/**
 * Converte string para snake_case
 * @param {string} str - String para converter
 * @returns {string} - String em snake_case
 */
export function toSnakeCase(str) {
    if (!str || typeof str !== 'string') return '';

    return str
        .replace(/([A-Z])/g, ' $1') // Adiciona espaço antes de maiúsculas
        .trim()
        .toLowerCase()
        .replace(/[\s-]+/g, '_'); // Substitui espaços e hífens por underscores
}

/**
 * Converte string para kebab-case
 * @param {string} str - String para converter
 * @returns {string} - String em kebab-case
 */
export function toKebabCase(str) {
    if (!str || typeof str !== 'string') return '';

    return str
        .replace(/([A-Z])/g, ' $1') // Adiciona espaço antes de maiúsculas
        .trim()
        .toLowerCase()
        .replace(/[\s_]+/g, '-'); // Substitui espaços e underscores por hífens
}

/**
 * Converte string para Title Case
 * @param {string} str - String para converter
 * @returns {string} - String em Title Case
 */
export function toTitleCase(str) {
    if (!str || typeof str !== 'string') return '';

    return str
        .replace(/_/g, ' ') // Substitui underscores por espaços
        .replace(/([a-z])([A-Z])/g, '$1 $2') // Adiciona espaço antes de maiúsculas
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
}

/**
 * Converte valor para boolean de forma segura
 * @param {any} value - Valor para converter
 * @returns {boolean} - Valor convertido para boolean
 */
export function toBoolean(value) {
    if (typeof value === 'boolean') return value;
    if (typeof value === 'string') {
        const lowerValue = value.toLowerCase();
        return lowerValue === 'true' || lowerValue === '1' || lowerValue === 'yes';
    }
    if (typeof value === 'number') return value !== 0;
    return Boolean(value);
}

/**
 * Converte valor para número de forma segura
 * @param {any} value - Valor para converter
 * @param {number} defaultValue - Valor padrão se conversão falhar
 * @returns {number} - Valor convertido para número
 */
export function toNumber(value, defaultValue = 0) {
    if (typeof value === 'number' && !isNaN(value)) return value;

    const converted = Number(value);
    return isNaN(converted) ? defaultValue : converted;
}

/**
 * Converte valor para string de forma segura
 * @param {any} value - Valor para converter
 * @param {string} defaultValue - Valor padrão se conversão falhar
 * @returns {string} - Valor convertido para string
 */
export function toString(value, defaultValue = '') {
    if (value === null || value === undefined) return defaultValue;
    return String(value);
}

/**
 * Converte array de strings em array de objetos com value/label
 * @param {Array} array - Array de strings
 * @param {string} valueProp - Nome da propriedade para value
 * @param {string} labelProp - Nome da propriedade para label
 * @returns {Array} - Array de objetos
 */
export function arrayToOptions(array, valueProp = 'value', labelProp = 'label') {
    if (!Array.isArray(array)) return [];

    return array.map(item => {
        if (typeof item === 'object' && item !== null) {
            return item; // Já é objeto, retorna como está
        }

        return {
            [valueProp]: item,
            [labelProp]: toString(item)
        };
    });
}

/**
 * Converte objeto em array de opções para select
 * @param {Object} obj - Objeto para converter
 * @param {string} valueProp - Nome da propriedade para value
 * @param {string} labelProp - Nome da propriedade para label
 * @returns {Array} - Array de objetos
 */
export function objectToOptions(obj, valueProp = 'value', labelProp = 'label') {
    if (!obj || typeof obj !== 'object') return [];

    return Object.entries(obj).map(([key, value]) => ({
        [valueProp]: key,
        [labelProp]: toString(value)
    }));
}

/**
 * Remove propriedades vazias de um objeto
 * @param {Object} obj - Objeto para limpar
 * @param {boolean} deep - Se deve fazer limpeza profunda
 * @returns {Object} - Objeto limpo
 */
export function removeEmptyProperties(obj, deep = false) {
    if (!obj || typeof obj !== 'object') return obj;

    const cleaned = {};

    Object.keys(obj).forEach(key => {
        const value = obj[key];

        // Verifica se valor não está vazio
        if (value !== null && value !== undefined && value !== '') {
            if (deep && typeof value === 'object' && !Array.isArray(value)) {
                const cleanedNested = removeEmptyProperties(value, deep);
                if (Object.keys(cleanedNested).length > 0) {
                    cleaned[key] = cleanedNested;
                }
            } else if (Array.isArray(value) && value.length > 0) {
                cleaned[key] = value;
            } else if (!Array.isArray(value)) {
                cleaned[key] = value;
            }
        }
    });

    return cleaned;
}

/**
 * Faz merge profundo de dois objetos
 * @param {Object} target - Objeto alvo
 * @param {Object} source - Objeto fonte
 * @returns {Object} - Objeto mesclado
 */
export function deepMerge(target, source) {
    if (!target || typeof target !== 'object') target = {};
    if (!source || typeof source !== 'object') return target;

    const result = { ...target };

    Object.keys(source).forEach(key => {
        if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
            result[key] = deepMerge(result[key], source[key]);
        } else {
            result[key] = source[key];
        }
    });

    return result;
}

/**
 * Clona um objeto profundamente
 * @param {any} obj - Objeto para clonar
 * @returns {any} - Objeto clonado
 */
export function deepClone(obj) {
    if (obj === null || typeof obj !== 'object') return obj;
    if (obj instanceof Date) return new Date(obj.getTime());
    if (obj instanceof Array) return obj.map(item => deepClone(item));
    if (typeof obj === 'object') {
        const clonedObj = {};
        Object.keys(obj).forEach(key => {
            clonedObj[key] = deepClone(obj[key]);
        });
        return clonedObj;
    }
    return obj;
}

/**
 * Converte objeto em FormData
 * @param {Object} obj - Objeto para converter
 * @param {FormData} formData - FormData existente (opcional)
 * @param {string} parentKey - Chave pai (para recursão)
 * @returns {FormData} - FormData criado
 */
export function objectToFormData(obj, formData = new FormData(), parentKey = '') {
    if (!obj || typeof obj !== 'object') return formData;

    Object.keys(obj).forEach(key => {
        const value = obj[key];
        const formKey = parentKey ? `${parentKey}[${key}]` : key;

        if (value === null || value === undefined) {
            // Não adiciona valores nulos
            return;
        } else if (value instanceof File || value instanceof Blob) {
            formData.append(formKey, value);
        } else if (Array.isArray(value)) {
            value.forEach((item, index) => {
                if (typeof item === 'object' && !(item instanceof File) && !(item instanceof Blob)) {
                    objectToFormData(item, formData, `${formKey}[${index}]`);
                } else {
                    formData.append(`${formKey}[${index}]`, item);
                }
            });
        } else if (typeof value === 'object') {
            objectToFormData(value, formData, formKey);
        } else {
            formData.append(formKey, toString(value));
        }
    });

    return formData;
}

/**
 * Define valor em um objeto usando caminho (dot notation)
 * @param {Object} obj - Objeto alvo
 * @param {string} path - Caminho para definir o valor
 * @param {any} value - Valor para definir
 * @returns {Object} - Objeto modificado
 */
export function setNestedValue(obj, path, value) {
    if (!obj || typeof obj !== 'object' || !path) return obj;

    const keys = path.split('.');
    let current = obj;

    for (let i = 0; i < keys.length - 1; i++) {
        const key = keys[i];
        if (!current[key] || typeof current[key] !== 'object') {
            current[key] = {};
        }
        current = current[key];
    }

    current[keys[keys.length - 1]] = value;
    return obj;
}

/**
 * Formata bytes em formato legível
 * @param {number} bytes - Número de bytes
 * @param {number} decimals - Número de decimais
 * @returns {string} - Tamanho formatado
 */
export function formatBytes(bytes, decimals = 2) {
    if (!bytes || bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

/**
 * Trunca texto com reticências
 * @param {string} text - Texto para truncar
 * @param {number} length - Comprimento máximo
 * @param {string} suffix - Sufixo (padrão: '...')
 * @returns {string} - Texto truncado
 */
export function truncateText(text, length = 100, suffix = '...') {
    if (!text || typeof text !== 'string') return '';
    if (text.length <= length) return text;

    return text.substring(0, length).trim() + suffix;
}

/**
 * Debounce de função
 * @param {Function} func - Função para fazer debounce
 * @param {number} wait - Tempo de espera em ms
 * @param {boolean} immediate - Se deve executar imediatamente
 * @returns {Function} - Função com debounce
 */
export function debounce(func, wait, immediate = false) {
    let timeout;

    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };

        const callNow = immediate && !timeout;

        clearTimeout(timeout);
        timeout = setTimeout(later, wait);

        if (callNow) func(...args);
    };
}