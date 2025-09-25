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
        <option value="">Select Role</option>
        <option value="admin">Admin</option>
        <option value="staff">Staff</option>
      </select>

      <button type="submit">Register</button>
    </form>
    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    
    <div class="login-link">
      <p>Already have an account? <router-link to="/login">Login here</router-link></p>
    </div>
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
      role: "",
      errorMessage: "",
    };
  },
  methods: {
    async handleRegister() {
      // Validasi client-side
      if (this.password !== this.passwordConfirmation) {
        this.errorMessage = "Password and confirmation do not match.";
        return;
      }

      if (this.password.length < 8) {
        this.errorMessage = "Password must be at least 8 characters long.";
        return;
      }

      if (!this.role) {
        this.errorMessage = "Please select a role.";
        return;
      }

      this.errorMessage = ""; // Clear previous errors

      try {
        const response = await axios.post("/register", {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmation: this.passwordConfirmation,
          role: this.role,
        });
        console.log("Registration successful:", response.data);
        alert(`Registration successful! User ${response.data.user.name} created with role ${response.data.user.role}.`);
        this.$router.push("/login");
      } catch (error) {
        console.error("Registration error:", error.response);
        if (error.response?.data?.errors) {
          // Handle validation errors from server
          const errors = error.response.data.errors;
          const errorMessages = Object.values(errors).flat();
          this.errorMessage = errorMessages.join(". ");
        } else {
          this.errorMessage =
            error.response?.data?.message ||
            "Registration failed. Please try again.";
        }
      }
    },
  },
};
</script>

<style scoped>
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

input, select {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 14px;
  transition: border-color 0.3s ease;
}

input:focus, select:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

select {
  background-color: white;
  cursor: pointer;
}

button {
  width: 100%;
  padding: 12px;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #218838;
}

button:active {
  transform: translateY(1px);
}

.error-message {
  color: #dc3545;
  text-align: center;
  margin-top: 15px;
  padding: 10px;
  background-color: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 5px;
  font-size: 14px;
}

.login-link {
  text-align: center;
  margin-top: 20px;
  padding-top: 15px;
  border-top: 1px solid #eee;
}

.login-link p {
  margin: 0;
  color: #666;
}

.login-link a {
  color: #007bff;
  text-decoration: none;
  font-weight: 500;
}

.login-link a:hover {
  text-decoration: underline;
}
</style>
