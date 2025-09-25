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
      }
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
        this.stats = {
          totalProducts: 150,
          lowStock: 5,
          recentTransactions: 23,
          totalUsers: 12
        };
      } catch (error) {
        console.error('Error loading dashboard data:', error);
      }
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
h1 {
  color: blue;
}
.dashboard {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.user-info {
  display: flex;
  align-items: center;
}

.logout-btn {
  margin-left: 1rem;
  padding: 0.5rem 1rem;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
}

.logout-btn:hover {
  background-color: #0056b3;
}

.dashboard-nav {
  display: flex;
  flex-direction: column;
  padding: 1rem;
  background-color: #343a40;
  color: white;
  flex: 0 0 250px;
}

.nav-item {
  padding: 0.75rem 1rem;
  color: white;
  text-decoration: none;
  border-radius: 0.25rem;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
}

.nav-item:hover {
  background-color: #495057;
}

.admin-only {
  display: none;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
  padding: 1rem;
  flex: 1;
}

.stat-card {
  background-color: #ffffff;
  padding: 1.5rem;
  border-radius: 0.25rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-number {
  font-size: 1.5rem;
  font-weight: bold;
}

.warning {
  color: #dc3545;
}
</style>