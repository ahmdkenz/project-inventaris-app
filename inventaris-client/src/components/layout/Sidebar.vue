<template>
  <aside class="sidebar">
    <div class="logo">
      <span>Inventaris App</span>
    </div>
    <nav class="nav-menu">
      <!-- Dashboard link berbeda untuk admin dan staff -->
      <router-link 
        :to="isAdmin ? '/admin/dashboard' : '/dashboard'" 
        class="nav-item" 
        exact
      >
        <Home :size="20" />
        <span>Dashboard</span>
      </router-link>
      
      <router-link to="/products" class="nav-item">
        <Box :size="20" />
        <span>Products</span>
      </router-link>
      
      <router-link to="/stocks/adjustment" class="nav-item">
        <Warehouse :size="20" />
        <span>Stock Management</span>
      </router-link>
      
      <!-- Menu Orders hanya tampil untuk staff, tidak untuk admin -->
      <router-link to="/orders" class="nav-item" v-if="!isAdmin">
        <ShoppingCart :size="20" />
        <span>Orders</span>
      </router-link>
      
      <router-link to="/reports" class="nav-item">
        <BarChart :size="20" />
        <span>Reports</span>
      </router-link>
      
      <!-- Menu khusus admin -->
      <template v-if="isAdmin">
        <router-link to="/products/create" class="nav-item admin-menu">
          <Plus :size="20" />
          <span>Add Product</span>
        </router-link>
        
        <router-link to="/settings/users" class="nav-item admin-menu">
          <Users :size="20" />
          <span>User Management</span>
        </router-link>
        
        <router-link to="/settings/system" class="nav-item admin-menu">
          <Cog :size="20" />
          <span>System Settings</span>
        </router-link>
      </template>
    </nav>
  </aside>
</template><script>
import {
  Home,
  Box,
  Warehouse,
  ShoppingCart,
  BarChart,
  Plus,
  Users,
  Cog
} from 'lucide-vue-next';
import { RouterLink } from 'vue-router';

export default {
  name: 'Sidebar',
  components: {
    Home,
    Box,
    Warehouse,
    ShoppingCart,
    BarChart,
    Plus,
    Users,
    Cog,
    RouterLink
  },
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