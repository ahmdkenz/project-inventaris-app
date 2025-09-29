<template>
  <aside class="sidebar">
    <div class="logo">Inventaris App</div>
    <nav class="nav-menu">
      <!-- Dashboard link berbeda untuk admin dan staff -->
      <router-link 
        :to="isAdmin ? '/admin/dashboard' : '/dashboard'" 
        class="nav-item" 
        exact
      >
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
      </router-link>
      
      <router-link to="/products" class="nav-item">
        <i class="fas fa-box"></i>
        <span>Products</span>
      </router-link>
      
      <router-link to="/stocks/adjustment" class="nav-item">
        <i class="fas fa-warehouse"></i>
        <span>Stock Management</span>
      </router-link>
      
      <!-- Menu Orders hanya tampil untuk staff, tidak untuk admin -->
      <router-link to="/orders" class="nav-item" v-if="!isAdmin">
        <i class="fas fa-shopping-cart"></i>
        <span>Orders</span>
      </router-link>
      
      <router-link to="/reports" class="nav-item">
        <i class="fas fa-chart-bar"></i>
        <span>Reports</span>
      </router-link>
      
      <!-- Menu khusus admin -->
      <template v-if="isAdmin">
        <router-link to="/products/create" class="nav-item admin-menu">
          <i class="fas fa-plus"></i>
          <span>Add Product</span>
        </router-link>
        
        <router-link to="/settings/users" class="nav-item admin-menu">
          <i class="fas fa-users-cog"></i>
          <span>User Management</span>
        </router-link>
        
        <router-link to="/settings/system" class="nav-item admin-menu">
          <i class="fas fa-cog"></i>
          <span>System Settings</span>
        </router-link>
      </template>
    </nav>
  </aside>
</template>

<script>
export default {
  name: 'Sidebar',
  data() {
    return {
      user: null
    };
  },
  computed: {
    isAdmin() {
      return this.user && this.user.role === 'admin';
    }
  },
  created() {
    this.loadUserData();
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    }
  }
};
</script>

<style>
@import '@/styles/sidebar.css';
@import '@/styles/sidebar-animations.css';
</style>