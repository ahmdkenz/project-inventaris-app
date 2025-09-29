<template>
  <header class="header">
    <div class="header-content">
      <div class="header-left">
        <button @click="toggleSidebar" class="menu-toggle">
          <Menu :size="20" />
        </button>
        <h1 class="page-title">{{ pageTitle || 'Inventaris App' }}</h1>
      </div>
      <div class="header-right">
        <div v-if="user" class="user-info">
          <span class="user-name">{{ user.name }}</span>
          <div class="user-dropdown">
            <button @click="toggleDropdown" class="dropdown-toggle">
              <UserCircle :size="24" />
            </button>
            <div v-if="showDropdown" class="dropdown-menu">
              <router-link to="/profile" class="dropdown-item">
                <User :size="16" />
                <span>Profile</span>
              </router-link>
              <button @click="logout" class="dropdown-item">
                <LogOut :size="16" />
                <span>Logout</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { Menu, UserCircle, User, LogOut } from 'lucide-vue-next';

export default {
  name: 'Header',
  components: {
    Menu,
    UserCircle,
    User,
    LogOut
  },
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
    
    // Close dropdown when clicking outside
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    toggleDropdown(event) {
      // Stop propagation to prevent the document click handler from firing
      event.stopPropagation();
      this.showDropdown = !this.showDropdown;
    },
    handleClickOutside(event) {
      // Close dropdown when clicking outside
      const dropdown = this.$el.querySelector('.user-dropdown');
      if (dropdown && !dropdown.contains(event.target)) {
        this.showDropdown = false;
      }
    },
    toggleSidebar() {
      // Find the parent AppLayout component
      let parent = this.$parent;
      while (parent && parent.$options.name !== 'AppLayout') {
        parent = parent.$parent;
      }
      
      if (parent && parent.$refs.sidebar) {
        parent.$refs.sidebar.toggleCollapse();
      }
    },
    updateTitle() {
      // Map routes to titles
      const routeTitles = {
        '/': 'Dashboard',
        '/admin/dashboard': 'Admin Dashboard',
        '/dashboard': 'Dashboard',
        '/products': 'Products',
        '/products/create': 'Create Product',
        '/orders': 'Orders',
        '/reports': 'Reports',
        '/settings': 'Settings',
        '/settings/users': 'User Management',
        '/settings/system': 'System Settings',
        '/stocks/adjustment': 'Stock Management',
        '/profile': 'User Profile'
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