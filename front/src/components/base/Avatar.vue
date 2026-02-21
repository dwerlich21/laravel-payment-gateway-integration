<template>
  <div class="flex-shrink-0 me-3">
    <div class="avatar-sm">
      <img
        v-if="data.img"
        :src="`${env.url}uploads/users/${data.img}`"
        alt=""
        class="member-img img-fluid d-block rounded-circle"
      >
      <div
        v-else
        class="avatar-title rounded-circle bg-soft-primary text-primary"
      >
        {{ generateNickname(data.name) }}
      </div>
    </div>
  </div>
</template>
<script setup>
import {defineProps} from "vue";
import {generateNickname} from "@/composables/functions";
import env from "@/env";

defineProps({
    data: {
        type: Object,
        required: true
    }
});
</script>