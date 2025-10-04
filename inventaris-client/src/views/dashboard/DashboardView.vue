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

      <!-- Enhanced Dashboard Component -->
      <EnhancedDashboard v-if="useEnhancedDashboard" />

      <!-- Legacy Dashboard Content (as fallback) -->
      <main v-else class="dashboard-content">
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
import EnhancedDashboard from '@/components/dashboard/EnhancedDashboard.vue';
import axios from '@/services/axios';
import { useNotification } from '@/composables/useNotification';

export default {
  name: 'DashboardView',
  components: {
    AppLayout,
    EnhancedDashboard
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
      recentActivities: [],
      useEnhancedDashboard: true,
      notification: useNotification()
    };
  },
  computed: {
    isAdmin() {
      return this.user.role === 'admin';
    }
  },
  async mounted() {
    this.loadUserData();
    
    // Try to use enhanced dashboard, fall back to legacy if there's an error
    try {
      await this.checkEnhancedDashboardAvailability();
      if (!this.useEnhancedDashboard) {
        await this.loadDashboardData();
      }
    } catch (error) {
      console.error('Dashboard initialization error:', error);
      this.useEnhancedDashboard = false;
      this.notification.warning('Enhanced dashboard could not be loaded, using standard view instead.');
      await this.loadDashboardData();
    }
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
        // Fetch combined dashboard summary data from API
        const summaryResponse = await axios.get('/dashboard/summary');
        if (summaryResponse.data && summaryResponse.data.success) {
          this.stats = summaryResponse.data.stats;
          this.recentActivities = summaryResponse.data.recentActivities;
        } else {
          // If summary endpoint fails, try individual endpoints
          // Fetch dashboard stats data from API
          const statsResponse = await axios.get('/dashboard/stats');
          if (statsResponse.data) {
            this.stats = statsResponse.data;
          }
          
          // Fetch recent activities data from API
          const activitiesResponse = await axios.get('/dashboard/activities');
          if (activitiesResponse.data) {
            this.recentActivities = activitiesResponse.data;
          }
        }
        
        // If no data received from API, fallback to dummy data
        if ((!this.stats || Object.keys(this.stats).length === 0) && 
            (!this.recentActivities || this.recentActivities.length === 0)) {
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
        }
      } catch (error) {
        console.error('Error loading dashboard data:', error);
        this.notification.error('Error loading dashboard data. Using default values.');
        
        // Fallback to dummy data if API fails
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
      }
    },
    
    async checkEnhancedDashboardAvailability() {
      try {
        // Check if the enhanced dashboard API endpoint is available
        const testResponse = await axios.get('/reports/enhanced/dashboard-status', {
          timeout: 3000 // Give a bit more time to respond
        });
        
        if (testResponse.data && testResponse.data.available) {
          this.useEnhancedDashboard = true;
          console.log('Enhanced dashboard available');
        } else {
          console.log('Enhanced dashboard not available from API response');
          this.useEnhancedDashboard = false;
        }
      } catch (error) {
        console.warn('Enhanced dashboard not available, falling back to standard view:', error);
        this.useEnhancedDashboard = false;
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