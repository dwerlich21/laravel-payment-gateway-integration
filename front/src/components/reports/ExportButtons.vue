<script setup>
import { ref } from 'vue';

const props = defineProps({
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['export-pdf', 'export-excel']);

const loadingPdf = ref(false);
const loadingExcel = ref(false);

const exportPdf = async () => {
    loadingPdf.value = true;
    emit('export-pdf');
    setTimeout(() => {
        loadingPdf.value = false;
    }, 2000);
};

const exportExcel = async () => {
    loadingExcel.value = true;
    emit('export-excel');
    setTimeout(() => {
        loadingExcel.value = false;
    }, 2000);
};
</script>

<template>
    <div class="d-flex gap-2">
        <b-button
            variant="danger"
            size="sm"
            :disabled="disabled || loadingPdf"
            @click="exportPdf"
        >
            <span v-if="loadingPdf">
                <span class="spinner-border spinner-border-sm me-1"></span>
                Gerando...
            </span>
            <span v-else>
                <i class="ri-file-pdf-line me-1"></i>
                Exportar PDF
            </span>
        </b-button>

        <b-button
            variant="success"
            size="sm"
            :disabled="disabled || loadingExcel"
            @click="exportExcel"
        >
            <span v-if="loadingExcel">
                <span class="spinner-border spinner-border-sm me-1"></span>
                Gerando...
            </span>
            <span v-else>
                <i class="ri-file-excel-line me-1"></i>
                Exportar Excel
            </span>
        </b-button>
    </div>
</template>
