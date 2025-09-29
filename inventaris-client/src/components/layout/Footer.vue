<template>
  <footer class="app-footer">
    <div class="footer-content">
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
