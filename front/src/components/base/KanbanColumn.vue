<template>
  <div class="kanban-column">
    <!-- Header da coluna -->
    <div class="kanban-column-header">
      <div class="d-flex align-items-center justify-content-between">
        <div class="flex-grow-1">
          <h6 class="column-title mb-0">
            <i
              :class="column.icon"
              class="me-1"
            />
            {{ column.label }}
            <BBadge
              :variant="column.variant"
              class="ms-2"
              pill
            >
              {{ column.items.length }}
            </BBadge>
          </h6>
        </div>
                
        <!-- Slot para conteúdo adicional no header -->
        <div
          v-if="$slots.headerExtra"
          class="flex-shrink-0"
        >
          <slot
            name="headerExtra"
            :column="column"
          />
        </div>
      </div>
    </div>

    <!-- Lista de itens -->
    <div class="kanban-column-content">
      <div class="kanban-items-wrapper">
        <draggable
          :list="column.items"
          class="kanban-drag-area"
          :group="{ name: dragGroup }"
          item-key="id"
          :disabled="disabled"
          @change="handleDragChange"
          @start="$emit('drag-start')"
          @end="$emit('drag-end')"
        >
          <template #item="{ element, index }">
            <div
              class="kanban-item"
              :data-id="element.id"
            >
              <slot
                :item="element"
                :index="index"
                :dragging="dragging"
              />
            </div>
          </template>
        </draggable>
      </div>

      <!-- Estado vazio -->
      <div
        v-if="column.items.length === 0"
        class="kanban-empty-state"
      >
        <div class="empty-state-content">
          <i
            :class="emptyIcon"
            class="empty-icon"
          />
          <p class="empty-text">
            {{ emptyMessage || `Nenhum item em ${column.label}` }}
          </p>
        </div>
      </div>

      <!-- Botão carregar mais -->
      <div
        v-if="hasMoreItems && column.items.length > 0"
        class="kanban-load-more"
      >
        <BButton
          variant="outline-primary"
          size="sm"
          class="w-100"
          :disabled="loadingMore"
          @click="$emit('load-more', column.value)"
        >
          <span v-if="loadingMore">
            <i class="ri-loader-2-line me-1 spin" />
            Carregando...
          </span>
          <span v-else>
            <i class="ri-add-line me-1" />
            Carregar Mais
          </span>
        </BButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import { VueDraggableNext as draggable } from 'vue-draggable-next';

// Props
const props = defineProps({
    column: {
        type: Object,
        required: true,
        validator: (column) => {
            return column.label && column.value && Array.isArray(column.items);
        }
    },
    dragging: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    dragGroup: {
        type: String,
        default: 'kanban'
    },
    hasMoreItems: {
        type: Boolean,
        default: false
    },
    loadingMore: {
        type: Boolean,
        default: false
    },
    emptyIcon: {
        type: String,
        default: 'ri-inbox-line'
    },
    emptyMessage: {
        type: String,
        default: null
    }
});

// Emits
const emit = defineEmits([
    'item-moved',
    'drag-start',
    'drag-end',
    'load-more'
]);

/**
 * Manipula mudanças no drag and drop
 */
function handleDragChange(event) {
    // Se um item foi adicionado a esta coluna
    if (event.added) {
        const item = event.added.element;
        const oldStatus = item.status || item.old_status;
        const newStatus = props.column.value;

        emit('item-moved', {
            itemId: item.id,
            fromStatus: oldStatus,
            toStatus: newStatus,
            item: item,
            event: event
        });
    }
}
</script>

<style scoped>
.kanban-column {
    flex: 0 0 auto;
    width: 300px;
    min-width: 280px;
    max-width: 320px;
    margin-right: 1rem;
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    display: flex;
    flex-direction: column;
    height: fit-content;
    max-height: calc(100vh - 200px);
}

html[data-layout-mode="dark"] .kanban-column {
    background-color: #2a3042;
}

.kanban-column-header {
    padding: 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 0.5rem 0.5rem 0 0;
    background-color: rgba(255, 255, 255, 0.5);
}

html[data-layout-mode="dark"] .kanban-column-header {
    background-color: rgba(255, 255, 255, 0.05);
    border-bottom-color: rgba(255, 255, 255, 0.05);
}

.column-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

html[data-layout-mode="dark"] .column-title {
    color: #e9ecef;
}

.kanban-column-content {
    flex: 1;
    padding: 1rem;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.kanban-items-wrapper {
    flex: 1;
    overflow-y: auto;
    margin: -0.5rem;
    padding: 0.5rem;
}

.kanban-drag-area {
    min-height: 100px;
}

.kanban-item {
    margin-bottom: 0.75rem;
    cursor: grab;
}

.kanban-item:active {
    cursor: grabbing;
}

.kanban-item:last-child {
    margin-bottom: 0;
}

/* Estado vazio */
.kanban-empty-state {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 150px;
}

.empty-state-content {
    text-align: center;
    padding: 2rem 1rem;
    border: 2px dashed #dee2e6;
    border-radius: 0.5rem;
    width: 100%;
}

html[data-layout-mode="dark"] .empty-state-content {
    border-color: #495057;
}

.empty-icon {
    font-size: 2rem;
    color: #adb5bd;
    margin-bottom: 0.5rem;
}

.empty-text {
    color: #6c757d;
    font-size: 0.875rem;
    margin: 0;
}

html[data-layout-mode="dark"] .empty-text {
    color: #adb5bd;
}

/* Botão carregar mais */
.kanban-load-more {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

html[data-layout-mode="dark"] .kanban-load-more {
    border-top-color: rgba(255, 255, 255, 0.05);
}

/* Scrollbar personalizada */
.kanban-items-wrapper::-webkit-scrollbar {
    width: 4px;
}

.kanban-items-wrapper::-webkit-scrollbar-track {
    background: transparent;
}

.kanban-items-wrapper::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.kanban-items-wrapper::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

html[data-layout-mode="dark"] .kanban-items-wrapper::-webkit-scrollbar-thumb {
    background: #495057;
}

html[data-layout-mode="dark"] .kanban-items-wrapper::-webkit-scrollbar-thumb:hover {
    background: #6c757d;
}

/* Estados de drag */
.kanban-item:global(.sortable-ghost) {
    opacity: 0.5;
    background: #f0f0f0;
    border: 2px dashed #007bff;
    border-radius: 0.375rem;
}

html[data-layout-mode="dark"] .kanban-item:global(.sortable-ghost) {
    background: #495057;
    border-color: #0d6efd;
}

.kanban-item:global(.sortable-drag) {
    transform: rotate(2deg);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    z-index: 1000;
}

/* Loading animation */
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Responsividade */
@media (max-width: 768px) {
    .kanban-column {
        width: 100%;
        min-width: 100%;
        max-width: 100%;
        margin-right: 0;
        margin-bottom: 1rem;
        max-height: 400px;
    }

    .kanban-items-wrapper {
        max-height: 250px;
    }
}
</style>