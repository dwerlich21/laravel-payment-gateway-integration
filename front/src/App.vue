<template>
    <router-view/>

    <Notivue v-slot="item">
        <Notification
            :theme="lightTheme"
            :item="item"
        />
    </Notivue>

    <!-- Spinner global legacy (mantido para compatibilidade) -->
    <div class="row">
        <div class="col-12 text-center">
            <span
                id="spinner"
                class="spinner spinner-border spinner-card flex-shrink-0"
                role="status"
                style="height: 5rem; width: 5rem; display: none"
            >
                <span class="visually-hidden"/>
            </span>
        </div>
    </div>
</template>
<script setup>
import {Notivue, Notification, lightTheme} from 'notivue';
import {onMounted} from 'vue';
import {useAuthStore} from "@/stores/index.js";

const authStore = useAuthStore();

onMounted(() => {
    authStore.getEnums();
});
</script>
