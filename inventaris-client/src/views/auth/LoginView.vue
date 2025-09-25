<template>
  <div class="login-container">
    <h1>Login</h1>
    <form @submit.prevent="handleLogin">
      <label for="email">Email:</label>
      <input id="email" v-model="email" type="email" required />

      <label for="password">Password:</label>
      <input id="password" v-model="password" type="password" required />

      <button type="submit">Login</button>

      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    </form>

    <!-- Register Button -->
    <div class="register-link">
      <p>Don't have an account? <router-link to="/register">Register here</router-link></p>
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
    };
  },
  methods: {
    async handleLogin() {
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
      }
    },
  },
};
</script>

<style scoped>
@import "../../styles/login.css";
</style>
