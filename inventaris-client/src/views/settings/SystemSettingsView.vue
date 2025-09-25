<template>
  <div class="system-settings">
    <h1>System Settings</h1>
    <p class="admin-only">Admin Only - Configure system-wide settings.</p>
    <div class="back-link">
      <router-link to="/admin/dashboard">← Back to Admin Dashboard</router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SystemSettingsView'
};
</script>

<style scoped>
.system-settings {
  padding: 2rem;
}
.admin-only {
  color: #28a745;
  font-weight: bold;
}
.back-link {
  margin-top: 2rem;
}
.back-link a {
  color: #007bff;
  text-decoration: none;
}
</style>
