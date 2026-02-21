import { useAuthStore } from "@/stores/auth.js";
import { computed } from "vue";

export function getOptions(key) {
    return computed(() => {
        const authStore = useAuthStore();
        return authStore.enums[key] || [];
    });
}

function getComparation(value, options, key) {
    let val = '';

    options.forEach(el => {
        if (el.value === value) {
            val = el[key];
        }
    })

    return val;
}

/**
 * Retorna o valor de um enum
 * @param {string} value
 * @param classEnum
 * @param key
 * @returns {string}
 */
export function getValueEnum(value, classEnum, key) {
    const options = getOptions(classEnum);

    if(!options.value || options.value.length === 0) {
        setTimeout(() => {
            getValueEnum(value, classEnum, key);
        })
    } else {
        return getComparation(value, options.value, key);
    }
}