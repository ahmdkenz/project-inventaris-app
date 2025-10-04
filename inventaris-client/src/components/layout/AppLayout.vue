<template>
  <div class="app-layout app-main-layout">
    <Sidebar 
      ref="sidebar" 
      @sidebar-hover="handleSidebarHover" 
    />
    <div class="main-container" :class="{ 
      'sidebar-collapsed': sidebarCollapsed, 
      'sidebar-hovered': sidebarHovered && sidebarCollapsed 
    }">
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
      sidebarCollapsed: false,
      sidebarHovered: false
    };
  },
  mounted() {
    // Always start with collapsed sidebar
    this.sidebarCollapsed = true;
    localStorage.setItem('sidebarCollapsed', 'true');
    
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
    },
    handleSidebarHover(isHovered) {
      // Update the hover state
      this.sidebarHovered = isHovered;
      
      // Debugging console log to see hover state changes
      console.log('Sidebar hovered:', isHovered);
    }
  }
};
</script>

<style>
@import '@/styles/app-layout.css';
</style>