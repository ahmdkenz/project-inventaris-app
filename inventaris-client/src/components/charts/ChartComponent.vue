<template>
  <div class="chart-container" :style="{ height: height }">
    <div class="chart-header" v-if="title || subtitle || $slots.header">
      <div class="chart-titles">
        <h3 class="chart-title" v-if="title">{{ title }}</h3>
        <p class="chart-subtitle" v-if="subtitle">{{ subtitle }}</p>
      </div>
      <div class="chart-actions" v-if="$slots.actions">
        <slot name="actions"></slot>
      </div>
    </div>
    <div class="chart-wrapper" ref="chartContainer"></div>
    <div class="chart-footer" v-if="$slots.footer">
      <slot name="footer"></slot>
    </div>
    <div v-if="loading" class="chart-loading">
      <div class="loading-spinner"></div>
      <span>Loading chart data...</span>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import Chart from 'chart.js/auto';

export default {
  name: 'ChartComponent',
  props: {
    title: {
      type: String,
      default: ''
    },
    subtitle: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: 'line',
      validator: (value) => ['line', 'bar', 'pie', 'doughnut', 'radar', 'polarArea'].includes(value)
    },
    data: {
      type: Object,
      required: true
    },
    options: {
      type: Object,
      default: () => ({})
    },
    height: {
      type: String,
      default: '300px'
    },
    loading: {
      type: Boolean,
      default: false
    },
    responsive: {
      type: Boolean,
      default: true
    }
  },
  setup(props) {
    const chartContainer = ref(null);
    let chartInstance = null;

    const createChart = () => {
      if (chartInstance) {
        chartInstance.destroy();
      }

      const ctx = chartContainer.value.getContext('2d');
      
      // Default options with some sensible defaults
      const defaultOptions = {
        responsive: props.responsive,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          },
          tooltip: {
            enabled: true,
            mode: 'index',
            intersect: false
          }
        }
      };

      // Merge default options with provided options
      const chartOptions = {
        ...defaultOptions,
        ...props.options
      };

      chartInstance = new Chart(ctx, {
        type: props.type,
        data: props.data,
        options: chartOptions
      });
    };

    onMounted(() => {
      if (!props.loading) {
        createChart();
      }
    });

    watch(() => props.data, (newData) => {
      if (chartInstance) {
        chartInstance.data = newData;
        chartInstance.update();
      } else if (!props.loading) {
        createChart();
      }
    }, { deep: true });

    watch(() => props.loading, (isLoading) => {
      if (!isLoading && chartContainer.value) {
        createChart();
      }
    });

    watch(() => props.type, (newType) => {
      if (chartInstance) {
        chartInstance.destroy();
      }
      if (!props.loading) {
        createChart();
      }
    });

    watch(() => props.options, (newOptions) => {
      if (chartInstance) {
        chartInstance.options = {
          ...chartInstance.options,
          ...newOptions
        };
        chartInstance.update();
      }
    }, { deep: true });

    onBeforeUnmount(() => {
      if (chartInstance) {
        chartInstance.destroy();
      }
    });

    return {
      chartContainer
    };
  }
};
</script>

<style scoped>
.chart-container {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  padding: 20px;
  position: relative;
  display: flex;
  flex-direction: column;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.chart-title {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
  margin: 0;
}

.chart-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 4px 0 0 0;
}

.chart-wrapper {
  flex: 1;
  position: relative;
  width: 100%;
}

.chart-footer {
  margin-top: 16px;
  font-size: 0.875rem;
  color: #6b7280;
}

.chart-loading {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.8);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 12px;
  font-size: 0.875rem;
  color: #6b7280;
  border-radius: 12px;
  z-index: 10;
}

.loading-spinner {
  width: 30px;
  height: 30px;
  border: 3px solid rgba(79, 70, 229, 0.2);
  border-radius: 50%;
  border-top-color: #4f46e5;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>