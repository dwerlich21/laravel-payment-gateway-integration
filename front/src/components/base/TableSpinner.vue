<template>
  <div 
    v-if="show" 
    class="table-spinner-overlay"
    :class="overlayClasses"
    :style="overlayStyles"
  >
    <div class="table-spinner-container">
      <!-- Spinner principal -->
      <div class="spinner-wrapper">
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
        default: 'Carregando dados...'
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
    }
});

const overlayClasses = computed(() => ({
    'with-overlay': props.overlay,
    'with-blur': props.blur
}));

const overlayStyles = computed(() => ({
    zIndex: props.zIndex
}));
</script>

<style lang="scss" scoped>
$primary-color: #007bff;
$dark-text: #ced4da;
$dark-overlay: rgba(26, 29, 33, 0.85);

.table-spinner-overlay {
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
    
    &.with-overlay {
        background: rgba(255, 255, 255, 0.95);
        
        :root[data-layout-mode="dark"] & {
            background: $dark-overlay;
        }
    }
    
    &.with-blur {
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
    }
}

.table-spinner-container {
    text-align: center;
    animation: fadeInScale 0.3s ease-out;
    padding: 2rem;
}

.spinner-wrapper {
    position: relative;
    width: 48px;
    height: 48px;
    margin: 0 auto 1rem;
}

.spinner-ring {
    position: relative;
    width: 100%;
    height: 100%;
    
    .spinner-circle {
        position: absolute;
        width: 100%;
        height: 100%;
        border: 3px solid transparent;
        border-radius: 50%;
        animation: spinRotate 1.4s linear infinite;
        
        &:nth-child(1) {
            border-top-color: $primary-color;
            animation-delay: 0s;
            
            :root[data-layout-mode="dark"] & {
                border-top-color: $dark-text;
            }
        }
        
        &:nth-child(2) {
            border-right-color: rgba($primary-color, 0.6);
            animation-delay: 0.15s;
            animation-direction: reverse;
            
            :root[data-layout-mode="dark"] & {
                border-right-color: rgba($dark-text, 0.6);
            }
        }
        
        &:nth-child(3) {
            border-bottom-color: rgba($primary-color, 0.3);
            animation-delay: 0.3s;
            
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
    width: 12px;
    height: 12px;
    background: $primary-color;
    border-radius: 50%;
    animation: pulse 1.8s ease-in-out infinite;
    
    :root[data-layout-mode="dark"] & {
        background: $dark-text;
    }
}

.spinner-message {
    font-size: 1rem;
    font-weight: 500;
    color: #6c757d;
    animation: fadeInUp 0.4s ease-out 0.2s both;
    
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
        transform: translate(-50%, -50%) scale(1.4);
        opacity: 0.3;
    }
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

// Responsividade
@media (max-width: 768px) {
    .table-spinner-container {
        padding: 1.5rem;
    }
    
    .spinner-wrapper {
        width: 40px;
        height: 40px;
    }
    
    .spinner-message {
        font-size: 0.9rem;
    }
    
    .spinner-pulse {
        width: 10px;
        height: 10px;
    }
}
</style>