<script setup>
import {ref, watch, onMounted} from "vue";
import http from "@/http";
import {notifyError, notifySuccess} from "@/composables/messages";
import {encodeId} from "@/composables/functions.js";
import {usePermissions} from "@/utils/permissions.js";

// Props recebidas
const props = defineProps({
    value: Object,
    endpoint: String,
});

// Converte para boolean corretamente (trata string "0", "1", number e boolean)
const toBoolean = (val) => val === true || val === 1 || val === '1';

// Controle de carregamento e status local
const load = ref(true);
const status = ref(toBoolean(props.value.active));
const hasPermission = ref(false);

// Verifica permissão ao montar
onMounted(async () => {
    hasPermission.value = await usePermissions.hasPermission(`${props.endpoint}.change-active`);
});

// Atualiza o status quando a prop mudar (reatividade)
watch(() => props.value.active, (newVal) => {
    status.value = toBoolean(newVal);
}, { immediate: false });

const changeStatus = () => {
    if (!hasPermission.value) return;

    if (load.value) {
        load.value = false;

        http
            .put(`${props.endpoint}/change-active/${encodeId(props.value.id)}`)
            .then(() => {
                status.value = !status.value;
                notifySuccess(
                    `Status alterado para ${status.value ? "Ativo" : "Inativo"}`
                );
            })
            .catch((errors) => {
                console.error(errors);
                notifyError(errors.response);
            })
            .finally(() => {
                setTimeout(() => {
                    load.value = true;
                }, 200);
            });
    }
};
</script>

<template>
    <!-- Com permissão: clicável -->
    <span
        v-if="hasPermission"
        class="pointer fs-14 badge"
        :class="status ? 'bg-success-subtle' : 'bg-danger-subtle'"
        @click.prevent="changeStatus"
    >
        <span v-if="load">{{ status ? 'Ativo' : 'Inativo' }}</span>
        <BSpinner
            v-else
            style="height: 1rem; width: 1rem;"
        />
    </span>

    <!-- Sem permissão: apenas visual -->
    <span
        v-else
        class="fs-14 badge"
        :class="status ? 'bg-success-subtle' : 'bg-danger-subtle'"
    >
        {{ status ? 'Ativo' : 'Inativo' }}
    </span>
</template>
