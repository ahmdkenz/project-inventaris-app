<template>
  <div class="dashboard">
    <header class="dashboard-header">
      <h1>Admin Dashboard</h1>
      <div class="user-info">
        <span>Welcome, {{ user.name }}</span>
        <button @click="logout" class="logout-btn">Logout</button>
      </div>
    </header>

    <nav class="dashboard-nav">
      <router-link to="/products" class="nav-item">📦 Products</router-link>
      <router-link to="/stocks/adjustment" class="nav-item">📊 Stock Management</router-link>
      <router-link to="/reports" class="nav-item">📈 Reports</router-link>
      <router-link to="/products/create" class="nav-item admin-only">➕ Add Product</router-link>
      <router-link to="/settings/users" class="nav-item admin-only">👥 User Management</router-link>
      <router-link to="/settings/system" class="nav-item admin-only">⚙️ System Settings</router-link>
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
        <div class="stat-card">
          <h3>Total Users</h3>
          <p class="stat-number">{{ stats.totalUsers }}</p>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <div class="action-card">
          <div class="icon">📦</div>
          <h4>Add Product</h4>
          <p>Add new products to inventory</p>
          <router-link to="/products/create" class="action-btn">Add Product</router-link>
        </div>
        <div class="action-card">
          <div class="icon">👥</div>
          <h4>Manage Users</h4>
          <p>Create and manage user accounts</p>
          <router-link to="/settings/users" class="action-btn">Manage Users</router-link>
        </div>
        <div class="action-card">
          <div class="icon">📊</div>
          <h4>View Reports</h4>
          <p>Generate and export reports</p>
          <router-link to="/reports" class="action-btn">View Reports</router-link>
        </div>
        <div class="action-card">
          <div class="icon">⚙️</div>
          <h4>System Settings</h4>
          <p>Configure system preferences</p>
          <router-link to="/settings/system" class="action-btn">Settings</router-link>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="recent-activities">
        <h2>Recent Activities</h2>
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
  name: 'AdminDashboard',
  data() {
    return {
      user: {},
      stats: {
        totalProducts: 0,
        lowStock: 0,
        recentTransactions: 0,
        totalUsers: 0
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
        // Mock data - replace with actual API calls
        this.stats = {
          totalProducts: 150,
          lowStock: 5,
          recentTransactions: 23,
          totalUsers: 12
        };
        
        this.recentActivities = [
          {
            id: 1,
            description: 'New product added: Laptop Dell XPS 13',
            user_name: this.user.name || 'Admin',
            created_at: new Date().toISOString()
          },
          {
            id: 2,
            description: 'User John Doe created',
            user_name: 'System',
            created_at: new Date(Date.now() - 3600000).toISOString()
          },
          {
            id: 3,
            description: 'Stock updated for Product #123',
            user_name: 'Admin',
            created_at: new Date(Date.now() - 7200000).toISOString()
          }
        ];
      } catch (error) {
        console.error('Error loading dashboard data:', error);
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
</style>
