<template>
    <b-card no-body class="card-equal-height">
        <b-card-header class="align-items-center d-flex border-0">
            <h6 class="card-title mb-0 flex-grow-1">Evolução Mensal</h6>
        </b-card-header>
        <b-card-body class="pt-0 px-0">
            <div v-if="loading" class="d-flex justify-content-center align-items-center" style="height: 350px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
            <apexchart
                v-else-if="chartOptions.series.length > 0"
                type="area"
                height="350"
                :options="chartOptions"
                :series="chartOptions.series"
            />
            <div v-else class="d-flex flex-column justify-content-center align-items-center text-muted" style="height: 350px;">
                <i class="ri-line-chart-line fs-1 mb-2"></i>
                <span>Sem dados para exibir</span>
            </div>
        </b-card-body>
    </b-card>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue';
import http from '@/http';
import { getChartColors } from '@/utils/themeColors';

const loading = ref(true);

// Get theme colors from CSS variables
const chartColors = getChartColors();

const chartOptions = reactive({
    series: [],
    chart: {
        type: 'area',
        height: 350,
        toolbar: { show: false },
        zoom: { enabled: false },
    },
    dataLabels: { enabled: false },
    stroke: {
        curve: 'smooth',
        width: 2
    },
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
    colors: chartColors.areaColors,
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: chartColors.gradientFrom,
            opacityTo: chartColors.gradientTo,
            stops: [0, 90, 100]
        },
    },
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
        padding: {
            top: 0,
            right: 0,
            bottom: 0,
            left: 10
        }
    },
    tooltip: {
        shared: true,
        intersect: false,
        y: {
            formatter: function (val) {
                return val + ' metas';
            }
        }
    },
    markers: {
        size: 0,
        hover: {
            size: 5,
            sizeOffset: 3
        }
    }
});

const loadData = async () => {
    loading.value = true;
    try {
        const response = await http.get('reports/goals/timeline');
        const data = response.data.data || {};
        if (data.labels && data.labels.length > 0) {
            chartOptions.xaxis.categories = data.labels;
            chartOptions.series = data.datasets;
        }
    } catch (error) {
        console.error('Error loading timeline chart:', error);
    }
    loading.value = false;
};

onMounted(() => {
    loadData();
});
</script>