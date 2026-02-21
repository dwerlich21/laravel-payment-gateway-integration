<!--eslint-disable no-mixed-spaces-and-tabs-->
<template>
  <b-modal
    id="modalRegister"
    v-model="openedModal"
    centered
    :title="'Cadastrar ' + props.title "
    header-class="p-3 bg-soft-primary text-primary"
    :size="props.size"
    class="v-modal-custom"
    scrollable
    @hidden="emits('close-modal')"
  >
    <slot name="form-modal" />

    <template #footer>
      <button
        id="closeModalRegister"
        type="button"
        class="btn btn-soft-danger"
        @click="emits('close-modal')"
      >
        Cancelar
      </button>
      <button
        id="save"
        type="button"
        class="btn btn-soft-success"
        @click.prevent="validation"
      >
        Salvar
      </button>
    </template>
  </b-modal>
</template>

<script setup>
import {ref, watch, defineProps, defineEmits} from "vue";
import {ValidateForm} from "@/composables/cruds";
import {endLoading, startLoading} from "@/composables/spinners";

const props = defineProps({
    session: String,
    title: String,
    size: {
        type: String,
        required: false,
        default: ''
    }
});

const emits = defineEmits(['submit-form', 'close-modal']);

let openedModal = ref(false);

function validation() {
    startLoading('form', 'save');
    if (!ValidateForm('form')) {
        endLoading('form', 'save');
        return;
    }
    emits('submit-form');
}

watch(() => apiStore.showModalForm, (newValue) => {
    openedModal.value = newValue;
});

</script>
