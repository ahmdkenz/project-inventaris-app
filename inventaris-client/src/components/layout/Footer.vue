<template>
  <footer class="app-footer">
    <div class="footer-content">
      <div class="footer-row">
        <div class="footer-logo">
          <span>Inventaris App</span>
        </div>
        <div class="footer-links">
          <router-link to="/" class="footer-link">Home</router-link>
          <router-link to="/about" class="footer-link">About</router-link>
          <router-link to="/contact" class="footer-link">Contact</router-link>
        </div>
      </div>
      <div class="footer-copyright">
        &copy; {{ currentYear }} Inventaris App. All rights reserved.
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: "Footer",
  computed: {
    currentYear() {
      return new Date().getFullYear();
    }
  }
};
</script>

<style>
@import '@/styles/footer.css';
</style>
