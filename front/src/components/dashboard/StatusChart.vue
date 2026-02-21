<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import http from '@/http';
import { getThemeColors, getChartColors } from '@/utils/themeColors';

const loading = ref(true);

// Get theme colors from CSS variables
const themeColors = getThemeColors();
const chartColors = getChartColors();

const chartOptions = reactive({
    series: [],
    labels: [],
    colors: chartColors.statusColors,
    chart: {
        type: 'donut',
        height: 300,
    },
    legend: {
        position: 'bottom',
        labels: {
            colors: chartColors.textColor,
        },
    },
    plotOptions: {
        pie: {
            donut: {
                size: '70%',
                labels: {
                    show: true,
                    name: {
                        show: true,
                        fontSize: '14px',
                        fontWeight: 600,
                        color: themeColors.gray700,
                    },
                    value: {
                        show: true,
                        fontSize: '20px',
                        fontWeight: 700,
                        color: themeColors.gray700,
                    },
                    total: {
                        show: true,
                        label: 'Total',
                        fontSize: '14px',
                        fontWeight: 600,
                        color: themeColors.gray600,
                        formatter: function (w) {
                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                        },
                    },
                },
            },
        },
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        width: 0
    },
    responsive: [{
        breakpoint: 480,
        options: {
            chart: { width: 200 },
            legend: { position: 'bottom' },
        },
    }],
});

const loadData = async () => {
    loading.value = true;
    try {
        const response = await http.get('reports/goals/by-status');
        const data = response.data.data || [];
        if (data.length > 0) {
            chartOptions.series = data.map(s => s.count);
            chartOptions.labels = data.map(s => s.label);
        }
    } catch (error) {
        console.error('Error loading status chart:', error);
    }
    loading.value = false;
};

const total = computed(() => chartOptions.series.reduce((a, b) => a + b, 0));

onMounted(() => {
    loadData();
});
</script>

<template>
    <b-card no-body class="card-equal-height card-height-100">
        <b-card-header class="align-items-center d-flex border-0">
            <h6 class="card-title mb-0 flex-grow-1">Metas por Status</h6>
            <div class="flex-shrink-0">
                <span class="badge bg-primary-subtle text-primary fs-12">
                    {{ total }} total
                </span>
            </div>
        </b-card-header>
        <b-card-body class="pt-0">
            <div v-if="loading" class="d-flex justify-content-center align-items-center" style="height: 300px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
            <apexchart
                v-else-if="chartOptions.series.length > 0"
                type="donut"
                height="300"
                :options="chartOptions"
                :series="chartOptions.series"
            />
            <div v-else class="d-flex flex-column justify-content-center align-items-center text-muted" style="height: 300px;">
                <i class="ri-pie-chart-line fs-1 mb-2"></i>
                <span>Sem dados para exibir</span>
            </div>
        </b-card-body>
    </b-card>
</template>

<style scoped>
/* StatusChart - Estilos específicos */
/* Classes utilitárias movidas para _dashboard.scss */
</style>
