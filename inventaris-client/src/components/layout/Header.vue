<template>
  <header class="header">
    <div class="header-left">
      <h1 class="page-title">{{ pageTitle || 'Inventaris App' }}</h1>
    </div>
    <div class="header-right">
      <div v-if="user" class="user-info">
        <span>{{ user.name }}</span>
        <div class="user-dropdown">
          <button @click="toggleDropdown" class="dropdown-toggle">
            <i class="fas fa-user-circle"></i>
          </button>
          <div v-if="showDropdown" class="dropdown-menu">
            <router-link to="/profile" class="dropdown-item">Profile</router-link>
            <button @click="logout" class="dropdown-item">Logout</button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
export default {
  name: 'Header',
  data() {
    return {
      user: null,
      showDropdown: false,
      pageTitle: ''
    };
  },
  created() {
    this.loadUserData();
    this.updateTitle();
    this.$router.afterEach(this.updateTitle);
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    toggleDropdown() {
      this.showDropdown = !this.showDropdown;
    },
    updateTitle() {
      // Map routes to titles
      const routeTitles = {
        '/': 'Dashboard',
        '/products': 'Products',
        '/products/create': 'Create Product',
        '/orders': 'Orders',
        '/reports': 'Reports',
        '/settings': 'Settings',
        '/stocks': 'Stock Management'
      };
      
      const path = this.$router.currentRoute.value.path;
      this.pageTitle = routeTitles[path] || 'Inventaris App';
    },
    logout() {
      localStorage.removeItem('authToken');
      localStorage.removeItem('user');
      this.$router.push('/login');
    }
  }
};
</script>

<style>
@import '@/styles/header.css';
</style>