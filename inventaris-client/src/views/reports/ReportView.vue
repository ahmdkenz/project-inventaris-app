<template>
  <div class="reports-container">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <h1>📊 Reports Dashboard</h1>
        <p class="subtitle">Comprehensive analytics and reporting</p>
      </div>
      <div class="header-actions">
        <button 
          @click="refreshReports" 
          :disabled="loading"
          class="btn btn-secondary"
        >
          <span v-if="loading">🔄</span>
          {{ loading ? 'Loading...' : 'Refresh Data' }}
        </button>
        <button 
          v-if="user.role === 'admin'" 
          @click="exportAllReports" 
          class="btn btn-primary"
        >
          📤 Export All
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Loading reports data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <div class="error-message">
        <h3>⚠️ Error Loading Reports</h3>
        <p>{{ error }}</p>
        <button @click="loadReportData" class="btn btn-primary">Try Again</button>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else class="reports-content">

      <!-- Report Overview Cards -->
      <div class="reports-grid">
        <!-- Sales Report Card -->
        <div class="report-card">
          <div class="card-header">
            <div class="card-title">
              <h3>📊 Sales Report</h3>
              <span class="period">This Month</span>
            </div>
          </div>
          <div class="card-content">
            <div class="metrics-grid">
              <div class="metric">
                <span class="value">${{ formatCurrency(salesData.totalRevenue) }}</span>
                <span class="label">Total Revenue</span>
                <span class="trend positive" v-if="salesData.revenueGrowth > 0">
                  ↗️ +{{ salesData.revenueGrowth }}%
                </span>
              </div>
              <div class="metric">
                <span class="value">{{ salesData.totalOrders }}</span>
                <span class="label">Total Orders</span>
                <span class="trend positive" v-if="salesData.orderGrowth > 0">
                  ↗️ +{{ salesData.orderGrowth }}%
                </span>
              </div>
            </div>
            <div class="card-actions">
              <router-link to="/reports/sales" class="btn btn-sm btn-info">
                📋 View Details
              </router-link>
              <button @click="exportReport('sales')" class="btn btn-sm btn-secondary">
                📤 Export
              </button>
            </div>
          </div>
        </div>

        <!-- Stock Report Card -->
        <div class="report-card">
          <div class="card-header">
            <div class="card-title">
              <h3>📦 Stock Report</h3>
              <span class="period">Current Status</span>
            </div>
          </div>
          <div class="card-content">
            <div class="metrics-grid">
              <div class="metric">
                <span class="value">{{ stockData.totalProducts }}</span>
                <span class="label">Total Products</span>
              </div>
              <div class="metric">
                <span class="value" :class="{ 'warning': stockData.lowStockItems > 0 }">
                  {{ stockData.lowStockItems }}
                </span>
                <span class="label">Low Stock Items</span>
                <span v-if="stockData.lowStockItems > 0" class="trend warning">
                  ⚠️ Needs Attention
                </span>
              </div>
            </div>
            <div class="card-actions">
              <router-link to="/reports/stock" class="btn btn-sm btn-info">
                📋 View Details
              </router-link>
              <button @click="exportReport('stock')" class="btn btn-sm btn-secondary">
                📤 Export
              </button>
            </div>
          </div>
        </div>

        <!-- Financial Report Card (Admin Only) -->
        <div v-if="user.role === 'admin'" class="report-card">
          <div class="card-header">
            <div class="card-title">
              <h3>💰 Financial Report</h3>
              <span class="period">This Quarter</span>
            </div>
          </div>
          <div class="card-content">
            <div class="metrics-grid">
              <div class="metric">
                <span class="value">${{ formatCurrency(financialData.profit) }}</span>
                <span class="label">Net Profit</span>
                <span class="trend" :class="{ 'positive': financialData.profitGrowth > 0, 'negative': financialData.profitGrowth < 0 }">
                  {{ financialData.profitGrowth > 0 ? '↗️' : '↘️' }} {{ Math.abs(financialData.profitGrowth) }}%
                </span>
              </div>
              <div class="metric">
                <span class="value">${{ formatCurrency(financialData.expenses) }}</span>
                <span class="label">Total Expenses</span>
              </div>
            </div>
            <div class="card-actions">
              <router-link to="/reports/financial" class="btn btn-sm btn-info">
                📋 View Details
              </router-link>
              <button @click="exportReport('financial')" class="btn btn-sm btn-secondary">
                📤 Export
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Analytics and Activities Section -->
      <div class="analytics-section">
        <div class="analytics-card card">
          <div class="card-header">
            <h3>📈 Key Performance Indicators</h3>
          </div>
          <div class="card-content">
            <div class="kpi-grid">
              <div class="kpi-item">
                <div class="kpi-icon">🏆</div>
                <div class="kpi-content">
                  <span class="kpi-label">Top Selling Product</span>
                  <span class="kpi-value">{{ analytics.topProduct || 'N/A' }}</span>
                </div>
              </div>
              <div class="kpi-item">
                <div class="kpi-icon">💵</div>
                <div class="kpi-content">
                  <span class="kpi-label">Average Order Value</span>
                  <span class="kpi-value">${{ formatCurrency(analytics.avgOrderValue) }}</span>
                </div>
              </div>
              <div class="kpi-item">
                <div class="kpi-icon">👥</div>
                <div class="kpi-content">
                  <span class="kpi-label">Active Users</span>
                  <span class="kpi-value">{{ analytics.totalCustomers }}</span>
                </div>
              </div>
              <div class="kpi-item">
                <div class="kpi-icon">🔄</div>
                <div class="kpi-content">
                  <span class="kpi-label">Stock Turnover Rate</span>
                  <span class="kpi-value">{{ analytics.stockTurnover }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activities -->
        <div class="activities-card card">
          <div class="card-header">
            <h3>🔔 Recent Activities</h3>
            <button @click="loadRecentActivities" class="btn btn-sm btn-secondary">
              🔄 Refresh
            </button>
          </div>
          <div class="card-content">
            <div v-if="recentActivities.length === 0" class="empty-state">
              <p>No recent activities</p>
            </div>
            <div v-else class="activities-list">
              <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
                <div class="activity-avatar">{{ activity.icon }}</div>
                <div class="activity-details">
                  <p class="activity-description">{{ activity.description }}</p>
                  <div class="activity-meta">
                    <span class="activity-time">{{ formatTimeAgo(activity.timestamp) }}</span>
                    <span v-if="activity.user" class="activity-user">by {{ activity.user }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Custom Report Generator (Admin Only) -->
      <div v-if="user.role === 'admin'" class="custom-report-card card">
        <div class="card-header">
          <h3>🔧 Custom Report Generator</h3>
        </div>
        <div class="card-content">
          <form @submit.prevent="generateCustomReport" class="custom-report-form">
            <div class="form-grid">
              <div class="form-group">
                <label for="reportType">Report Type</label>
                <select 
                  id="reportType" 
                  v-model="customReport.type"
                  class="form-control"
                >
                  <option value="sales">📊 Sales Analysis</option>
                  <option value="inventory">📦 Inventory Analysis</option>
                  <option value="customer">👥 Customer Analysis</option>
                  <option value="financial">💰 Financial Analysis</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="dateRange">Date Range</label>
                <select 
                  id="dateRange" 
                  v-model="customReport.range"
                  class="form-control"
                >
                  <option value="today">Today</option>
                  <option value="week">This Week</option>
                  <option value="month">This Month</option>
                  <option value="quarter">This Quarter</option>
                  <option value="year">This Year</option>
                  <option value="custom">Custom Range</option>
                </select>
              </div>
            </div>
            
            <div v-if="customReport.range === 'custom'" class="form-grid">
              <div class="form-group">
                <label for="startDate">Start Date</label>
                <input 
                  id="startDate" 
                  v-model="customReport.startDate" 
                  type="date"
                  class="form-control"
                >
              </div>
              <div class="form-group">
                <label for="endDate">End Date</label>
                <input 
                  id="endDate" 
                  v-model="customReport.endDate" 
                  type="date"
                  class="form-control"
                >
              </div>
            </div>
            
            <div class="form-actions">
              <button 
                type="submit" 
                :disabled="generatingReport"
                class="btn btn-primary"
              >
                <span v-if="generatingReport">🔄 Generating...</span>
                <span v-else>📈 Generate Report</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../services/axios';

export default {
  name: 'ReportView',
  data() {
    return {
      loading: false,
      error: null,
      generatingReport: false,
      user: {},
      salesData: {
        totalRevenue: 0,
        totalOrders: 0,
        revenueGrowth: 0,
        orderGrowth: 0
      },
      stockData: {
        totalProducts: 0,
        lowStockItems: 0
      },
      financialData: {
        profit: 0,
        expenses: 0,
        profitGrowth: 0
      },
      analytics: {
        topProduct: null,
        avgOrderValue: 0,
        totalCustomers: 0,
        stockTurnover: 0
      },
      recentActivities: [],
      customReport: {
        type: 'sales',
        range: 'month',
        startDate: '',
        endDate: ''
      }
    };
  },
  async mounted() {
    this.loadUserData();
    await this.loadReportData();
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      } else {
        this.$router.push('/auth/login');
      }
    },

    async loadReportData() {
      this.loading = true;
      this.error = null;
      
      try {
        // Load dashboard stats
        const dashboardResponse = await axios.get('/api/dashboard/stats');
        if (dashboardResponse.data) {
          const stats = dashboardResponse.data;
          
          this.salesData = {
            totalRevenue: stats.totalRevenue || 0,
            totalOrders: stats.totalOrders || 0,
            revenueGrowth: stats.revenueGrowth || 0,
            orderGrowth: stats.orderGrowth || 0
          };
          
          this.stockData = {
            totalProducts: stats.totalProducts || 0,
            lowStockItems: stats.lowStockItems || 0
          };
          
          this.analytics = {
            topProduct: stats.topProduct || 'N/A',
            avgOrderValue: stats.avgOrderValue || 0,
            totalCustomers: stats.totalUsers || 0,
            stockTurnover: stats.stockTurnover || 0
          };
        }

        // Load recent activities
        await this.loadRecentActivities();

      } catch (error) {
        console.error('Error loading report data:', error);
        this.error = error.response?.data?.message || 'Failed to load report data';
      } finally {
        this.loading = false;
      }
    },

    async loadRecentActivities() {
      try {
        const response = await axios.get('/api/dashboard/activities');
        if (response.data?.activities) {
          this.recentActivities = response.data.activities.map(activity => ({
            id: activity.id,
            icon: this.getActivityIcon(activity.type),
            description: activity.description,
            timestamp: activity.created_at,
            user: activity.user?.name
          }));
        }
      } catch (error) {
        console.error('Error loading activities:', error);
        this.recentActivities = [];
      }
    },

    async refreshReports() {
      await this.loadReportData();
    },

    async exportReport(type) {
      try {
        // Create export request with date range
        const params = {
          type: type,
          format: 'xlsx',
          date_from: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
          date_to: new Date().toISOString().split('T')[0]
        };

        const response = await axios.get('/api/reports/export', {
          params: params,
          responseType: 'blob'
        });

        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `${type}_report_${new Date().toISOString().split('T')[0]}.xlsx`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        // Show success message
        this.$toast?.success(`${type.charAt(0).toUpperCase() + type.slice(1)} report exported successfully!`);
      } catch (error) {
        console.error(`Error exporting ${type} report:`, error);
        this.$toast?.error(error.response?.data?.message || 'Failed to export report');
      }
    },

    async exportAllReports() {
      try {
        const reportTypes = ['sales', 'stock'];
        if (this.user.role === 'admin') {
          reportTypes.push('financial');
        }

        for (const type of reportTypes) {
          await this.exportReport(type);
          // Small delay between exports
          await new Promise(resolve => setTimeout(resolve, 1000));
        }

        this.$toast?.success('All reports exported successfully!');
      } catch (error) {
        console.error('Error exporting all reports:', error);
        this.$toast?.error('Failed to export some reports');
      }
    },

    async generateCustomReport() {
      this.generatingReport = true;
      
      try {
        const params = {
          type: this.customReport.type,
          range: this.customReport.range,
          start_date: this.customReport.startDate,
          end_date: this.customReport.endDate
        };

        const response = await axios.post('/api/reports/custom', params);
        
        if (response.data?.report_url) {
          // Open generated report
          window.open(response.data.report_url, '_blank');
        }

        this.$toast?.success('Custom report generated successfully!');
      } catch (error) {
        console.error('Error generating custom report:', error);
        this.$toast?.error(error.response?.data?.message || 'Failed to generate custom report');
      } finally {
        this.generatingReport = false;
      }
    },

    getActivityIcon(type) {
      const icons = {
        'product_created': '📦',
        'product_updated': '✏️',
        'stock_adjustment': '📊',
        'order_created': '🛍️',
        'order_completed': '✅',
        'user_registered': '👤',
        'report_generated': '📋',
        'default': '🔔'
      };
      return icons[type] || icons.default;
    },

    formatCurrency(amount) {
      return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(amount || 0);
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleString();
    },

    formatTimeAgo(dateString) {
      const now = new Date();
      const date = new Date(dateString);
      const diff = now - date;
      
      const minutes = Math.floor(diff / 60000);
      const hours = Math.floor(diff / 3600000);
      const days = Math.floor(diff / 86400000);
      
      if (days > 0) return `${days} day${days > 1 ? 's' : ''} ago`;
      if (hours > 0) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
      if (minutes > 0) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
      return 'Just now';
    }
  }
};
</script>

