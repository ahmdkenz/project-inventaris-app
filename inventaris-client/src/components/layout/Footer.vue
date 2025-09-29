<template>
  <footer class="app-footer">
    <div class="footer-content">
      <div class="footer-logo">
        <span>Inventaris App</span>
      </div>
      <div class="footer-links">
        <router-link to="/" class="footer-link">Home</router-link>
        <router-link to="/about" class="footer-link">About</router-link>
        <router-link to="/contact" class="footer-link">Contact</router-link>
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

<style scoped>
.app-footer {
  background-color: #001529;
  color: rgba(255, 255, 255, 0.65);
  padding: 20px 0;
  transition: all 0.3s ease;
  width: 100%;
  z-index: 900;
  border-top: 1px solid #002a4a;
}

.app-footer:hover {
  color: rgba(255, 255, 255, 0.9);
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  padding: 0 20px;
}

.footer-logo {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 10px;
}

.footer-logo span {
  transition: color 0.3s ease;
}

.app-footer:hover .footer-logo span {
  color: #1890ff;
}

.footer-links {
  display: flex;
  gap: 20px;
  margin-bottom: 10px;
}

.footer-link {
  color: rgba(255, 255, 255, 0.65);
  text-decoration: none;
  transition: all 0.3s;
}

.footer-link:hover {
  color: #1890ff;
  text-decoration: underline;
}

.footer-copyright {
  width: 100%;
  text-align: center;
  margin-top: 10px;
  font-size: 14px;
}

@media (max-width: 768px) {
  .footer-content {
    flex-direction: column;
    text-align: center;
  }
  
  .footer-links {
    margin: 10px 0;
  }
}
</style>
