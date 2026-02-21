<template>
  <Cropper
    ref="cropper"
    class="cropper"
    :src="props.img"
    :stencil-props="{
      aspectRatio: props.proportion,
      movable: true,
      resizable: true,
    }"
  />
  <BRow>
    <BCol class="text-center">
      <button
        type="button"
        class="btn btn-soft-danger mt-3 mb-5"
        @click="cancelCrop"
      >
        Cancelar
      </button>
      <button
        type="button"
        class="btn btn-soft-info mt-3 ms-2 mb-5"
        @click="cropImage"
      >
        Recortar
      </button>
    </BCol>
  </BRow>
</template>

<script setup>
import {Cropper} from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css';
import {ref, defineProps, defineEmits} from 'vue';

const cropper = ref(null);

const props = defineProps({
    img: {
        required: true
    },
    proportion: {
        required: true,
        type: Number
    }
});

const emit = defineEmits(['set-img', 'reset-logo']);

function cropImage() {
    if (cropper.value) {
        const {canvas} = cropper.value.getResult();
        if (canvas) {
            canvas.toBlob((blob) => {
                if (blob) {
                    emit('set-img', blob);
                }
            });
        }
    }
}

function cancelCrop() {
    emit('reset-logo');
}
</script>

<style scoped>

.cropper {
    height: 400px;
    width: 400px;
    background: #DDD;
    margin: auto;
}
</style>