<style scoped>
@import "../../styles/layout.css";
@import "../../styles/responsive-fixes.css";

/* Reports Container */
.reports-container {
  padding: var(--spacing-lg);
  max-width: 1400px;
  margin: 0 auto;
}

.reports-content {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-lg);
}

/* Reports Grid */
.reports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: var(--spacing-lg);
}

.report-card {
  background: var(--color-white);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--color-border);
  transition: all 0.2s ease;
}

.report-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-1px);
}

.card-header {
  padding: var(--spacing-md);
  border-bottom: 1px solid var(--color-border);
}

.card-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--color-text);
}

.period {
  font-size: 0.85rem;
  color: var(--color-text-secondary);
  background: var(--color-background-secondary);
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--border-radius-sm);
}

/* Metrics */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: var(--spacing-md);
  padding: var(--spacing-md);
}

.metric {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.metric .value {
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--color-primary);
  margin-bottom: var(--spacing-xs);
}

.metric .value.warning {
  color: var(--color-warning);
}

.metric .label {
  font-size: 0.85rem;
  color: var(--color-text-secondary);
  margin-bottom: var(--spacing-xs);
}

.trend {
  font-size: 0.75rem;
  padding: 2px 6px;
  border-radius: var(--border-radius-sm);
  font-weight: 600;
}

