<template>
  <aside class="sidebar" :class="{ 'collapsed': isCollapsed && !isHovered }">
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
        <div class="divider"></div>
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
      user: null,
      isCollapsed: false,
      isHovered: false,
      screenWidth: window.innerWidth
    };
  },
  computed: {
    isAdmin() {
      return this.user && this.user.role === 'admin';
    }
  },
  created() {
    this.loadUserData();
    this.checkScreenSize();
    window.addEventListener('resize', this.checkScreenSize);
    
    // Default sidebar to collapsed state
    this.isCollapsed = true;
    localStorage.setItem('sidebarCollapsed', 'true');
  },
  mounted() {
    // Auto-collapse by default on all screens
    this.isCollapsed = true;

    // Add hover event listeners
    const sidebar = this.$el;
    sidebar.addEventListener('mouseenter', this.handleMouseEnter);
    sidebar.addEventListener('mouseleave', this.handleMouseLeave);
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.checkScreenSize);
    const sidebar = this.$el;
    sidebar.removeEventListener('mouseenter', this.handleMouseEnter);
    sidebar.removeEventListener('mouseleave', this.handleMouseLeave);
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    toggleCollapse() {
      this.isCollapsed = !this.isCollapsed;
      localStorage.setItem('sidebarCollapsed', this.isCollapsed.toString());
    },
    handleMouseEnter() {
      this.isHovered = true;
      // Emit event when sidebar is hovered
      this.$emit('sidebar-hover', true);
    },
    handleMouseLeave() {
      this.isHovered = false;
      // Emit event when sidebar hover ends
      this.$emit('sidebar-hover', false);
    },
    checkScreenSize() {
      this.screenWidth = window.innerWidth;
      // Auto-collapse on smaller screens
      if (this.screenWidth <= 768) {
        this.isCollapsed = true;
      }
    }
  }
};
</script>

<style>
@import '@/styles/sidebar.css';
@import '@/styles/sidebar-animations.css';
</style>