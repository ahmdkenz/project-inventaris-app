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
      <router-link to="/reports" class="nav-item">📈 Reports</router-link>
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
          recentTransactions: 23
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
  color: green;
}
.dashboard {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.dashboard-header {
  background-color: #f8f9fa;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
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
  background-color: #343a40;
  padding: 0.5rem 1rem;
  display: flex;
  gap: 1rem;
}

.nav-item {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 0.25rem;
}

.nav-item:hover {
  background-color: #495057;
}

.dashboard-content {
  flex: 1;
  padding: 1rem;
  background-color: #ffffff;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
}

.stat-card {
  background-color: #f1f3f5;
  padding: 1rem;
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