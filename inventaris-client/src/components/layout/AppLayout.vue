<template>
  <div class="app-layout app-main-layout">
    <Sidebar ref="sidebar" />
    <div class="main-container" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
      <Header />
      <main class="main-content">
        <slot name="header"></slot>
        <slot></slot>
      </main>
      <Footer />
    </div>
  </div>
</template>

<script>
import Header from './Header.vue';
import Sidebar from './Sidebar.vue';
import Footer from './Footer.vue';

export default {
  name: "AppLayout",
  components: {
    Header,
    Sidebar,
    Footer
  },
  data() {
    return {
      sidebarCollapsed: false
    };
  },
  mounted() {
    // Check if sidebar is collapsed from localStorage
    const savedState = localStorage.getItem('sidebarCollapsed');
    this.sidebarCollapsed = savedState === 'true';
    
    // Watch for changes in the sidebar component
    if (this.$refs.sidebar) {
      this.$watch(() => this.$refs.sidebar.isCollapsed, (newVal) => {
        this.sidebarCollapsed = newVal;
      });
    }
    
    // Check screen size on mount
    this.checkScreenSize();
    window.addEventListener('resize', this.checkScreenSize);
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.checkScreenSize);
  },
  methods: {
    checkScreenSize() {
      const screenWidth = window.innerWidth;
      if (screenWidth <= 768) {
        this.sidebarCollapsed = true;
        if (this.$refs.sidebar) {
          this.$refs.sidebar.isCollapsed = true;
        }
      }
    }
  }
};
</script>

<style>
@import '@/styles/app-layout.css';
</style>