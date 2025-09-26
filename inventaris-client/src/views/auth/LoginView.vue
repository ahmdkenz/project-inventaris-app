<template>
  <div class="app-layout">
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

          <div v-if="errorMessage" class="error">
            {{ errorMessage }}
          </div>
        </form>

        <div class="auth-link">
          <p>Don't have an account? <router-link to="/register">Register here</router-link></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "../../services/axios";

export default {
  name: "LoginView",
  data() {
    return {
      email: "",
      password: "",
      errorMessage: "",
      isSubmitting: false,
    };
  },
  created() {
    // Check if redirected with error=inactive
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('error') === 'inactive') {
      this.errorMessage = "Your account is inactive. Please contact administrator.";
    }
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
        
        // Store token and user data
        localStorage.setItem("authToken", token);
        localStorage.setItem("user", JSON.stringify(user));
        
        // Set axios default header
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        
        // Redirect based on role
        if (user.role === 'admin') {
          this.$router.push('/admin/dashboard');
        } else {
          this.$router.push('/dashboard');
        }
      } catch (error) {
        this.errorMessage =
          error.response?.data?.message || "Login failed. Please try again.";
      } finally {
        this.isSubmitting = false;
      }
    },
  },
};
</script>

<style scoped>
@import "../../styles/layout.css";

.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 2rem;
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
  font-size: 2rem;
  color: #333;
}

.auth-form {
  width: 100%;
}

.btn-block {
  width: 100%;
  padding: 0.75rem;
  font-size: 1rem;
  margin-top: 1rem;
}

.auth-link {
  text-align: center;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid #eee;
}

.auth-link p {
  margin: 0;
  color: #666;
}

.auth-link a {
  color: var(--link-color, #007bff);
  text-decoration: none;
  font-weight: 500;
}

.auth-link a:hover {
  text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
  .auth-container {
    padding: 1rem;
  }
  
  .auth-card {
    padding: 2rem;
  }
  
  .auth-card h1 {
    font-size: 1.5rem;
  }
}
</style>
