<template>
  <AppLayout>
    <div class="orders-page">
      <div class="page-header">
        <h1>Order Management</h1>
      </div>
      
      <!-- Orders Navigation -->
      <nav class="orders-nav">
        <router-link to="/orders/purchase" class="order-nav-item">
          <span class="icon">📥</span>
          <span class="label">Purchase Orders</span>
        </router-link>
        <router-link to="/orders/sales" class="order-nav-item">
          <span class="icon">📤</span>
          <span class="label">Sales Orders</span>
        </router-link>
      </nav>
      
      <!-- Orders Statistics -->
      <div class="order-stats">
        <div class="order-stat-card sales">
          <div class="icon">📤</div>
          <h3>Sales Orders</h3>
          <div class="value">{{ stats.totalSalesOrders }}</div>
          <div class="trend up" v-if="stats.salesOrdersTrend > 0">
            +{{ stats.salesOrdersTrend }}% from last month
          </div>
          <div class="trend down" v-else-if="stats.salesOrdersTrend < 0">
            {{ stats.salesOrdersTrend }}% from last month
          </div>
          <div class="trend" v-else>
            Same as last month
          </div>
        </div>
        
        <div class="order-stat-card purchase">
          <div class="icon">📥</div>
          <h3>Purchase Orders</h3>
          <div class="value">{{ stats.totalPurchaseOrders }}</div>
          <div class="trend up" v-if="stats.purchaseOrdersTrend > 0">
            +{{ stats.purchaseOrdersTrend }}% from last month
          </div>
          <div class="trend down" v-else-if="stats.purchaseOrdersTrend < 0">
            {{ stats.purchaseOrdersTrend }}% from last month
          </div>
          <div class="trend" v-else>
            Same as last month
          </div>
        </div>
        
        <div class="order-stat-card pending">
          <div class="icon">⌛</div>
          <h3>Pending Orders</h3>
          <div class="value">{{ stats.pendingOrders }}</div>
        </div>
        
        <div class="order-stat-card completed">
          <div class="icon">✅</div>
          <h3>Completed Orders</h3>
          <div class="value">{{ stats.completedOrders }}</div>
        </div>
      </div>
      
      <!-- Recent Orders -->
      <div class="recent-orders">
        <h2>
          Recent Orders 
          <router-link to="/orders/all" class="view-all">
            View All <span>→</span>
          </router-link>
        </h2>
        
        <div class="order-list">
          <div v-for="order in recentOrders" :key="order.id" class="order-item">
            <div :class="['order-type', order.type]">
              {{ order.type === 'sales' ? '📤' : '📥' }}
            </div>
            <div class="order-info">
              <div class="order-number">{{ order.number }}</div>
              <div class="order-details">
                {{ order.type === 'sales' ? order.customer : order.supplier }} • {{ formatCurrency(order.amount) }}
              </div>
            </div>
            <span :class="['order-status', 'status-' + order.status]">
              {{ order.status }}
            </span>
            <div class="order-date">{{ formatDate(order.date) }}</div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import axios from '../../services/axios';
import AppLayout from '../../components/layout/AppLayout.vue';

export default {
  name: 'OrdersView',
  components: {
    AppLayout
  },
  data() {
    return {
      user: {},
      stats: {
        totalSalesOrders: 32,
        salesOrdersTrend: 12,
        totalPurchaseOrders: 18,
        purchaseOrdersTrend: -5,
        pendingOrders: 8,
        completedOrders: 42
      },
      recentOrders: []
    };
  },
  async mounted() {
    this.loadUserData();
    await Promise.all([
      this.loadOrdersData(),
      this.loadOrderStats()
    ]);
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    async loadOrderStats() {
      try {
        const response = await axios.get('/orders/stats');
        if (response.data && response.data.data) {
          this.stats = response.data.data;
        }
      } catch (error) {
        console.error('Error loading order stats:', error);
        // Fallback to mock data if API fails
        this.stats = {
          totalSalesOrders: 32,
          salesOrdersTrend: 12,
          totalPurchaseOrders: 18,
          purchaseOrdersTrend: -5,
          pendingOrders: 8,
          completedOrders: 42
        };
      }
    },
    async loadOrdersData() {
      try {
        const response = await axios.get('/orders/recent', {
          params: { limit: 5 }
        });
        
        if (response.data && response.data.data) {
          this.recentOrders = response.data.data;
        } else {
          // Fallback to mock data if API response is empty
          this.recentOrders = [
            {
              id: 1,
              type: 'sales',
              number: 'SO2024090123',
              customer: 'PT. ABC Company',
              amount: 2250000,
              status: 'pending',
              date: '2024-09-15'
            },
            {
              id: 2,
              type: 'purchase',
              number: 'PO2024090089',
              supplier: 'Tech Supplier Co.',
              amount: 1500000,
              status: 'approved',
              date: '2024-09-14'
            },
            {
              id: 3,
              type: 'sales',
              number: 'SO2024090118',
              customer: 'CV. XYZ Solutions',
              amount: 3750000,
              status: 'confirmed',
              date: '2024-09-12'
            },
            {
              id: 4,
              type: 'purchase',
              number: 'PO2024090076',
              supplier: 'Office Supplies Ltd.',
              amount: 2500000,
              status: 'received',
              date: '2024-09-10'
            },
            {
              id: 5,
              type: 'sales',
              number: 'SO2024090097',
              customer: 'Toko Komputer Maju',
              amount: 1200000,
              status: 'delivered',
              date: '2024-09-08'
            }
          ];
        }
      } catch (error) {
        console.error('Error loading orders data:', error);
        // Set fallback data
        this.recentOrders = [
          {
            id: 1,
            type: 'sales',
            number: 'SO2024090123',
            customer: 'PT. ABC Company',
            amount: 2250000,
            status: 'pending',
            date: '2024-09-15'
          },
          {
            id: 2,
            type: 'purchase',
            number: 'PO2024090089',
            supplier: 'Tech Supplier Co.',
            amount: 1500000,
            status: 'approved',
            date: '2024-09-14'
          }
        ];
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
      }).format(amount);
    }
  }
};
</script>

<style scoped>
@import "../../styles/orders.css";
@import "../../styles/responsive-fixes.css";

.page-header {
  margin-bottom: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-header h1 {
  margin: 0;
  font-size: 1.8rem;
  color: #333;
}

.orders-page {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}
</style>