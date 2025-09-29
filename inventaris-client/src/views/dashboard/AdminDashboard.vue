<template>
  <AppLayout>
    <template #header>
    </template>

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
  </AppLayout>
</template>

<script>
import axios from '@/services/axios';
import AppLayout from '@/components/layout/AppLayout.vue';

export default {
  name: 'AdminDashboard',
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
  async mounted() {
    this.loadUserData();
    await Promise.all([
      this.loadDashboardData(),
      this.loadTotalUsers()
    ]);

    // Polling untuk realtime refresh
    this._dashboardInterval = setInterval(async () => {
      await Promise.all([
        this.loadDashboardData(),
        this.loadTotalUsers()
      ]);
    }, 10000); // Refresh setiap 10 detik
  },
  beforeUnmount() {
    if (this._dashboardInterval) clearInterval(this._dashboardInterval);
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    
    async loadTotalUsers() {
      try {
        // Load total users directly from users endpoint
        const response = await axios.get('/users');
        
        if (response.data && Array.isArray(response.data)) {
          // Update stats for total users
          this.stats.totalUsers = response.data.length;
          console.log('Total users loaded from database:', this.stats.totalUsers);
        }
      } catch (error) {
        console.error('Error loading users data:', error);
        // Don't reset totalUsers here, keep the value from loadDashboardData()
      }
    },
    async loadDashboardData() {
      try {
        const statsResponse = await axios.get('/dashboard/stats');

        if (statsResponse.data) {
          this.stats.totalProducts = statsResponse.data.totalProducts || 0;
          this.stats.lowStock = statsResponse.data.lowStock || 0; // Menggunakan nilai lowStock langsung dari backend
          this.stats.recentTransactions = statsResponse.data.recentTransactions || 0;
          console.log('Stats loaded from database:', this.stats);
        }
      } catch (error) {
        console.error('Error loading dashboard data:', error);
        // Fallback ke nilai sebelumnya jika API gagal
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
@import "../../styles/layout.css";
@import "../../styles/dashboard.css";
@import "../../styles/responsive-fixes.css";

.dashboard {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: linear-gradient(135deg, #e0eafc, #cfdef3);
}

.dashboard-header {
  background: #ffffff;
  padding: 1.5rem 2rem;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dashboard-nav {
  background: #ffffff;
  padding: 1rem 2rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.nav-item {
  text-decoration: none;
  color: #333;
  font-weight: 500;
  position: relative;
}

.nav-item:hover {
  color: #007bff;
}

.admin-only {
  display: none;
}

@media (min-width: 768px) {
  .admin-only {
    display: block;
  }
}

.dashboard-content {
  flex: 1;
  padding: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.quick-actions {
  margin-top: 2rem;
}

.recent-activities {
  margin-top: 2rem;
}
</style>
