<template>
  <div class="order-detail">
    <h1>Order Detail #{{ $route.params.id }}</h1>
    <p>View detailed information about this order.</p>
    <div class="back-link">
      <router-link to="/dashboard">← Back to Dashboard</router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OrderDetailView'
};
</script>

<style scoped>
.order-detail {
  padding: 2rem;
}
.back-link {
  margin-top: 2rem;
}
.back-link a {
  color: #007bff;
  text-decoration: none;
}
</style>