.trend.positive {
  background: rgba(34, 197, 94, 0.1);
  color: #16a34a;
}

.trend.negative {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.trend.warning {
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
}

/* Card Actions */
.card-actions {
  display: flex;
  gap: var(--spacing-sm);
  padding: var(--spacing-md);
  padding-top: 0;
  justify-content: center;
}

/* Analytics Section */
.analytics-section {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: var(--spacing-lg);
}

/* KPI Grid */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--spacing-md);
  padding: var(--spacing-md);
}

.kpi-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm);
  background: var(--color-background-secondary);
  border-radius: var(--border-radius-sm);
}

.kpi-icon {
  font-size: 1.5rem;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-white);
  border-radius: var(--border-radius-sm);
  box-shadow: var(--shadow-sm);
}

.kpi-content {
  flex: 1;
}

.kpi-label {
  display: block;
  font-size: 0.85rem;
  color: var(--color-text-secondary);
  margin-bottom: 2px;
}

.kpi-value {
  display: block;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--color-text);
}

/* Activities */
.activities-list {
  max-height: 400px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm);
  border-bottom: 1px solid var(--color-border);
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-avatar {
  font-size: 1.2rem;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-background-secondary);
  border-radius: 50%;
  flex-shrink: 0;
}

.activity-details {
  flex: 1;
  min-width: 0;
}

