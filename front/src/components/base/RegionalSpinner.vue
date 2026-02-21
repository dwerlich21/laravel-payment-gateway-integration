<template>
  <div 
    v-if="show" 
    class="regional-spinner-overlay"
    :class="overlayClasses"
    :style="overlayStyles"
  >
    <div class="regional-spinner-container">
      <!-- Spinner principal -->
      <div
        class="spinner-wrapper"
        :class="sizeClass"
      >
        <div class="spinner-ring">
          <div class="spinner-circle" />
          <div class="spinner-circle" />
          <div class="spinner-circle" />
        </div>
        <div class="spinner-pulse" />
      </div>

      <!-- Mensagem -->
      <div
        v-if="message"
        class="spinner-message"
        :class="sizeClass"
      >
        {{ message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineProps } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    message: {
        type: String,
        default: 'Carregando...'
    },
    size: {
        type: String,
        default: 'md', // xs, sm, md, lg
        validator: (value) => ['xs', 'sm', 'md', 'lg'].includes(value)
    },
    overlay: {
        type: Boolean,
        default: true
    },
    blur: {
        type: Boolean,
        default: true
    },
    zIndex: {
        type: Number,
        default: 10
    },
    position: {
        type: String,
        default: 'absolute', // absolute, fixed
        validator: (value) => ['absolute', 'fixed'].includes(value)
    },
    fullHeight: {
        type: Boolean,
        default: false
    }
});

const sizeClass = computed(() => `spinner-${props.size}`);

const overlayClasses = computed(() => ({
    'with-overlay': props.overlay,
    'with-blur': props.blur,
    'position-fixed': props.position === 'fixed',
    'full-height': props.fullHeight
}));

const overlayStyles = computed(() => ({
    zIndex: props.zIndex,
    position: props.position
}));
</script>

<style lang="scss" scoped>
$primary-color: #007bff;
$primary-light: rgba(0, 123, 255, 0.1);
$dark-primary: #0056b3;
$success-color: #28a745;
$warning-color: #ffc107;
$danger-color: #dc3545;

$dark-bg: #1a1d21;
$dark-text: #ced4da;
$dark-overlay: rgba(26, 29, 33, 0.8);

.regional-spinner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border-radius: inherit;
    
    &.position-fixed {
        position: fixed;
    }
    
    &.full-height {
        min-height: 200px;
    }
    
    &.with-overlay {
        background: rgba(255, 255, 255, 0.4);
        margin: -20px;
        border-radius: 10px;
        
        :root[data-layout-mode="dark"] & {
            background: $dark-overlay;
        }
    }
    
    &.with-blur {
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
    }
}

.regional-spinner-container {
    text-align: center;
    animation: fadeInScale 0.3s ease-out;
}

.spinner-wrapper {
    position: relative;
    margin: 0 auto;
    
    &.spinner-xs {
        width: 24px;
        height: 24px;
        margin-bottom: 0.5rem;
    }
    
    &.spinner-sm {
        width: 32px;
        height: 32px;
        margin-bottom: 0.75rem;
    }
    
    &.spinner-md {
        width: 40px;
        height: 40px;
        margin-bottom: 1rem;
    }
    
    &.spinner-lg {
        width: 48px;
        height: 48px;
        margin-bottom: 1.25rem;
    }
}

.spinner-ring {
    position: relative;
    width: 100%;
    height: 100%;
    
    .spinner-circle {
        position: absolute;
        width: 100%;
        height: 100%;
        border: 2px solid transparent;
        border-radius: 50%;
        animation: spinRotate 1.2s linear infinite;
        
        &:nth-child(1) {
            border-top-color: $primary-color;
            animation-delay: 0s;
            
            :root[data-layout-mode="dark"] & {
                border-top-color: $dark-text;
            }
        }
        
        &:nth-child(2) {
            border-right-color: rgba($primary-color, 0.6);
            animation-delay: 0.1s;
            animation-direction: reverse;
            
            :root[data-layout-mode="dark"] & {
                border-right-color: rgba($dark-text, 0.6);
            }
        }
        
        &:nth-child(3) {
            border-bottom-color: rgba($primary-color, 0.3);
            animation-delay: 0.2s;
            
            :root[data-layout-mode="dark"] & {
                border-bottom-color: rgba($dark-text, 0.3);
            }
        }
    }
}

.spinner-pulse {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    background: $primary-color;
    border-radius: 50%;
    animation: pulse 1.5s ease-in-out infinite;
    
    :root[data-layout-mode="dark"] & {
        background: $dark-text;
    }
}

.spinner-message {
    font-weight: 500;
    color: #6c757d;
    animation: fadeInUp 0.4s ease-out 0.1s both;
    
    &.spinner-xs {
        font-size: 0.75rem;
    }
    
    &.spinner-sm {
        font-size: 0.875rem;
    }
    
    &.spinner-md {
        font-size: 0.9rem;
    }
    
    &.spinner-lg {
        font-size: 1rem;
    }
    
    :root[data-layout-mode="dark"] & {
        color: $dark-text;
    }
}

@keyframes spinRotate {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }
    50% {
        transform: translate(-50%, -50%) scale(1.3);
        opacity: 0.4;
    }
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

// Variantes de tamanho para diferentes contextos
.spinner-xs {
    .spinner-circle {
        border-width: 1.5px;
    }
    .spinner-pulse {
        width: 4px;
        height: 4px;
    }
}

.spinner-sm {
    .spinner-circle {
        border-width: 2px;
    }
    .spinner-pulse {
        width: 6px;
        height: 6px;
    }
}

.spinner-md {
    .spinner-circle {
        border-width: 2px;
    }
    .spinner-pulse {
        width: 8px;
        height: 8px;
    }
}

.spinner-lg {
    .spinner-circle {
        border-width: 3px;
    }
    .spinner-pulse {
        width: 10px;
        height: 10px;
    }
}

// Responsividade
@media (max-width: 768px) {
    .regional-spinner-overlay {
        &.with-blur {
            backdrop-filter: blur(1px);
            -webkit-backdrop-filter: blur(1px);
        }
    }
    
    .spinner-wrapper {
        &.spinner-lg {
            width: 40px;
            height: 40px;
        }
        
        &.spinner-md {
            width: 32px;
            height: 32px;
        }
    }
    
    .spinner-message {
        &.spinner-lg {
            font-size: 0.9rem;
        }
        
        &.spinner-md {
            font-size: 0.875rem;
        }
    }
}
</style>