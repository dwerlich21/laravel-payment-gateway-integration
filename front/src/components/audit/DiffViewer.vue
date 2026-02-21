<template>
    <div class="diff-viewer">
        <div v-if="!hasChanges" class="text-center text-muted py-3">
            Sem alterações para exibir
        </div>

        <div v-else>
            <!-- Changed Fields (for updates) -->
            <div v-if="changedFields && Object.keys(changedFields).length > 0">
                <h6 class="mb-3">Campos Alterados</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%">Campo</th>
                                <th style="width: 37.5%">Valor Anterior</th>
                                <th style="width: 37.5%">Valor Novo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(change, field) in changedFields" :key="field">
                                <td class="fw-medium">{{ formatFieldName(field) }}</td>
                                <td class="text-danger bg-danger-subtle">
                                    <code class="text-danger">{{ formatValue(change.old) }}</code>
                                </td>
                                <td class="text-success bg-success-subtle">
                                    <code class="text-success">{{ formatValue(change.new) }}</code>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Old Values Only (for deletes) -->
            <div v-else-if="oldValues && !newValues">
                <h6 class="mb-3">Dados Removidos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 30%">Campo</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(value, field) in oldValues" :key="field">
                                <td class="fw-medium">{{ formatFieldName(field) }}</td>
                                <td class="text-danger bg-danger-subtle">
                                    <code class="text-danger">{{ formatValue(value) }}</code>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- New Values Only (for creates) -->
            <div v-else-if="newValues && !oldValues">
                <h6 class="mb-3">Dados Criados</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 30%">Campo</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(value, field) in newValues" :key="field">
                                <td class="fw-medium">{{ formatFieldName(field) }}</td>
                                <td class="text-success bg-success-subtle">
                                    <code class="text-success">{{ formatValue(value) }}</code>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Raw JSON View (for exports and other actions) -->
            <div v-else-if="newValues">
                <h6 class="mb-3">Detalhes</h6>
                <pre class="bg-light p-3 rounded mb-0"><code>{{ JSON.stringify(newValues, null, 2) }}</code></pre>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    oldValues: {
        type: Object,
        default: null,
    },
    newValues: {
        type: Object,
        default: null,
    },
    changedFields: {
        type: Object,
        default: null,
    },
});

const hasChanges = computed(() => {
    return (
        (props.changedFields && Object.keys(props.changedFields).length > 0) ||
        props.oldValues ||
        props.newValues
    );
});

const formatFieldName = (field) => {
    // Convert snake_case to Title Case
    return field
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const formatValue = (value) => {
    if (value === null || value === undefined) {
        return 'null';
    }
    if (typeof value === 'boolean') {
        return value ? 'Sim' : 'Nao';
    }
    if (typeof value === 'object') {
        return JSON.stringify(value, null, 2);
    }
    return String(value);
};
</script>

<style scoped>
.diff-viewer pre {
    max-height: 300px;
    overflow-y: auto;
    font-size: 0.8rem;
}

.diff-viewer code {
    font-size: 0.85rem;
    white-space: pre-wrap;
    word-break: break-word;
}

.diff-viewer .table td {
    vertical-align: top;
}
</style>
