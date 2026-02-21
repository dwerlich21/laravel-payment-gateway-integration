<template>
  <div class="kanban-board">
    <!-- Loading state -->
    <div
      v-if="loading"
      class="kanban-loading"
    >
      <div class="text-center py-5">
        <div
          class="spinner-border text-primary"
          role="status"
        >
          <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2 text-muted">
          Carregando dados...
        </p>
      </div>
    </div>

    <!-- Kanban columns -->
    <div
      v-else
      class="kanban-columns-container"
    >
      <div class="kanban-columns">
        <KanbanColumn
          v-for="column in columns"
          :key="column.value"
          :column="column"
          :dragging="dragging"
          :drag-group="dragGroup"
          :disabled="disabled"
          :has-more-items="hasMoreItems(column.value)"
          :loading-more="loadingMore[column.value]"
          :empty-icon="emptyIcon"
          :empty-message="getEmptyMessage(column)"
          @item-moved="handleItemMoved"
          @drag-start="dragging = true"
          @drag-end="dragging = false"
          @load-more="handleLoadMore"
        >
          <!-- Slot para header extra da coluna -->
          <template #headerExtra="{ column }">
            <slot
              name="columnHeader"
              :column="column"
            />
          </template>

          <!-- Slot para itens da coluna -->
          <template #default="{ item, index, dragging }">
            <slot
              name="item"
              :item="item"
              :index="index"
              :dragging="dragging"
              :column="column"
            />
          </template>
        </KanbanColumn>
      </div>
    </div>

    <!-- Empty state geral -->
    <div
      v-if="!loading && isEmpty"
      class="kanban-empty-board"
    >
      <div class="empty-board-content">
        <i
          class="ri-kanban-view text-muted"
          style="font-size: 4rem;"
        />
        <h4 class="mt-3 mb-2">
          {{ emptyBoardTitle || 'Nenhum item encontrado' }}
        </h4>
        <p class="text-muted mb-3">
          {{ emptyBoardMessage || 'Adicione alguns itens para começar a usar o kanban.' }}
        </p>
        <slot name="emptyActions" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from 'vue';
import KanbanColumn from './KanbanColumn.vue';

// Props
const props = defineProps({
    columns: {
        type: Array,
        required: true,
        validator: (columns) => {
            return columns.every(col => col.label && col.value && Array.isArray(col.items));
        }
    },
    loading: {
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
    emptyIcon: {
        type: String,
        default: 'ri-inbox-line'
    },
    emptyBoardTitle: {
        type: String,
        default: null
    },
    emptyBoardMessage: {
        type: String,
        default: null
    },
    pagination: {
        type: Object,
        default: () => ({})
    }
});

// Emits
const emit = defineEmits([
    'item-moved',
    'load-more'
]);

// Estado reativo
const dragging = ref(false);
const loadingMore = ref({});

// Computados
const isEmpty = computed(() => {
    return props.columns.every(column => column.items.length === 0);
});

// Métodos
/**
 * Manipula movimentação de itens entre colunas
 */
async function handleItemMoved(event) {
    try {
        emit('item-moved', event);
    } catch (error) {
        console.error('Erro ao mover item:', error);
    }
}

/**
 * Manipula carregamento de mais itens
 */
async function handleLoadMore(columnValue) {
    try {
        loadingMore.value[columnValue] = true;
        emit('load-more', columnValue);
    } finally {
        loadingMore.value[columnValue] = false;
    }
}

/**
 * Verifica se coluna tem mais itens para carregar
 */
function hasMoreItems(columnValue) {
    if (!props.pagination[columnValue]) return false;
    const pagination = props.pagination[columnValue];
    return pagination.total > pagination.partial;
}

/**
 * Retorna mensagem vazia personalizada para coluna
 */
function getEmptyMessage(column) {
    return column.emptyMessage || `Nenhum item em ${column.label}`;
}
</script>

<style scoped>
.kanban-board {
    width: 100%;
    height: 100%;
}

.kanban-loading {
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.kanban-columns-container {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 1rem 0;
}

.kanban-columns {
    display: flex;
    align-items: flex-start;
    min-width: max-content;
    gap: 1rem;
    padding: 0 1rem;
}

/* Empty board state */
.kanban-empty-board {
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.empty-board-content {
    text-align: center;
    max-width: 400px;
}

.empty-board-content i {
    opacity: 0.6;
}

.empty-board-content h4 {
    color: #495057;
}

html[data-layout-mode="dark"] .empty-board-content h4 {
    color: #e9ecef;
}

/* Scrollbar personalizada para container horizontal */
.kanban-columns-container::-webkit-scrollbar {
    height: 8px;
}

.kanban-columns-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.kanban-columns-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.kanban-columns-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

html[data-layout-mode="dark"] .kanban-columns-container::-webkit-scrollbar-track {
    background: #343a40;
}

html[data-layout-mode="dark"] .kanban-columns-container::-webkit-scrollbar-thumb {
    background: #495057;
}

html[data-layout-mode="dark"] .kanban-columns-container::-webkit-scrollbar-thumb:hover {
    background: #6c757d;
}

/* Responsividade */
@media (max-width: 768px) {
    .kanban-columns {
        flex-direction: column;
        align-items: stretch;
        min-width: 100%;
        gap: 0.75rem;
    }

    .kanban-columns-container {
        overflow-x: visible;
        padding: 0.5rem 0;
    }
}

/* Estados de drag global */
.kanban-board.dragging {
    user-select: none;
}

.kanban-board.dragging * {
    cursor: grabbing !important;
}
</style>