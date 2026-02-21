<template>
    <b-card no-body class="card-equal-height card-height-100">
        <b-card-header class="align-items-center d-flex border-0">
            <h6 class="card-title mb-0 flex-grow-1">Metas por Usuário</h6>
        </b-card-header>
        <b-card-body class="pt-0">
            <div v-if="loading" class="d-flex justify-content-center align-items-center" style="height: 300px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
            <apexchart
                v-else-if="chartOptions.series[0].data.length > 0"
                type="bar"
                height="300"
                :options="chartOptions"
                :series="chartOptions.series"
            />
            <div v-else class="d-flex flex-column justify-content-center align-items-center text-muted" style="height: 300px;">
                <i class="ri-bar-chart-2-line fs-1 mb-2"></i>
                <span>Sem dados para exibir</span>
            </div>
        </b-card-body>
    </b-card>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import http from '@/http';
import { getThemeColors, getChartColors } from '@/utils/themeColors';

const loading = ref(true);

// Get theme colors from CSS variables
const themeColors = getThemeColors();
const chartColors = getChartColors();

const chartOptions = reactive({
    series: [
        { name: 'Total', data: [] },
        { name: 'Concluidas', data: [] }
    ],
    chart: {
        type: 'bar',
        height: 300,
        toolbar: { show: false },
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '45%',
            borderRadius: 4,
            borderRadiusApplication: 'end',
        },
    },
    dataLabels: { enabled: false },
    xaxis: {
        categories: [],
        labels: {
            style: {
                colors: chartColors.axisLabelColor,
                fontSize: '12px',
            }
        },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            style: {
                colors: chartColors.axisLabelColor,
                fontSize: '12px',
            }
        }
    },
    colors: chartColors.barColors,
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        labels: {
            colors: chartColors.textColor,
        },
        markers: {
            width: 8,
            height: 8,
            radius: 8,
        }
    },
    grid: {
        borderColor: chartColors.gridColor,
        strokeDashArray: 3,
    },
    tooltip: {
        shared: true,
        intersect: false,
    }
});

const loadData = async () => {
    loading.value = true;
    try {
        const response = await http.get('reports/goals/by-user');
        const data = response.data.data || [];
        if (data.length > 0) {
            chartOptions.xaxis.categories = data.map(u => u.user_name.split(' ')[0]);
            chartOptions.series[0].data = data.map(u => u.total);
            chartOptions.series[1].data = data.map(u => u.completed);
        }
    } catch (error) {
        console.error('Error loading user chart:', error);
    }
    loading.value = false;
};

onMounted(() => {
    loadData();
});
</script>
