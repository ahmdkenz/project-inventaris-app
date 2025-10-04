<template>
  <ChartComponent
    title="Top Selling Products"
    :subtitle="subtitle || 'Last 30 days'"
    type="bar"
    :data="chartData"
    :options="chartOptions"
    :loading="loading"
    height="300px"
  >
    <template #actions>
      <div class="view-selector">
        <button 
          v-for="option in viewOptions" 
          :key="option.value" 
          :class="['view-button', { active: view === option.value }]"
          @click="changeView(option.value)"
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
  name: 'TopProductsChart',
  components: {
    ChartComponent
  },
  props: {
    limit: {
      type: Number,
      default: 5
    },
    endpoint: {
      type: String,
      default: '/api/reports/enhanced/product-performance'
    },
    subtitle: {
      type: String,
      default: ''
    }
  },
  setup(props) {
    const loading = ref(true);
    const products = ref([]);
    const view = ref('revenue'); // 'revenue', 'quantity'
    
    const viewOptions = [
      { label: 'Revenue', value: 'revenue' },
      { label: 'Quantity', value: 'quantity' }
    ];
    
    const fetchData = async () => {
      loading.value = true;
      try {
        const today = new Date();
        const startDate = new Date();
        startDate.setDate(today.getDate() - 30); // Last 30 days
        
        const response = await axios.get(props.endpoint, {
          params: {
            date_from: startDate.toISOString().split('T')[0],
            date_to: today.toISOString().split('T')[0],
            sort_by: view.value,
            limit: props.limit
          }
        });
        
        if (response.data && response.data.product_performance) {
          products.value = response.data.product_performance.products;
        }
      } catch (error) {
        console.error('Error fetching top products data:', error);
        // Fallback to dummy data
        generateDummyData();
      } finally {
        loading.value = false;
      }
    };
    
    const generateDummyData = () => {
      const productNames = [
        'Premium Laptop', 'Wireless Earbuds', 'Smartphone X', 
        'Smart TV 55"', 'Bluetooth Speaker', 'Coffee Maker',
        'Gaming Console', 'Fitness Tracker', 'Desk Lamp', 'External SSD'
      ];
      
      products.value = productNames.slice(0, props.limit).map((name, index) => {
        const revenue = Math.round(1000000 + Math.random() * 5000000);
        const unitsSold = Math.round(10 + Math.random() * 100);
        
        return {
          id: 'PROD-' + (index + 1),
          name,
          sku: 'SKU-' + (1000 + index),
          category: ['Electronics', 'Home', 'Accessories'][Math.floor(Math.random() * 3)],
          units_sold: unitsSold,
          revenue,
          profit: Math.round(revenue * 0.3),
          margin: Math.round(30 + Math.random() * 20)
        };
      });
      
      // Sort by current view
      if (view.value === 'revenue') {
        products.value.sort((a, b) => b.revenue - a.revenue);
      } else {
        products.value.sort((a, b) => b.units_sold - a.units_sold);
      }
    };
    
    const chartData = computed(() => {
      const sortedProducts = [...products.value];
      
      if (view.value === 'revenue') {
        sortedProducts.sort((a, b) => b.revenue - a.revenue);
      } else {
        sortedProducts.sort((a, b) => b.units_sold - a.units_sold);
      }
      
      const labels = sortedProducts.map(product => product.name);
      let data, backgroundColor;
      
      if (view.value === 'revenue') {
        data = sortedProducts.map(product => product.revenue);
        backgroundColor = 'rgba(79, 70, 229, 0.8)';
      } else {
        data = sortedProducts.map(product => product.units_sold);
        backgroundColor = 'rgba(16, 185, 129, 0.8)';
      }
      
      return {
        labels,
        datasets: [
          {
            label: view.value === 'revenue' ? 'Revenue (IDR)' : 'Units Sold',
            data,
            backgroundColor,
            borderColor: view.value === 'revenue' ? '#4338ca' : '#047857',
            borderWidth: 1,
            borderRadius: 4
          }
        ]
      };
    });
    
    const chartOptions = computed(() => {
      return {
        indexAxis: 'y',
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                let label = context.dataset.label || '';
                if (label) {
                  label += ': ';
                }
                if (view.value === 'revenue') {
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
        },
        scales: {
          x: {
            grid: {
              display: false
            },
            ticks: {
              callback: function(value) {
                if (view.value === 'revenue') {
                  if (value >= 1000000) {
                    return (value / 1000000).toLocaleString() + 'M';
                  } else if (value >= 1000) {
                    return (value / 1000).toLocaleString() + 'K';
                  } else {
                    return value;
                  }
                }
                return value;
              }
            }
          },
          y: {
            grid: {
              display: false
            }
          }
        }
      };
    });
    
    const changeView = (newView) => {
      view.value = newView;
      fetchData();
    };
    
    onMounted(() => {
      fetchData();
    });
    
    return {
      loading,
      view,
      viewOptions,
      chartData,
      chartOptions,
      changeView
    };
  }
};
</script>

<style scoped>
.view-selector {
  display: flex;
  gap: 8px;
}

.view-button {
  padding: 4px 8px;
  font-size: 0.75rem;
  background-color: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.view-button.active {
  background-color: #4f46e5;
  border-color: #4338ca;
  color: white;
}

.view-button:hover:not(.active) {
  background-color: #e5e7eb;
}
</style>