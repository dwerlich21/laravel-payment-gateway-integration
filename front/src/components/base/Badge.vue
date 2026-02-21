<template>
  <span
    class="badge"
    :class="[`bg-${variant}-subtle`, ...addClass]"
  >
    <i
      v-if="icon"
      :class="[icon, {
        'me-1': label
      }]"
    />
    {{ label }}
  </span>
</template>

<script setup>
import {defineProps} from 'vue';

defineProps({
    variant: {
        type: String,
        required: true,
        default: () => 'primary'
    },
    icon: {
        type: String,
        required: false,
    },
    label: {
        type: String,
        required: false,
    },
    addClass: {
        type: Array,
        required: false,
        default: () => []
    }
});
</script>