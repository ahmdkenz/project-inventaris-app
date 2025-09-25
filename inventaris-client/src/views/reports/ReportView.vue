<template>
  <div class="reports">
    <!-- Header -->
    <div class="page-header">
      <h1>Reports Dashboard</h1>
      <div class="actions">
        <button @click="refreshReports" class="btn btn-secondary">
          Refresh Data
        </button>
        <button v-if="user.role === 'admin'" @click="exportAllReports" class="btn btn-primary">
          Export All Reports
        </button>
      </div>
    </div>

    <!-- Report Cards -->
    <div class="report-cards">
      <!-- Sales Report Card -->
      <div class="report-card">
        <div class="card-header">
          <h3>📊 Sales Report</h3>
          <span class="period">This Month</span>
        </div>
        <div class="card-content">
          <div class="metric">
            <span class="value">${{ salesData.totalRevenue.toLocaleString() }}</span>
            <span class="label">Total Revenue</span>
          </div>
          <div class="metric">
            <span class="value">{{ salesData.totalOrders }}</span>
            <span class="label">Total Orders</span>
          </div>
          <div class="actions">
            <router-link to="/reports/sales" class="btn btn-sm btn-info">
              View Details
            </router-link>
            <button @click="exportReport('sales')" class="btn btn-sm btn-secondary">
              Export
            </button>
          </div>
        </div>
      </div>

      <!-- Stock Report Card -->
      <div class="report-card">
        <div class="card-header">
          <h3>📦 Stock Report</h3>
          <span class="period">Current</span>
        </div>
        <div class="card-content">
          <div class="metric">
            <span class="value">{{ stockData.totalProducts }}</span>
            <span class="label">Total Products</span>
          </div>
          <div class="metric">
            <span class="value warning">{{ stockData.lowStockItems }}</span>
            <span class="label">Low Stock Items</span>
          </div>
          <div class="actions">
            <router-link to="/reports/stock" class="btn btn-sm btn-info">
              View Details
            </router-link>
            <button @click="exportReport('stock')" class="btn btn-sm btn-secondary">
              Export
            </button>
          </div>
        </div>
      </div>

      <!-- Financial Report Card (Admin Only) -->
      <div v-if="user.role === 'admin'" class="report-card">
        <div class="card-header">
          <h3>💰 Financial Report</h3>
          <span class="period">This Quarter</span>
        </div>
        <div class="card-content">
          <div class="metric">
            <span class="value">${{ financialData.profit.toLocaleString() }}</span>
            <span class="label">Net Profit</span>
          </div>
          <div class="metric">
            <span class="value">${{ financialData.expenses.toLocaleString() }}</span>
            <span class="label">Total Expenses</span>
          </div>
          <div class="actions">
            <router-link to="/reports/financial" class="btn btn-sm btn-info">
              View Details
            </router-link>
            <button @click="exportReport('financial')" class="btn btn-sm btn-secondary">
              Export
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Analytics -->
    <div class="analytics-section">
      <div class="analytics-card">
        <h3>📈 Quick Analytics</h3>
        <div class="analytics-grid">
          <div class="analytics-item">
            <span class="analytics-label">Top Selling Product</span>
            <span class="analytics-value">{{ analytics.topProduct }}</span>
          </div>
          <div class="analytics-item">
            <span class="analytics-label">Average Order Value</span>
            <span class="analytics-value">${{ analytics.avgOrderValue }}</span>
          </div>
          <div class="analytics-item">
            <span class="analytics-label">Total Customers</span>
            <span class="analytics-value">{{ analytics.totalCustomers }}</span>
          </div>
          <div class="analytics-item">
            <span class="analytics-label">Stock Turnover Rate</span>
            <span class="analytics-value">{{ analytics.stockTurnover }}%</span>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="activities-card">
        <h3>🔔 Recent Activities</h3>
        <div class="activities-list">
          <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
            <div class="activity-icon">{{ activity.icon }}</div>
            <div class="activity-content">
              <p class="activity-text">{{ activity.description }}</p>
              <span class="activity-time">{{ formatDate(activity.timestamp) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Custom Report Generator (Admin Only) -->
    <div v-if="user.role === 'admin'" class="custom-report">
      <h3>🔧 Custom Report Generator</h3>
      <form @submit.prevent="generateCustomReport" class="custom-report-form">
        <div class="form-row">
          <div class="form-group">
            <label for="reportType">Report Type:</label>
            <select id="reportType" v-model="customReport.type">
              <option value="sales">Sales Analysis</option>
              <option value="inventory">Inventory Analysis</option>
              <option value="customer">Customer Analysis</option>
              <option value="financial">Financial Analysis</option>
            </select>
          </div>
          <div class="form-group">
            <label for="dateRange">Date Range:</label>
            <select id="dateRange" v-model="customReport.range">
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="quarter">This Quarter</option>
              <option value="year">This Year</option>
              <option value="custom">Custom Range</option>
            </select>
          </div>
        </div>
        <div v-if="customReport.range === 'custom'" class="form-row">
          <div class="form-group">
            <label for="startDate">Start Date:</label>
            <input id="startDate" v-model="customReport.startDate" type="date">
          </div>
          <div class="form-group">
            <label for="endDate">End Date:</label>
            <input id="endDate" v-model="customReport.endDate" type="date">
          </div>
        </div>
        <button type="submit" class="btn btn-primary">
          Generate Report
        </button>
      </form>
    </div>
  </div>
</template>

<script>
import axios from '../../services/axios';

export default {
  name: 'ReportView',
  data() {
    return {
      user: {},
      salesData: {
        totalRevenue: 125000,
        totalOrders: 342
      },
      stockData: {
        totalProducts: 150,
        lowStockItems: 8
      },
      financialData: {
        profit: 45000,
        expenses: 80000
      },
      analytics: {
        topProduct: 'Laptop Dell XPS',
        avgOrderValue: 365,
        totalCustomers: 89,
        stockTurnover: 85
      },
      recentActivities: [
        {
          id: 1,
          icon: '📊',
          description: 'Monthly sales report generated',
          timestamp: new Date().toISOString()
        },
        {
          id: 2,
          icon: '📦',
          description: 'Stock adjustment completed for 5 items',
          timestamp: new Date(Date.now() - 3600000).toISOString()
        },
        {
          id: 3,
          icon: '💰',
          description: 'New order #1234 worth $1,200',
          timestamp: new Date(Date.now() - 7200000).toISOString()
        }
      ],
      customReport: {
        type: 'sales',
        range: 'month',
        startDate: '',
        endDate: ''
      }
    };
  },
  mounted() {
    this.loadUserData();
    this.loadReportData();
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    async loadReportData() {
      try {
        // Mock API calls - replace with actual implementation
        console.log('Loading report data...');
      } catch (error) {
        console.error('Error loading report data:', error);
      }
    },
    async refreshReports() {
      await this.loadReportData();
    },
    async exportReport(type) {
      try {
        console.log(`Exporting ${type} report...`);
        alert(`${type.charAt(0).toUpperCase() + type.slice(1)} report exported successfully!`);
      } catch (error) {
        console.error(`Error exporting ${type} report:`, error);
      }
    },
    async exportAllReports() {
      try {
        console.log('Exporting all reports...');
        alert('All reports exported successfully!');
      } catch (error) {
        console.error('Error exporting reports:', error);
      }
    },
    async generateCustomReport() {
      try {
        console.log('Generating custom report:', this.customReport);
        alert('Custom report generated successfully!');
      } catch (error) {
        console.error('Error generating custom report:', error);
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleString();
    }
  }
};
</script>

<style scoped>
@import "../../styles/reports.css";
</style>
