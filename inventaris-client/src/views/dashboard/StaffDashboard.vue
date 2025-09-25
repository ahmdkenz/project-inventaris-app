<template>
  <div class="dashboard">
    <header class="dashboard-header">
      <h1>Staff Dashboard</h1>
      <div class="user-info">
        <span>Welcome, {{ user.name }}</span>
        <button @click="logout" class="logout-btn">Logout</button>
      </div>
    </header>

    <nav class="dashboard-nav">
      <router-link to="/products" class="nav-item">📦 Products</router-link>
      <router-link to="/stocks/adjustment" class="nav-item">📊 Stock Management</router-link>
      <router-link to="/orders" class="nav-item">🛒 Orders</router-link>
      <router-link to="/reports" class="nav-item">📈 Reports</router-link>
    </nav>

    <main class="dashboard-content">
      <!-- Stats Grid -->
      <div class="stats-grid">
        <div class="stat-card">
          <h3>Total Products</h3>
          <p class="stat-number">{{ stats.totalProducts }}</p>
        </div>
        <div class="stat-card">
          <h3>Low Stock Items</h3>
          <p class="stat-number warning">{{ stats.lowStock }}</p>
        </div>
        <div class="stat-card">
          <h3>Recent Transactions</h3>
          <p class="stat-number">{{ stats.recentTransactions }}</p>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <div class="action-card">
          <div class="icon">📦</div>
          <h4>View Products</h4>
          <p>Browse and search products</p>
          <router-link to="/products" class="action-btn">View Products</router-link>
        </div>
        <div class="action-card">
          <div class="icon">📊</div>
          <h4>Stock Adjustment</h4>
          <p>Adjust inventory stock levels</p>
          <router-link to="/stocks/adjustment" class="action-btn">Adjust Stock</router-link>
        </div>
        <div class="action-card">
          <div class="icon">�</div>
          <h4>Manage Orders</h4>
          <p>Sales and purchase orders</p>
          <router-link to="/orders" class="action-btn">Manage Orders</router-link>
        </div>
        <div class="action-card">
          <div class="icon">�📈</div>
          <h4>View Reports</h4>
          <p>Access available reports</p>
          <router-link to="/reports" class="action-btn">View Reports</router-link>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="recent-activities">
        <h2>My Recent Activities</h2>
        <div class="activity-list">
          <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
            <span class="activity-desc">{{ activity.description }}</span>
            <span class="activity-user">by {{ activity.user_name }}</span>
            <span class="activity-time">{{ formatDate(activity.created_at) }}</span>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import axios from '../../services/axios';

export default {
  name: 'StaffDashboard',
  data() {
    return {
      user: {},
      stats: {
        totalProducts: 0,
        lowStock: 0,
        recentTransactions: 0
      },
      recentActivities: []
    };
  },
  async mounted() {
    this.loadUserData();
    await this.loadDashboardData();
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    async loadDashboardData() {
      try {
        const statsResponse = await axios.get('/dashboard/stats');

        if (statsResponse.data) {
          this.stats = {
            totalProducts: statsResponse.data.totalProducts || 0,
            lowStock: statsResponse.data.lowStock || 0,
            recentTransactions: statsResponse.data.recentTransactions || 0
          };
        }
      } catch (error) {
        console.error('Error loading dashboard data:', error);
        // Fallback to mock data if API fails
        this.stats = {
          totalProducts: 0,
          lowStock: 0,
          recentTransactions: 0
        };
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleString();
    },
    async logout() {
      try {
        await axios.post('/logout');
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        localStorage.removeItem('authToken');
        localStorage.removeItem('user');
        this.$router.push('/login');
      }
    }
  }
};
</script>

<style scoped>
@import "../../styles/dashboard.css";
@import "../../styles/orders.css";
</style>
