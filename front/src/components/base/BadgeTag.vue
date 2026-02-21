<template>
  <div
    class="tags-container justify-content-center"
    :class="addClass"
  >
    <span
      v-for="tag in tags"
      :key="`badge-tag-${tag.id}`"
      class="badge bg-dark-subtle text-body badge-border align-items-center d-flex"
    >
      <i class="ri-price-tag-3-line me-1" />
      <span>
        {{ tag.name ?? tag }}
      </span>
    </span>
    <span v-if="tags && tags.length === 0" />
  </div>
</template>

<script setup>
import {defineProps} from 'vue';

defineProps({
    tags: {
        type: Array,
        required: true
    },
    addClass: {
        type: [String, Array],
        default: ''
    }
});
</script>

<style scoped>
.tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}
</style>