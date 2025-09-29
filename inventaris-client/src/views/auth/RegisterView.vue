<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <h1>Register</h1>
          <form @submit.prevent="handleRegister" class="auth-form">
            <div class="grid-2">
              <div class="form-group">
                <label for="name">Name:</label>
                <input
                  id="name"
                  v-model="name"
                  type="text"
                  class="form-control"
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

              <div class="form-group">
                <label for="password_confirmation">Confirm Password:</label>
                <input
                  id="password_confirmation"
                  v-model="passwordConfirmation"
                  type="password"
                  class="form-control"
                  placeholder="Confirm your password"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="role">Role:</label>
              <select id="role" v-model="role" class="form-control" required>
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block" :disabled="isSubmitting">
              {{ isSubmitting ? 'Creating Account...' : 'Register' }}
            </button>

            <div v-if="errorMessage" class="error">
              {{ errorMessage }}
            </div>

            <div v-if="successMessage" class="success">
              {{ successMessage }}
            </div>
          </form>

          <div class="auth-link">
            <p>Already have an account? <router-link to="/login">Login here</router-link></p>
          </div>
        </div>
      </div>
      
      <Footer />
    </div>
</template>

<script>
import axios from "@/services/axios";
import Footer from "@/components/layout/Footer.vue";

export default {
  components: {
    Footer
  },
  name: "RegisterView",
  data() {
    return {
      name: "",
      email: "",
      password: "",
      passwordConfirmation: "",
      role: "",
      errorMessage: "",
      successMessage: "",
      isSubmitting: false,
    };
  },
  methods: {
    async handleRegister() {
      this.isSubmitting = true;
      this.errorMessage = "";
      this.successMessage = "";
      
      // Validasi client-side
      if (this.password !== this.passwordConfirmation) {
        this.errorMessage = "Password and confirmation do not match.";
        this.isSubmitting = false;
        return;
      }

      if (this.password.length < 8) {
        this.errorMessage = "Password must be at least 8 characters long.";
        this.isSubmitting = false;
        return;
      }

      if (!this.role) {
        this.errorMessage = "Please select a role.";
        this.isSubmitting = false;
        return;
      }

      try {
        const response = await axios.post("/register", {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmation: this.passwordConfirmation,
          role: this.role,
        });
        
        console.log("Registration successful:", response.data);
        this.successMessage = `Registration successful! User ${response.data.user.name} created with role ${response.data.user.role}.`;
        
        // Redirect after 2 seconds
        setTimeout(() => {
          this.$router.push("/login");
        }, 2000);
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
      } finally {
        this.isSubmitting = false;
      }
    },
  },
};
</script>

<style scoped>
@import "@/styles/layout.css";
@import "@/styles/register.css";

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
