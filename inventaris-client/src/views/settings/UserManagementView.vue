<template>
  <div class="user-management">
    <h1>User Management</h1>
    <p class="admin-only">Admin Only - Manage system users and permissions.</p>
    <div class="back-link">
      <router-link to="/admin/dashboard">← Back to Admin Dashboard</router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserManagementView'
};
</script>

<style scoped>
.user-management {
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
