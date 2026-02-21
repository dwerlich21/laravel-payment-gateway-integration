<template>
  <div class="form-actions d-flex justify-content-end mt-4">
    <BButton
      variant="soft-danger"
      class="me-2"
      @click="router.back();"
    >
      <i class="ri-close-line me-1" />
      Cancelar
    </BButton>

    <BButton
      variant="soft-success"
      :disabled="isSubmitting"
      @click="$emit('submit')"
    >
      <BSpinner
        v-if="isSubmitting"
        small
        class="me-1"
      />
      <i
        v-else
        class="ri-save-line me-1"
      />
      {{ isEditing ? 'Atualizar' : 'Salvar' }}
    </BButton>
  </div>
</template>

<script setup>
import {defineProps, defineEmits} from 'vue';
import router from "@/router";

defineProps({
    isEditing: {
        type: Boolean,
        required: true
    },
    isSubmitting: {
        type: Boolean,
        default: false
    }
});

defineEmits(['submit']);

</script>