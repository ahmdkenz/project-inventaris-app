<template>
  <div class="register-container">
    <h1>Register</h1>
    <form @submit.prevent="handleRegister">
      <label for="name">Name:</label>
      <input id="name" v-model="name" type="text" required />

      <label for="email">Email:</label>
      <input id="email" v-model="email" type="email" required />

      <label for="password">Password:</label>
      <input id="password" v-model="password" type="password" required />

      <label for="password_confirmation">Confirm Password:</label>
      <input
        id="password_confirmation"
        v-model="passwordConfirmation"
        type="password"
        required
      />

      <label for="role">Role:</label>
      <select id="role" v-model="role" required>
        <option value="staff">Staff</option>
        <option value="admin">Admin</option>
      </select>

      <button type="submit">Register</button>
    </form>
    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
  </div>
</template>

<script>
import axios from "../../services/axios";

export default {
  name: "RegisterView",
  data() {
    return {
      name: "",
      email: "",
      password: "",
      passwordConfirmation: "",
      role: "staff",
      errorMessage: "",
      successMessage: "",
    };
  },
  methods: {
    async handleRegister() {
      try {
        const response = await axios.post("/register", {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmation: this.passwordConfirmation,
          role: this.role,
        });
        
        this.successMessage = "Registration successful! Redirecting to login...";
        this.errorMessage = "";
        
        // Redirect after 2 seconds
        setTimeout(() => {
          this.$router.push("/login");
        }, 2000);
        
      } catch (error) {
        this.errorMessage =
          error.response?.data?.message ||
          "Registration failed. Please try again.";
        this.successMessage = "";
      }
    },
  },
};
</script>

<style scoped>
@import "../../styles/register.css";

.register-container {
  max-width: 400px;
  margin: 0 auto;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

h1 {
  text-align: center;
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 5px;
}

input,
select {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

button {
  width: 100%;
  padding: 10px;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

button:hover {
  background-color: #218838;
}

.error-message {
  color: red;
  text-align: center;
  margin-top: 10px;
}

.success-message {
  color: green;
  text-align: center;
  margin-top: 10px;
}
</style>
