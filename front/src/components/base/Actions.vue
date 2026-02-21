<script setup>
import {ref, onMounted} from "vue";
import {showAlertConfirm, Forbidden, encodeId} from "@/composables/functions";
import http from "@/http";
import {endLoading, startLoading} from "@/composables/spinners";
import {usePermissions} from "@/utils/permissions.js";

const props = defineProps({
    types: {
        type: Array,
        required: true
    },
    value: {
        type: Object,
        required: true
    },
    endpoint: {
        type: String,
        required: true
    },
    session: {
        type: String,
        required: true
    },
});

const emits = defineEmits(['set-form-data', 'deleted']);

// Permissões
const canEdit = ref(false);
const canDelete = ref(false);

onMounted(async () => {
    canEdit.value = await usePermissions.hasPermission(`${props.endpoint}.update`);
    canDelete.value = await usePermissions.hasPermission(`${props.endpoint}.destroy`);
});

const getView = (id) => {
    startLoading('form', 'save');

    http.get(`${props.endpoint}/${encodeId(id)}`)
        .then(response => {
            emits('set-form-data', response.data.message)
        })
        .catch(errors => {
            console.error(errors);
            endLoading('form', 'save');
            Forbidden(errors);
        });
}

// Exclusão de item da tabela
const delElement = async () => {
    const result = await showAlertConfirm('Seus dados serão removidos e não poderão mais ser recuperados.');

    if (result) {
        try {
            await http.delete(`${props.endpoint}/${encodeId(props.value.id)}`);
            emits('deleted');
        } catch (error) {
            console.error(error);
            Forbidden(error);
        }
    }
}
</script>

<template>
    <span>
        <slot name="add-icons"/>

        <!-- Editar via modal -->
        <i
            v-if="props.types.indexOf('modal') > -1 && canEdit"
            v-b-tooltip.hover="'Editar Registro'"
            class="bx bx-pencil text-info fs-14 mx-1 pointer"
            @click="getView(props.value.id)"
        />

        <!-- Editar via página -->
        <router-link
            v-if="props.types.indexOf('page') > -1 && canEdit"
            :to="{ name: `${endpoint.replace('/','')}-form` , params: { id: encodeId(props.value.id) }}"
        >
            <i
                v-b-tooltip.hover="'Editar Registro'"
                class="bx bx-pencil text-info fs-14 mx-1 pointer"
            />
        </router-link>

        <!-- Excluir -->
        <i
            v-if="props.types.indexOf('delete') > -1 && canDelete"
            v-b-tooltip.hover="'Excluir Registro'"
            class="bx bx-trash text-danger fs-14 mx-1 pointer"
            @click="delElement"
        />
    </span>
</template>