.activity-description {
  margin: 0 0 var(--spacing-xs) 0;
  font-size: 0.9rem;
  color: var(--color-text);
}

.activity-meta {
  display: flex;
  gap: var(--spacing-sm);
  align-items: center;
}

.activity-time {
  font-size: 0.8rem;
  color: var(--color-text-secondary);
}

.activity-user {
  font-size: 0.8rem;
  color: var(--color-text-secondary);
  font-style: italic;
}

/* Custom Report Form */
.custom-report-card {
  margin-top: var(--spacing-lg);
}

.custom-report-form {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--spacing-md);
}

.form-actions {
  display: flex;
  justify-content: center;
  padding-top: var(--spacing-sm);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: var(--spacing-xl);
  color: var(--color-text-secondary);
}

/* Responsive Design */
@media (max-width: 768px) {
  .reports-container {
    padding: var(--spacing-md);
  }
  
  .reports-grid {
    grid-template-columns: 1fr;
  }
  
  .analytics-section {
    grid-template-columns: 1fr;
  }
  
  .metrics-grid {
    grid-template-columns: 1fr 1fr;
  }
  
  .kpi-grid {
    grid-template-columns: 1fr;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .metrics-grid {
    grid-template-columns: 1fr;
  }
  
  .card-actions {
    flex-direction: column;
  }
}
</style>
