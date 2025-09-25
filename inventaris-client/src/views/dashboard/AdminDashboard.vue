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
        // Load dashboard statistics from API
        const [statsResponse, activitiesResponse] = await Promise.all([
          axios.get('/dashboard/stats'),
          axios.get('/dashboard/activities')
        ]);
        
        if (statsResponse.data) {
          // Pastikan data dari API digunakan, tidak diganti dengan mock data
          this.stats = {
            totalProducts: statsResponse.data.totalProducts || 0,
            lowStock: statsResponse.data.lowStock || 0,
            recentTransactions: statsResponse.data.recentTransactions || 0,
            totalUsers: statsResponse.data.totalUsers || 0
          };
          console.log('Stats loaded from database:', this.stats);
        }
        
        if (activitiesResponse.data && Array.isArray(activitiesResponse.data)) {
          this.recentActivities = activitiesResponse.data;
        }
      } catch (error) {
        console.error('Error loading dashboard data:', error);
        // Fallback to mock data if API fails
        this.stats = {
          totalProducts: 0,
          lowStock: 0,
          recentTransactions: 0,
          totalUsers: 0
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
        
        // Mencoba load data langsung dari endpoint products dan users jika stats endpoint gagal
        try {
          const productsResponse = await axios.get('/products');
          if (productsResponse.data && Array.isArray(productsResponse.data.data)) {
            this.stats.totalProducts = productsResponse.data.data.length;
            this.stats.lowStock = productsResponse.data.data.filter(
              product => product.stock <= (product.min_stock || 10)
            ).length;
          }
          
          const usersResponse = await axios.get('/users');
          if (usersResponse.data && Array.isArray(usersResponse.data.data)) {
            this.stats.totalUsers = usersResponse.data.data.length;
          }
        } catch (secondaryError) {
          console.error('Secondary API call failed:', secondaryError);
        }
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
