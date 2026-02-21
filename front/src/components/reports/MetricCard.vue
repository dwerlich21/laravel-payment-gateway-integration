<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    icon: {
        type: String,
        default: 'ri-bar-chart-line',
    },
    color: {
        type: String,
        default: 'primary',
    },
    subtitle: {
        type: String,
        default: '',
    },
    trend: {
        type: Number,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <b-card no-body class="metric-card h-100 card-equal-height">
        <b-card-body>
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <p class="text-uppercase fw-medium text-muted mb-0 fs-12">
                        {{ title }}
                    </p>
                    <h4 class="fs-22 fw-semibold mb-0 mt-2" v-if="!loading">
                        {{ value }}
                        <span
                            v-if="trend !== null"
                            class="badge fs-10 ms-1"
                            :class="trend >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'"
                        >
                            <i :class="trend >= 0 ? 'ri-arrow-up-line' : 'ri-arrow-down-line'"></i>
                            {{ Math.abs(trend) }}%
                        </span>
                    </h4>
                    <div v-else class="placeholder-glow mt-2">
                        <span class="placeholder col-6"></span>
                    </div>
                    <p class="text-muted mb-0 fs-11 mt-1" v-if="subtitle">
                        {{ subtitle }}
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <div
                        class="avatar-sm rounded"
                        :class="`bg-${color}-subtle`"
                    >
                        <span
                            class="avatar-title rounded fs-3"
                            :class="`text-${color}`"
                        >
                            <i :class="icon"></i>
                        </span>
                    </div>
                </div>
            </div>
        </b-card-body>
    </b-card>
</template>

<style scoped>
/* MetricCard - Estilos com hover effect */
.metric-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(var(--vz-dark-rgb), 0.1);
}

/* Classes utilitárias já definidas em _dashboard.scss */
</style>
