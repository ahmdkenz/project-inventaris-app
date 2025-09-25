<template>
  <div class="stock-adjustment">
    <h1>Stock Adjustment</h1>
    <p>Manage stock levels for products.</p>
    <div class="back-link">
      <router-link to="/dashboard">← Back to Dashboard</router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StockAdjustmentView'
};
</script>

<style scoped>
.stock-adjustment {
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
