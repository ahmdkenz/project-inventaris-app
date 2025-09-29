<!-- File: src/views/auth/LoginView.vue -->
<template>
  <!-- Kita hapus div.app-layout dari sini -->
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
</template>

<script>
// [DIUBAH] Impor store dan router
import { useAuthStore } from "@/stores/authStore";
import { useRouter } from "vue-router";
import axios from "@/services/axios";

export default {
  name: "LoginView",
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
/* Path ke file CSS bisa jadi tidak diperlukan jika layout.css sudah global */
/* @import "../../styles/layout.css"; */

.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 2rem;
  background-color: #f0f2f5;
}

.auth-card {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 450px;
}

.auth-card h1 {
  text-align: center;
  margin-bottom: 2rem;
}

.error-message {
  color: red;
  text-align: center;
  margin-top: 1rem;
}

.auth-form {
  width: 100%;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

.btn-block {
  width: 100%;
  padding: 0.75rem;
  font-size: 1rem;
  margin-top: 1rem;
  cursor: pointer;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
}

.btn-block:disabled {
    background-color: #aaa;
}

.auth-link {
  text-align: center;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #eee;
}
</style>