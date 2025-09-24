<template>
  <canvas ref="lineChart"></canvas>
</template>

<script>
import { Chart } from "chart.js";

export default {
  name: "LineChart",
  props: {
    data: Object,
    options: Object,
  },
  mounted() {
    new Chart(this.$refs.lineChart, {
      type: "line",
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
