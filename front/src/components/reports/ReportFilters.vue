<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    showStatus: {
        type: Boolean,
        default: true,
    },
    showUser: {
        type: Boolean,
        default: false,
    },
    showDateRange: {
        type: Boolean,
        default: true,
    },
    users: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['filter']);

const filters = ref({
    start_date: '',
    end_date: '',
    status: '',
    responsible_id: '',
});

const statusOptions = [
    { value: '', text: 'Todos os status' },
    { value: 'pending', text: 'Pendente' },
    { value: 'in_progress', text: 'Em Andamento' },
    { value: 'completed', text: 'Concluído' },
    { value: 'cancelled', text: 'Cancelado' },
];

const applyFilters = () => {
    const cleanFilters = {};
    Object.keys(filters.value).forEach(key => {
        if (filters.value[key]) {
            cleanFilters[key] = filters.value[key];
        }
    });
    emit('filter', cleanFilters);
};

const clearFilters = () => {
    filters.value = {
        start_date: '',
        end_date: '',
        status: '',
        responsible_id: '',
    };
    emit('filter', {});
};

// Auto-apply on change (optional)
watch(filters, () => {
    // Uncomment for auto-apply
    // applyFilters();
}, { deep: true });
</script>

<template>
    <b-card no-body class="mb-3">
        <b-card-header class="border-0 bg-light">
            <div class="d-flex align-items-center">
                <i class="ri-filter-3-line me-2"></i>
                <span class="fw-semibold">Filtros</span>
            </div>
        </b-card-header>
        <b-card-body>
            <b-row class="g-3 row-equal-height">
                <b-col class="col-equal-height" v-if="showDateRange" sm="6" md="3">
                    <label class="form-label fs-12">Data Inicio</label>
                    <b-form-input
                        v-model="filters.start_date"
                        type="date"
                        size="sm"
                    />
                </b-col>

                <b-col class="col-equal-height" v-if="showDateRange" sm="6" md="3">
                    <label class="form-label fs-12">Data Fim</label>
                    <b-form-input
                        v-model="filters.end_date"
                        type="date"
                        size="sm"
                    />
                </b-col>

                <b-col class="col-equal-height" v-if="showStatus" sm="6" md="3">
                    <label class="form-label fs-12">Status</label>
                    <b-form-select
                        v-model="filters.status"
                        :options="statusOptions"
                        size="sm"
                    />
                </b-col>

                <b-col class="col-equal-height" v-if="showUser && users.length > 0" sm="6" md="3">
                    <label class="form-label fs-12">Responsavel</label>
                    <b-form-select
                        v-model="filters.responsible_id"
                        size="sm"
                    >
                        <option value="">Todos</option>
                        <option
                            v-for="user in users"
                            :key="user.id"
                            :value="user.id"
                        >
                            {{ user.name }}
                        </option>
                    </b-form-select>
                </b-col>

                <b-col cols="12" class="col-equal-height d-flex gap-2 justify-content-end">
                    <b-button
                        variant="soft-secondary"
                        size="sm"
                        @click="clearFilters"
                    >
                        <i class="ri-refresh-line me-1"></i>
                        Limpar
                    </b-button>
                    <b-button
                        variant="primary"
                        size="sm"
                        @click="applyFilters"
                    >
                        <i class="ri-search-line me-1"></i>
                        Filtrar
                    </b-button>
                </b-col>
            </b-row>
        </b-card-body>
    </b-card>
</template>
