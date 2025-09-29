<!-- File: src/views/auth/LoginView.vue -->
<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <h1>Login</h1>
        <form @submit.prevent="handleLogin" class="auth-form">
          <div class="form-group">
            <label for="email">Email:</label>
            <input 
              id="email" 
              v-model="email" 
              type="email" 
              class="form-control"
              placeholder="Enter your email" 
              required 
            />
          </div>

          <div class="form-group">
            <label for="password">Password:</label>
            <input 
              id="password" 
              v-model="password" 
              type="password" 
              class="form-control"
              placeholder="Enter your password" 
              required 
            />
          </div>

          <button type="submit" class="btn btn-primary btn-block" :disabled="isSubmitting">
            {{ isSubmitting ? 'Logging in...' : 'Login' }}
          </button>

          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>
        </form>

        <div class="auth-link">
          <p>Belum punya akun? <router-link to="/register">Daftar di sini</router-link></p>
        </div>
      </div>
    </div>
    
    <Footer />
  </div>
</template>

<script>
// [DIUBAH] Impor store dan router
import { useAuthStore } from "@/stores/authStore";
import { useRouter } from "vue-router";
import axios from "@/services/axios";
import Footer from "@/components/layout/Footer.vue";

export default {
  name: "LoginView",
  components: {
    Footer
  },
  // [DIUBAH] Gunakan setup function untuk Composition API
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();
    return { authStore, router };
  },
  data() {
    return {
      email: "",
      password: "",
      errorMessage: "",
      isSubmitting: false,
    };
  },
  methods: {
    async handleLogin() {
      this.isSubmitting = true;
      this.errorMessage = "";
      
      try {
        const response = await axios.post("/login", {
          email: this.email,
          password: this.password,
        });
        
        const { user, token } = response.data;
        
        // [DIUBAH] Gunakan action dari authStore untuk login
        this.authStore.login(user, token);
        
        // Redirect based on role
        if (user.role === 'admin') {
          this.router.push('/admin/dashboard'); // Sesuaikan dengan rute Anda
        } else {
          this.router.push('/dashboard'); // Sesuaikan dengan rute Anda
        }
      } catch (error) {
        this.errorMessage =
          error.response?.data?.message || "Login gagal. Silakan coba lagi.";
      } finally {
        this.isSubmitting = false;
      }
    },
  },
};
</script>

<style scoped>
@import "../../styles/login.css";

.auth-page {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.auth-container {
  flex: 1;
}

/* Style untuk integrasi dengan Footer */
.app-footer {
  position: relative;
  transition: all 0.4s ease;
  transform-origin: bottom;
}

.auth-page .app-footer:hover {
  padding-top: 25px;
  transform: translateY(-3px);
}
</style>