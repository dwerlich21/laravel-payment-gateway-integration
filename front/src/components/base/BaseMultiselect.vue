<template>
  <div>
    <div
      v-if="label"
      class="d-flex w-100 justify-content-between align-items-center"
    >
      <label>
        {{ label }}
        <span
          v-if="required"
          class="text-danger"
        >*
        </span>
      </label>
      <IconInfo v-if="createOption" />
    </div>

    <Multiselect
      v-model="selectedValue"
      :options="options"
      :mode="mode"
      :searchable="searchable"
      :create-option="createOption"
      :close-on-select="closeOnSelect"
      :placeholder="placeholder"
      :disabled="disabled"
      :loading="loading"
      :class="{
        'is-error': errors.length > 0,
        'is-invalid': required && !selectedValue,
        'is-valid': (!required || selectedValue) && errors.length === 0,
      }"
      no-results-text="Nenhuma opção encontrada"
      no-options-text="Nenhuma opção encontrada"
      :open-direction="openDirection !== 'auto' ? openDirection : undefined"
      @open="$emit('open')"
      @close="$emit('close')"
      @select="$emit('select', $event)"
      @deselect="$emit('deselect', $event)"
      @input="updateValue"
      @search-change="$emit('search-change', $event)"
      @tag="$emit('tag', $event)"
    />

    <small
      v-if="errors.length > 0"
      class="form-text text-danger mt-1"
    >{{ errors[0] }}</small>

    <small
      v-else-if="helpText"
      class="form-text text-muted mt-1"
    >{{ helpText }}</small>
  </div>
</template>

<script setup>
import {ref, watch, onMounted, defineProps, defineEmits} from 'vue';
import Multiselect from '@vueform/multiselect';
import "@vueform/multiselect/themes/default.css";
import IconInfo from "@/components/base/IconInfo.vue";

const props = defineProps({
    modelValue: {
        type: [String, Number, Object, Array],
        default: null
    },
    label: {
        type: String,
        default: ''
    },
    required: {
        type: Boolean,
        default: false
    },
    placeholder: {
        type: String,
        default: 'Selecione uma opção'
    },
    options: {
        type: Array,
        default: () => []
    },
    mode: {
        type: String,
        default: 'single',
        validator: (value) => ['single', 'multiple', 'tags'].includes(value)
    },
    searchable: {
        type: Boolean,
        default: true
    },
    createOption: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    },
    closeOnSelect: {
        type: Boolean,
        default: true
    },
    openDirection: {
        type: String,
        default: 'bottom',
        validator: (value) => ['top', 'bottom', 'auto'].includes(value)
    },
    helpText: {
        type: String,
        default: ''
    },
    disabled: {
        type: Boolean,
        default: false
    },
    errors: {
        type: Array,
        default: () => []
    }
});

// const chatAI = inject('chatAI');

const emit = defineEmits([
    'update:modelValue',
    'open',
    'close',
    'select',
    'deselect',
    'search-change',
    'change',
    'tag'
]);

const selectedValue = ref(props.modelValue);

watch(() => selectedValue.value, (newValue) => {
    if (newValue !== '' && typeof newValue === 'string') {
        // verifyDescription();
    }
}, {deep: true, immediate: true})

// async function verifyDescription() {
//     const resultado = await chatAI.verifyContent(
//         selectedValue.value,
//         {type: props.label}
//     );
//
//     if (!resultado.approved) {
//         chatAI.addAIMessage(`Detectei um problema na descrição da categoria: ${resultado.message}\n\nSugestão: ${resultado.suggestions.join(' ')}`);
//         chatAI.open();
//     }
// }

watch(() => props.modelValue, (newValue) => {
    selectedValue.value = newValue;
});

const updateValue = (val) => {
    emit('update:modelValue', val);
};

onMounted(() => {
    selectedValue.value = props.modelValue;
});
</script>