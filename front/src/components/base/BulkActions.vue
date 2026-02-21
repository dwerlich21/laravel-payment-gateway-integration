<template>
    <Transition name="slide-up">
        <div
            v-if="selectedCount > 0"
            class="bulk-actions-bar"
        >
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary me-2">{{ selectedCount }}</span>
                    <span class="text-muted">
                        {{ selectedCount === 1 ? 'item selecionado' : 'itens selecionados' }}
                    </span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <!-- Slot para ações customizadas -->
                    <slot name="actions"></slot>

                    <!-- Ações padrao -->
                    <b-dropdown
                        v-if="showStatusActions"
                        variant="soft-info"
                        size="sm"
                        text="Alterar Status"
                    >
                        <b-dropdown-item
                            v-for="status in statusOptions"
                            :key="status.value"
                            @click="$emit('change-status', status.value)"
                        >
                            <i :class="[status.icon, 'me-2']"></i>
                            {{ status.label }}
                        </b-dropdown-item>
                    </b-dropdown>

                    <b-dropdown
                        v-if="showActiveActions"
                        variant="soft-warning"
                        size="sm"
                        text="Ativar/Desativar"
                    >
                        <b-dropdown-item @click="$emit('change-active', true)">
                            <i class="ri-checkbox-circle-line me-2 text-success"></i>
                            Ativar selecionados
                        </b-dropdown-item>
                        <b-dropdown-item @click="$emit('change-active', false)">
                            <i class="ri-close-circle-line me-2 text-danger"></i>
                            Desativar selecionados
                        </b-dropdown-item>
                    </b-dropdown>

                    <b-button
                        v-if="showDeleteAction"
                        variant="soft-danger"
                        size="sm"
                        @click="confirmDelete"
                    >
                        <i class="ri-delete-bin-line me-1"></i>
                        Excluir
                    </b-button>

                    <b-button
                        variant="light"
                        size="sm"
                        @click="$emit('clear-selection')"
                    >
                        <i class="ri-close-line"></i>
                    </b-button>
                </div>
            </div>
        </div>
    </Transition>

</template>

<script setup>
import {showAlertConfirm} from "@/composables/functions";

const props = defineProps({
    selectedCount: {
        type: Number,
        default: 0,
    },
    showDeleteAction: {
        type: Boolean,
        default: true,
    },
    showActiveActions: {
        type: Boolean,
        default: true,
    },
    showStatusActions: {
        type: Boolean,
        default: false,
    },
    statusOptions: {
        type: Array,
        default: () => [
            { value: 'pending', label: 'Pendente', icon: 'ri-time-line text-warning' },
            { value: 'in_progress', label: 'Em Andamento', icon: 'ri-loader-4-line text-info' },
            { value: 'completed', label: 'Concluído', icon: 'ri-checkbox-circle-line text-success' },
            { value: 'cancelled', label: 'Cancelado', icon: 'ri-close-circle-line text-danger' },
        ],
    },
});

const emit = defineEmits(['delete', 'change-active', 'change-status', 'clear-selection']);

const confirmDelete = async () => {
    const itemText = props.selectedCount === 1 ? 'registro' : 'registros';
    const result = await showAlertConfirm(
        `${props.selectedCount} ${itemText} serão removidos e não poderão mais ser recuperados.`
    );

    if (result) {
        emit('delete');
    }
};
</script>

<style scoped>
.bulk-actions-bar {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1050;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    width: calc(100% - 2rem);
    max-width: 900px;
}

.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
    opacity: 0;
    transform: translate(-50%, 20px);
}

[data-layout-mode="dark"] .bulk-actions-bar {
    background: linear-gradient(135deg, #2a2f34 0%, #343a40 100%);
    border-color: #495057;
}
</style>
