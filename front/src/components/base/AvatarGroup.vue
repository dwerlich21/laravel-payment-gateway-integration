<template>
  <div class="d-flex align-items-center justify-content-center flex-row">
    <div
      class="avatar-group"
      style="flex-wrap: nowrap"
    >
      <div
        v-for="responsible in data.responsible"
        :key="`responsible-${responsible.id}-${data.id}`"
        v-b-tooltip.hover="`${responsible.name || 'Nome indisponível'}`"
        class="avatar-group-item align-items-center"
      >
        <img
          v-if="responsible.img"
          :src="`${env.url}uploads/users/${responsible.img}`"
          alt=""
          class="rounded-circle avatar-xxs"
        >

        <div
          v-else
          class="avatar-circle d-flex align-items-center justify-content-center rounded-circle avatar-xxs"
          :class="userService.generateAvatarColor(responsible.name)"
        >
          {{ userService.generateNickname(responsible.name) }}
        </div>
      </div>
    </div>
  </div>
  <span v-if="data.responsible && data.responsible.length === 0" />
</template>
<script setup>
import {defineProps} from 'vue'
import UserService from "@/services/UserService.js";
import env from "@/env.js";

const userService = new UserService()

defineProps({
    data: {
        type: Object,
        required: true
    }
})

</script>