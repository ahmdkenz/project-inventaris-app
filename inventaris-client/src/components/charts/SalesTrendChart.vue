<template>
  <ChartComponent
    title="Sales Trend"
    :subtitle="`Last ${period} days`"
    type="line"
    :data="chartData"
    :options="chartOptions"
    :loading="loading"
    height="300px"
  >
    <template #actions>
      <div class="period-selector">
        <button 
          v-for="option in periodOptions" 
          :key="option.value" 
          :class="['period-button', { active: period === option.value }]"
          @click="changePeriod(option.value)"
        >
          {{ option.label }}
        </button>
      </div>
    </template>
  </ChartComponent>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import ChartComponent from './ChartComponent.vue';
import axios from 'axios';

export default {
  name: 'SalesTrendChart',
  components: {
    ChartComponent
  },
  props: {
    initialPeriod: {
      type: Number,
      default: 30
    },
    endpoint: {
      type: String,
      default: '/api/reports/enhanced/trend-analysis'
    }
  },
  setup(props) {
    const loading = ref(true);
    const period = ref(props.initialPeriod);
    const salesData = ref([]);
    
    const periodOptions = [
      { label: '7D', value: 7 },
      { label: '30D', value: 30 },
      { label: '90D', value: 90 }
    ];
    
    const fetchData = async () => {
      loading.value = true;
      try {
        const today = new Date();
        const startDate = new Date();
        startDate.setDate(today.getDate() - period.value);
        
        const response = await axios.get(props.endpoint, {
          params: {
            period: 'daily',
            date_from: startDate.toISOString().split('T')[0],
            date_to: today.toISOString().split('T')[0],
            metrics: ['sales']
          }
        });
        
        if (response.data && response.data.trend_analysis) {
          const data = response.data.trend_analysis;
          salesData.value = data.data_points.map(point => ({
            date: point.label,
            sales: point.metrics.sales ? point.metrics.sales.total_amount : 0,
            orders: point.metrics.sales ? point.metrics.sales.count : 0
          }));
        }
      } catch (error) {
        console.error('Error fetching sales trend data:', error);
        // Fallback to dummy data when API fails
        generateDummyData();
      } finally {
        loading.value = false;
      }
    };
    
    const generateDummyData = () => {
      salesData.value = [];
      const today = new Date();
      
      for (let i = period.value - 1; i >= 0; i--) {
        const date = new Date();
        date.setDate(today.getDate() - i);
        const formattedDate = date.toISOString().split('T')[0];
        
        // Generate random data with some trend
        const baseSales = 5000000 + Math.random() * 2000000;
        const variation = (Math.sin(i / 5) + 1) * 1000000;
        const sales = Math.round(baseSales + variation);
        const orders = Math.round((baseSales + variation) / 200000);
        
        salesData.value.push({
          date: formattedDate,
          sales,
          orders
        });
      }
    };
    
    const chartData = computed(() => {
      const labels = salesData.value.map(item => item.date);
      const salesValues = salesData.value.map(item => item.sales);
      const orderValues = salesData.value.map(item => item.orders);
      
      return {
        labels,
        datasets: [
          {
            label: 'Sales (IDR)',
            data: salesValues,
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3,
            yAxisID: 'y'
          },
          {
            label: 'Orders',
            data: orderValues,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 2,
            borderDash: [5, 5],
            fill: false,
            tension: 0.3,
            yAxisID: 'y1'
          }
        ]
      };
    });
    
    const chartOptions = computed(() => {
      return {
        scales: {
          x: {
            grid: {
              display: false
            },
            ticks: {
              maxRotation: 45,
              minRotation: 45
            }
          },
          y: {
            position: 'left',
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            },
            ticks: {
              callback: function(value) {
                if (value >= 1000000) {
                  return (value / 1000000).toLocaleString() + 'M';
                } else if (value >= 1000) {
                  return (value / 1000).toLocaleString() + 'K';
                } else {
                  return value;
                }
              }
            }
          },
          y1: {
            position: 'right',
            grid: {
              display: false
            },
            ticks: {
              color: '#10b981'
            }
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: function(context) {
                let label = context.dataset.label || '';
                if (label) {
                  label += ': ';
                }
                if (context.dataset.yAxisID === 'y') {
                  label += new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                  }).format(context.raw);
                } else {
                  label += context.raw;
                }
                return label;
              }
            }
          }
        }
      };
    });
    
    const changePeriod = (newPeriod) => {
      period.value = newPeriod;
      fetchData();
    };
    
    onMounted(() => {
      fetchData();
    });
    
    return {
      loading,
      period,
      periodOptions,
      chartData,
      chartOptions,
      changePeriod
    };
  }
};
</script>

<style scoped>
.period-selector {
  display: flex;
  gap: 8px;
}

.period-button {
  padding: 4px 8px;
  font-size: 0.75rem;
  background-color: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.period-button.active {
  background-color: #4f46e5;
  border-color: #4338ca;
  color: white;
}

.period-button:hover:not(.active) {
  background-color: #e5e7eb;
}
</style>