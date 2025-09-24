<template>
  <canvas ref="barChart"></canvas>
</template>

<script>
import { Chart } from "chart.js";

export default {
  name: "BarChart",
  props: {
    data: Object,
    options: Object,
  },
  mounted() {
    new Chart(this.$refs.barChart, {
      type: "bar",
      data: this.data,
      options: this.options,
    });
  },
};
</script>

<style scoped>
canvas {
  max-width: 100%;
}
</style>
