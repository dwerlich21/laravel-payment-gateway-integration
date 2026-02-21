<!--<script>-->
<!--export default {-->
<!--    props: ['idProps']-->
<!--}-->

<!--</script>-->

<!--<template>-->
<!--    <div class="row">-->
<!--        <div class="col-12 text-center mt-3">-->
<!--            <span class="spinner spinner-border spinner-card flex-shrink-0" :id="this.idProps" role="status"-->
<!--                  style="height: 5rem; width: 5rem; position: absolute">-->
<!--                <span class="visually-hidden"></span>-->
<!--            </span>-->
<!--        </div>-->
<!--    </div>-->
<!--</template>-->


<template>
  <Teleport to="body">
    <div
      v-if="isVisible"
      class="global-spinner-overlay"
      :class="overlayClasses"
    >
      <div class="global-spinner-container">
        <!-- Spinner principal -->
        <div class="spinner-wrapper">
          <div class="spinner-ring">
            <div class="spinner-circle" />
            <div class="spinner-circle" />
            <div class="spinner-circle" />
            <div class="spinner-circle" />
          </div>

          <!-- Pulse effect -->
          <div class="spinner-pulse" />
        </div>

        <!-- Mensagem -->
        <div
          v-if="message"
          class="spinner-message"
        >
          {{ message }}
        </div>

        <!-- Submensagem opcional -->
        <div
          v-if="subMessage"
          class="spinner-sub-message"
        >
          {{ subMessage }}
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, onMounted, onUnmounted, defineProps, toRefs, watch } from 'vue';

// Props
const props = defineProps({
    show: {
        type: Boolean, // controla se o spinner está visível
        default: false
    },
    message: {
        type: String, // mensagem principal do carregamento
        default: 'Carregando...'
    },
    subMessage: {
        type: String, // submensagem opcional
        default: ''
    },
    overlay: {
        type: Boolean, // se deve mostrar overlay escuro
        default: true
    },
    zIndex: {
        type: Number, // z-index customizado
        default: 99
    }
});

// Estado reativo
const isVisible = computed(() => props.show);

// Verifica se sidebar está visível baseado nos data attributes
const sidebarVisible = computed(() => {
    if (typeof document === 'undefined') return false;

    const visibility = document.documentElement.getAttribute('data-sidebar-visibility');
    const layout = document.documentElement.getAttribute('data-layout');

    // Sidebar está oculta quando visibility='hidden' ou em layout horizontal
    return visibility !== 'hidden' && layout !== 'horizontal';
});

// Classes dinâmicas para o overlay
const overlayClasses = computed(() => ({
    'with-sidebar': sidebarVisible.value,
    'without-overlay': !props.overlay
}));

// Previne scroll quando spinner está ativo
onMounted(() => {
    if (props.show) {
        document.body.style.overflow = 'hidden';
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
});

// Watch para controlar o scroll
const { show } = toRefs(props);
watch(show, (newVal) => {
    if (newVal) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});
</script>
<style lang="scss" scoped>

$primary-color: #212529;
$primary-gradient: linear-gradient(45deg, #212529, #343a40);
$spinner-size: 60px;
$animation-duration: 1.5s;

$dark-bg: #1a1d21;
$dark-text: #ced4da;
$dark-overlay-bg: rgba(26, 29, 33, 0.5);
$dark-primary-gradient: linear-gradient(45deg, #ced4da, #878a99);

.global-spinner-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(3px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: v-bind(zIndex);
    transition: all 0.3s ease;

    &.with-sidebar {
        @media (min-width: 768px) {
            left: var(--sidebar-width, 250px);
        }
    }

    &.without-overlay {
        background: transparent;
        backdrop-filter: none;
    }

    :root[data-layout-mode="dark"] & {
        background: $dark-overlay-bg;
        backdrop-filter: blur(4px);

        &.without-overlay {
            background: transparent;
        }
    }
}

.global-spinner-container {
    text-align: center;
    animation: fadeInUp 0.3s ease-out;
}

.spinner-wrapper {
    position: relative;
    width: $spinner-size;
    height: $spinner-size;
    margin: 0 auto 1.5rem;
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
        animation: spinRotate $animation-duration linear infinite;

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
            border-bottom-color: rgba($primary-color, 0.4);
            animation-delay: 0.3s;

            :root[data-layout-mode="dark"] & {
                border-bottom-color: rgba($dark-text, 0.4);
            }
        }

        &:nth-child(4) {
            border-left-color: rgba($primary-color, 0.2);
            animation-delay: 0.45s;
            animation-direction: reverse;

            :root[data-layout-mode="dark"] & {
                border-left-color: rgba($dark-text, 0.2);
            }
        }
    }
}

.spinner-pulse {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    background: $primary-gradient;
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;

    :root[data-layout-mode="dark"] & {
        background: $dark-primary-gradient;
    }
}

.spinner-message {
    font-size: 1.1rem;
    font-weight: 600;
    color: $primary-color;
    margin-bottom: 0.5rem;
    animation: fadeInUp 0.5s ease-out 0.2s both;

    :root[data-layout-mode="dark"] & {
        color: $dark-text;
    }
}

.spinner-sub-message {
    font-size: 0.9rem;
    color: rgba($primary-color, 0.7);
    animation: fadeInUp 0.5s ease-out 0.4s both;

    :root[data-layout-mode="dark"] & {
        color: rgba($dark-text, 0.7);
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
        transform: translate(-50%, -50%) scale(1.5);
        opacity: 0.3;
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 767.98px) {
    .global-spinner-overlay {
        &.with-sidebar {
            left: 0;
        }
    }

    .spinner-wrapper {
        width: 50px;
        height: 50px;
    }

    .spinner-message {
        font-size: 1rem;
    }

    .spinner-sub-message {
        font-size: 0.85rem;
    }
}

.global-spinner-overlay {
    &:focus-within {
        outline: 2px solid $primary-color;
        outline-offset: -2px;

        :root[data-layout-mode="dark"] & {
            outline-color: $dark-text;
        }
    }
}

</style>