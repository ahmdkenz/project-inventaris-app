<template>
  <ChartComponent
    title="Stock Distribution"
    subtitle="By category"
    type="doughnut"
    :data="chartData"
    :options="chartOptions"
    :loading="loading"
    height="300px"
  >
    <template #footer>
      <div class="chart-summary">
        <div class="summary-item">
          <span class="summary-label">Total Products:</span>
          <span class="summary-value">{{ totalProducts }}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Total Value:</span>
          <span class="summary-value">{{ formattedTotalValue }}</span>
        </div>
      </div>
    </template>
  </ChartComponent>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import ChartComponent from './ChartComponent.vue';
import axios from 'axios';

export default {
  name: 'StockDistributionChart',
  components: {
    ChartComponent
  },
  props: {
    endpoint: {
      type: String,
      default: '/api/reports/enhanced/inventory-efficiency'
    }
  },
  setup(props) {
    const loading = ref(true);
    const categories = ref([]);
    const totalProducts = ref(0);
    const totalValue = ref(0);
    
    const fetchData = async () => {
      loading.value = true;
      try {
        const response = await axios.get(props.endpoint);
        
        if (response.data && response.data.inventory_efficiency) {
          const data = response.data.inventory_efficiency;
          
          // Extract category data
          const categoryData = data.category_breakdown;
          categories.value = Object.entries(categoryData).map(([name, value]) => ({
            name,
            value
          }));
          
          // Set totals
          totalProducts.value = data.summary.total_products;
          totalValue.value = data.summary.total_stock_value;
        }
      } catch (error) {
        console.error('Error fetching stock distribution data:', error);
        // Fallback to dummy data
        generateDummyData();
      } finally {
        loading.value = false;
      }
    };
    
    const generateDummyData = () => {
      const categoryNames = [
        'Electronics', 'Home Appliances', 'Furniture', 
        'Office Supplies', 'Accessories', 'Other'
      ];
      
      categories.value = categoryNames.map(name => {
        const value = Math.round(100000 + Math.random() * 2000000);
        return { name, value };
      });
      
      totalProducts.value = Math.round(100 + Math.random() * 200);
      totalValue.value = categories.value.reduce((sum, cat) => sum + cat.value, 0);
    };
    
    const chartData = computed(() => {
      const labels = categories.value.map(cat => cat.name);
      const data = categories.value.map(cat => cat.value);
      
      // Generate a nice color palette
      const colors = [
        '#4f46e5', '#10b981', '#f59e0b', '#ef4444', 
        '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6',
        '#6366f1', '#84cc16', '#eab308', '#f43f5e'
      ];
      
      const backgroundColor = labels.map((_, i) => colors[i % colors.length]);
      
      return {
        labels,
        datasets: [
          {
            data,
            backgroundColor,
            borderColor: '#ffffff',
            borderWidth: 2,
            hoverOffset: 15
          }
        ]
      };
    });
    
    const chartOptions = computed(() => {
      return {
        plugins: {
          legend: {
            position: 'right',
            labels: {
              boxWidth: 15,
              padding: 15
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.raw;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((value / total) * 100);
                
                return `${label}: ${new Intl.NumberFormat('id-ID', {
                  style: 'currency',
                  currency: 'IDR',
                  minimumFractionDigits: 0,
                  maximumFractionDigits: 0
                }).format(value)} (${percentage}%)`;
              }
            }
          }
        },
        cutout: '60%'
      };
    });
    
    const formattedTotalValue = computed(() => {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(totalValue.value);
    });
    
    onMounted(() => {
      fetchData();
    });
    
    return {
      loading,
      chartData,
      chartOptions,
      totalProducts,
      totalValue,
      formattedTotalValue
    };
  }
};
</script>

<style scoped>
.chart-summary {
  display: flex;
  justify-content: space-around;
  padding-top: 12px;
  border-top: 1px solid #f3f4f6;
}

.summary-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.summary-label {
  font-size: 0.75rem;
  color: #6b7280;
  margin-bottom: 4px;
}

.summary-value {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
}
</style>