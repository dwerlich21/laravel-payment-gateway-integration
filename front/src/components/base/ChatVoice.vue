<template>
  <div
    class="audio-player"
    style="width: 300px"
  >
    <audio
      ref="audio"
      :src="audioUrl"
      @timeupdate="updateCurrentTime"
    />

    <BRow class="w-100">
      <BCol
        xxl="3"
        class="text-center my-auto"
      >
        <i
          class="bx pointer"
          style="font-size: 30px"
          :class="isPlaying ? 'bx-pause-circle' : 'bx-play-circle'"
          @click="togglePlayPause"
        />
      </BCol>

      <BCol
        xxl="9"
        class="position-relative"
      >
        <hr class="border-dashed mb-1">
        <div
          class="rounded-circle bg-primary position-absolute"
          :style="`width: 10px; height: 10px; top: 12px; left: ${left}%`"
        />
        <span class="text-muted fs-12 float-end">{{ formattedCurrentTime }} / {{
          formattedDuration
        }}</span>
      </BCol>
    </BRow>
  </div>
</template>

<script setup>
/* eslint-disable */
import {ref, onMounted, defineProps, computed} from 'vue';

const props = defineProps({
    audioUrl: {
        required: true
    }
});

const audio = ref(null);
const isPlaying = ref(false);
const currentTime = ref(0);
const duration = ref(0);

// Formata o tempo em mm:ss
const formatTime = (seconds) => {
    const minutes = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
};

const formattedCurrentTime = computed(() => formatTime(currentTime.value));
const formattedDuration = computed(() => formatTime(duration.value));
const left = computed(() => currentTime.value / duration.value * 90);

// Alterna entre play e pause
const togglePlayPause = () => {
    if (audio.value) {
        if (isPlaying.value) {
            audio.value.pause();
        } else {
            audio.value.play();
        }
    }
};

const updateCurrentTime = () => {
    if (audio.value) {
        currentTime.value = audio.value.currentTime;
    }
};

// Atualiza a duração quando o áudio está carregado
const updateDuration = () => {
    if (audio.value) {
        duration.value = audio.value.duration;
    }
};

onMounted(() => {
    if (audio.value) {
        audio.value.addEventListener('play', () => (isPlaying.value = true));
        audio.value.addEventListener('pause', () => (isPlaying.value = false));
        audio.value.addEventListener('loadedmetadata', updateDuration);
    }
});
</script>

<style scoped>
.audio-player {
    display: flex;
    align-items: center;
    gap: 10px;
}

.controls button {
    padding: 5px 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.controls button:hover {
    background-color: #0056b3;
}

.controls span {
    font-size: 14px;
}
</style>
