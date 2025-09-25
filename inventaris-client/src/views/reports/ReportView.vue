<template>
  <div class="reports">
    <h1>Reports</h1>
    <p>Generate and view various reports.</p>
    <div class="report-links">
      <router-link to="/reports/sales" class="report-btn">Sales Report</router-link>
      <router-link to="/reports/stock" class="report-btn">Stock Report</router-link>
    </div>
    <div class="back-link">
      <router-link to="/dashboard">← Back to Dashboard</router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ReportView'
};
</script>

<style scoped>
.reports {
  padding: 2rem;
}
.report-links {
  display: flex;
  gap: 1rem;
  margin: 2rem 0;
}
.report-btn {
  padding: 0.75rem 1.5rem;
  background: #007bff;
  color: white;
  text-decoration: none;
  border-radius: 4px;
}
.report-btn:hover {
  background: #0056b3;
}
.back-link {
  margin-top: 2rem;
}
.back-link a {
  color: #007bff;
  text-decoration: none;
}
</style>
