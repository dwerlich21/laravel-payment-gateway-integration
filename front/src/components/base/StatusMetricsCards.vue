<template>
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-muted mb-0">
          <i class="bx bx-time me-2" />
          Tempo Médio por Status
        </h5>
        <div class="text-muted small">
          <i class="bx bx-info-circle me-1" />
          Últimos 30 dias
        </div>
      </div>
    </div>

    <!-- Loading state -->
    <div
      v-if="loading"
      class="col-12"
    >
      <div class="d-flex justify-content-center p-4">
        <div
          class="spinner-border text-primary"
          role="status"
        >
          <span class="visually-hidden">Carregando...</span>
        </div>
      </div>
    </div>

    <!-- Metrics cards -->
    <div
      v-for="metric in metrics"
      v-else
      :key="metric.status"
      :class="cardColClass"
    >
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center">
          <!-- Status badge and icon -->
          <div class="mb-3">
            <div
              class="avatar-lg mx-auto rounded-circle d-flex align-items-center justify-content-center"
              :class="getStatusBgClass(metric.status)"
            >
              <i
                :class="getStatusIcon(metric.status)"
                class="fs-1 text-white"
              />
            </div>
          </div>

          <!-- Status label -->
          <h6 class="text-muted mb-2 text-uppercase font-weight-bold">
            {{ getStatusLabel(metric.status) }}
          </h6>

          <!-- Average time -->
          <h3 class="text-primary mb-1 font-weight-bold">
            {{ metric.average_formatted }}
          </h3>

          <!-- Additional info -->
          <div class="text-muted small">
            <div class="d-flex justify-content-between mt-2 align-items-center">
              <i class="bx bx-list-ul me-1" />
              <span>
                {{ metric.completed_transitions }} ocorrências
              </span>
            </div>
          </div>

          <!-- Status indicator -->
          <div class="mt-3">
            <Badge
              :variant="getStatusVariant(metric.status)"
              :icon="getStatusIcon(metric.status)"
              :label="getStatusLabel(metric.status)"
              :add-class="['px-3', 'py-2']"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div
      v-if="!loading && metrics.length === 0"
      class="col-12"
    >
      <div class="card border-0 bg-light">
        <div class="card-body text-center py-5">
          <i class="bx bx-time-five fs-1 text-muted mb-3" />
          <h6 class="text-muted">
            Nenhuma métrica de tempo disponível
          </h6>
          <p class="text-muted small mb-0">
            As métricas aparecerão quando houver dados de tempo por status disponíveis.
          </p>
        </div>
      </div>
    </div>

    <!-- Error state -->
    <div
      v-if="error"
      class="col-12"
    >
      <div
        class="alert alert-warning d-flex align-items-center"
        role="alert"
      >
        <i class="bx bx-error me-2" />
        <div>
          <strong>Erro ao carregar métricas:</strong> {{ error }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {ref, onMounted, computed, defineExpose, defineProps} from 'vue';
import http from '@/http';
import Badge from './Badge.vue';
import {getValueEnum} from '@/composables/enumsData';

// Props
const props = defineProps({
    entityType: {
        type: String,
        default: 'attendance'
    },
    period: {
        type: Number,
        default: 30 // dias
    },
    cardsPerRow: {
        type: Number,
        default: 6 // quantidade de cards por linha
    }
});

// Reactive data
const loading = ref(true);
const error = ref(null);
const metrics = ref([]);

// Computed
const cardColClass = computed(() => {
    const colSize = Math.floor(12 / props.cardsPerRow);
    return `col-xl-${colSize} col-lg-3 col-md-4 col-sm-6 mb-3`;
});

// Methods
const loadMetrics = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await http.get(`status-metrics/averages/${props.entityType}/`, {
            params: {
                period_days: props.period
            }
        });

        metrics.value = response.data.status_averages || [];

    } catch (err) {
        console.error('Erro ao carregar métricas de status:', err);
        error.value = err.response?.data?.message || err.message || 'Erro de conexão';
        metrics.value = [];
    } finally {
        loading.value = false;
    }
};

const getStatusLabel = (status) => {
    return getValueEnum(status, 'AttendanceStatus', 'label') || status;
};

const getStatusIcon = (status) => {
    return getValueEnum(status, 'AttendanceStatus', 'icon') || 'bx bx-circle';
};

const getStatusVariant = (status) => {
    return getValueEnum(status, 'AttendanceStatus', 'variant') || 'secondary';
};

const getStatusBgClass = (status) => {
    const variant = getStatusVariant(status);
    const classMap = {
        'primary': 'bg-primary',
        'secondary': 'bg-secondary',
        'success': 'bg-success',
        'warning': 'bg-warning',
        'danger': 'bg-danger',
        'info': 'bg-info'
    };
    return classMap[variant] || 'bg-secondary';
};

// Lifecycle
onMounted(() => {
    loadMetrics();
});

// Expose method for parent component
defineExpose({
    refreshMetrics: loadMetrics
});
</script>

<style scoped>
.avatar-lg {
    width: 4rem;
    height: 4rem;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.font-weight-bold {
    font-weight: 600;
}
</style>