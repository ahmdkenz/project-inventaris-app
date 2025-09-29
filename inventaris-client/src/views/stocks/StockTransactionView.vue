<template>
  <AppLayout>
    <div>
      <h1 class="page-title">Transaksi Stok</h1>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '../../components/layout/AppLayout.vue';

export default {
  name: "StockTransactionView",
  components: {
    AppLayout
  }
};
</script>

<style scoped>
@import "../../styles/minimal-stock.css";
</style>
