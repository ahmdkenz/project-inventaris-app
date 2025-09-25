<template>
  <div class="register-container">
    <div class="register-card">
      <h1>Register</h1>
      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label for="name">Name:</label>
          <input
            id="name"
            v-model="name"
            type="text"
            placeholder="Enter your name"
            required
          />
        </div>

        <div class="form-group">
          <label for="email">Email:</label>
          <input
            id="email"
            v-model="email"
            type="email"
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
            placeholder="Enter your password"
            required
          />
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirm Password:</label>
          <input
            id="password_confirmation"
            v-model="passwordConfirmation"
            type="password"
            placeholder="Confirm your password"
            required
          />
        </div>

        <div class="form-group">
          <label for="role">Role:</label>
          <select id="role" v-model="role" required>
            <option value="">Select Role</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
          </select>
        </div>

        <button type="submit" class="btn-primary">Register</button>
      </form>

      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

      <div class="login-link">
        <p
          >Already have an account? <router-link to="/login">Login here</router-link>
        </p>
      </div>
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
        alert(
          `Registration successful! User ${response.data.user.name} created with role ${response.data.user.role}.`
        );
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
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #f8f9fa;
}

.register-card {
  background: #fff;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
  text-align: center;
}

h1 {
  margin-bottom: 20px;
  font-size: 24px;
  color: #333;
}

.form-group {
  margin-bottom: 15px;
  text-align: left;
}

label {
  display: block;
  margin-bottom: 5px;
  font-size: 14px;
  color: #555;
}

input,
select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.3s;
}

input:focus,
select:focus {
  border-color: #007bff;
  outline: none;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.btn-primary {
  width: 100%;
  padding: 10px;
  background-color: #007bff;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.error-message {
  margin-top: 15px;
  color: #dc3545;
  font-size: 14px;
}

.login-link {
  margin-top: 20px;
  font-size: 14px;
}

.login-link a {
  color: #007bff;
  text-decoration: none;
}

.login-link a:hover {
  text-decoration: underline;
}
</style>
