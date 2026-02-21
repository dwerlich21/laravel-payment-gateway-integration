<template>
  <div
    class="spinner-container"
    :class="containerClasses"
  >
    <!-- Conteúdo principal -->
    <div
      class="content-wrapper"
      :class="{ 'loading': loading }"
    >
      <slot />
    </div>
        
    <!-- Spinner regional -->
    <RegionalSpinner
      :show="loading"
      :message="message"
      :size="size"
      :overlay="overlay"
      :blur="blur"
      :full-height="fullHeight"
    />
  </div>
</template>

<script setup>
import { computed, defineProps } from 'vue';
import RegionalSpinner from './RegionalSpinner.vue';

const props = defineProps({
    loading: {
        type: Boolean,
        default: false
    },
    message: {
        type: String,
        default: 'Carregando...'
    },
    size: {
        type: String,
        default: 'md'
    },
    overlay: {
        type: Boolean,
        default: true
    },
    blur: {
        type: Boolean,
        default: true
    },
    fullHeight: {
        type: Boolean,
        default: false
    },
    minHeight: {
        type: String,
        default: ''
    },
    position: {
        type: String,
        default: 'relative'
    }
});

const containerClasses = computed(() => ({
    'position-relative': props.position === 'relative',
    'position-absolute': props.position === 'absolute',
    'min-height-set': props.minHeight
}));
</script>

<style lang="scss" scoped>
.spinner-container {
    position: relative;
    
    &.position-relative {
        position: relative;
    }
    
    &.position-absolute {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }
    
    &.min-height-set {
        min-height: v-bind(minHeight);
    }
}

.content-wrapper {
    transition: opacity 0.3s ease;
    
    &.loading {
        opacity: 0.6;
        pointer-events: none;
    }
}
</style>