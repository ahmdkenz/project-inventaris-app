<template>
  <div class="dashboard">
    <!-- Header -->
    <header class="dashboard-header">
      <h1>{{ isAdmin ? 'Admin Dashboard' : 'Staff Dashboard' }}</h1>
      <div class="user-info">
        <span>Welcome, {{ user.name }}</span>
        <button @click="logout" class="logout-btn">Logout</button>
      </div>
    </header>

    <!-- Navigation Menu -->
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
      
      <!-- Admin Only -->
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

    <!-- Dashboard Content -->
    <main class="dashboard-content">
      <!-- Quick Stats -->
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

      <!-- Recent Activities -->
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
  </div>
</template>

<script>
import axios from '../../services/axios';

export default {
  name: 'DashboardView',
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
        // Mock data for now - you can replace with actual API calls
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
.dashboard {
  min-height: 100vh;
  background-color: #f5f5f5;
}

.dashboard-header {
  background: white;
  padding: 1rem 2rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dashboard-header h1 {
  margin: 0;
  color: #333;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logout-btn {
  background: #dc3545;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

.logout-btn:hover {
  background: #c82333;
}

.dashboard-nav {
  background: white;
  padding: 1rem 2rem;
  display: flex;
  gap: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 2rem;
}

.nav-item {
  padding: 0.5rem 1rem;
  text-decoration: none;
  color: #666;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.nav-item:hover,
.nav-item.router-link-active {
  background-color: #007bff;
  color: white;
}

.nav-item.admin-only {
  border-left: 3px solid #28a745;
  margin-left: 1rem;
}

.dashboard-content {
  padding: 0 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-card h3 {
  margin: 0 0 0.5rem 0;
  color: #666;
  font-size: 0.9rem;
}

.stat-number {
  margin: 0;
  font-size: 2rem;
  font-weight: bold;
  color: #333;
}

.stat-number.warning {
  color: #dc3545;
}

.recent-activities {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recent-activities h2 {
  margin: 0 0 1rem 0;
  color: #333;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.activity-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 4px;
}

.activity-time {
  font-size: 0.8rem;
  color: #666;
  min-width: 120px;
}

.activity-desc {
  flex: 1;
  margin: 0 1rem;
}

.activity-user {
  font-size: 0.8rem;
  color: #666;
  font-style: italic;
}

@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .dashboard-nav {
    flex-wrap: wrap;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .activity-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }
}
</style>