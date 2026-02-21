<template>
  <div
    class="sortable-header-wrapper"
    @click="handleClick"
  >
    {{ label }}
    <span
      v-if="loading && isActive"
      class="sort-icon"
    >
      <BSpinner
        small
        variant="primary"
      />
    </span>
    <span
      v-else
      class="sort-icon"
    >
      <span v-if="!isActive">⇅</span>
      <span v-else-if="!sortDesc">▲</span>
      <span v-else>▼</span>
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  column: {
    type: String,
    required: true
  },
  sortBy: {
    type: String,
    default: ''
  },
  sortDesc: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['sort']);

const isActive = computed(() => props.sortBy === props.column);

const handleClick = () => {
  if (props.loading) return;
  emit('sort', props.column);
};
</script>

<style scoped>
.sortable-header-wrapper {
  cursor: pointer;
  user-select: none;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  width: 100%;
  padding: 0.25rem 0.5rem;
  margin: -0.25rem -0.5rem;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.sortable-header-wrapper:active {
  background-color: rgba(64, 81, 137, 0.15);
  transform: scale(0.98);
}

.sort-icon {
  flex-shrink: 0;
  color: #405189;
  font-size: 0.85rem;
  opacity: 0.4;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 16px;
}

.sortable-header-wrapper:hover .sort-icon {
  opacity: 1;
  transform: scale(1.1);
}

[data-layout-mode="dark"] .sort-icon {
  color: #ced4da;
}
</style>
