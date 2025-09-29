<template>
  <AppLayout>
    <template #header>
      <h1>{{ isAdmin ? 'Admin Dashboard' : 'Staff Dashboard' }}</h1>
    </template>

    <template #default>
      <nav class="dashboard-nav">
        <router-link to="/products" class="nav-item">
          📦 Products
        </router-link>
        <router-link to="/stocks/adjustment" class="nav-item">
          📊 Stock Management
        </router-link>
        <router-link to="/reports" class="nav-item">
          📈 Reports
        </router-link>
        <template v-if="isAdmin">
          <router-link to="/products/create" class="nav-item admin-only">
            ➕ Add Product
          </router-link>
          <router-link to="/settings/users" class="nav-item admin-only">
            👥 User Management
          </router-link>
          <router-link to="/settings/system" class="nav-item admin-only">
            ⚙️ System Settings
          </router-link>
        </template>
      </nav>

      <main class="dashboard-content">
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
          <div class="stat-card" v-if="isAdmin">
            <h3>Total Users</h3>
            <p class="stat-number">{{ stats.totalUsers }}</p>
          </div>
        </div>

        <div class="recent-activities">
          <h2>Recent Activities</h2>
          <div class="activity-list">
            <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
              <span class="activity-time">{{ formatDate(activity.created_at) }}</span>
              <span class="activity-desc">{{ activity.description }}</span>
              <span class="activity-user">by {{ activity.user_name }}</span>
            </div>
          </div>
        </div>
      </main>
    </template>
  </AppLayout>
</template>

<script>
import AppLayout from '@/components/layout/AppLayout.vue';
import axios from '@/services/axios';

export default {
  name: 'DashboardView',
  components: {
    AppLayout
  },
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
  computed: {
    isAdmin() {
      return this.user.role === 'admin';
    }
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
        this.stats = {
          totalProducts: 150,
          lowStock: 5,
          recentTransactions: 23,
          totalUsers: this.isAdmin ? 12 : 0
        };

        this.recentActivities = [
          {
            id: 1,
            description: 'New product added: Laptop Dell XPS 13',
            user_name: this.user.name,
            created_at: new Date().toISOString()
          },
          {
            id: 2,
            description: 'Stock updated for Product #123',
            user_name: 'System',
            created_at: new Date(Date.now() - 3600000).toISOString()
          },
          {
            id: 3,
            description: 'New user registered: John Doe',
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
    }
  }
};
</script>

<style>
@import '@/styles/dashboard.css';
@import '@/styles/minimal-dashboard.css';
</style>