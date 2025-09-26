<template>
  <div class="settings-container">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <h1>⚙️ System Settings</h1>
        <p class="subtitle">Configure your application preferences and system options</p>
      </div>
      <div class="header-actions">
        <button @click="resetToDefaults" class="btn btn-secondary">
          🔄 Reset to Defaults
        </button>
        <button @click="saveAllSettings" :disabled="saving" class="btn btn-primary">
          <span v-if="saving">💾</span>
          {{ saving ? 'Saving...' : '💾 Save Changes' }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Loading settings...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <div class="error-message">
        <h3>⚠️ Error Loading Settings</h3>
        <p>{{ error }}</p>
        <button @click="loadSettings" class="btn btn-primary">Try Again</button>
      </div>
    </div>

  <!-- Settings Content -->
  <div v-else class="settings-content compact-sections">
      <!-- General Settings -->
      <div class="settings-section card">
        <div class="card-header">
          <h3>🏢 General Settings</h3>
        </div>
        <div class="card-content">
          <div class="form-grid">
            <div class="form-group">
              <label for="companyName">Company Name</label>
              <input 
                id="companyName"
                v-model="settings.general.companyName"
                type="text"
                class="form-control"
                placeholder="Enter company name"
              >
            </div>
            <div class="form-group">
              <label for="companyEmail">Company Email</label>
              <input 
                id="companyEmail"
                v-model="settings.general.companyEmail"
                type="email"
                class="form-control"
                placeholder="Enter company email"
              >
            </div>
            <div class="form-group">
              <label for="companyPhone">Company Phone</label>
              <input 
                id="companyPhone"
                v-model="settings.general.companyPhone"
                type="tel"
                class="form-control"
                placeholder="Enter company phone"
              >
            </div>
            <div class="form-group">
              <label for="timezone">Timezone</label>
              <select id="timezone" v-model="settings.general.timezone" class="form-control">
                <option value="UTC">UTC</option>
                <option value="America/New_York">Eastern Time</option>
                <option value="America/Chicago">Central Time</option>
                <option value="America/Denver">Mountain Time</option>
                <option value="America/Los_Angeles">Pacific Time</option>
                <option value="Asia/Jakarta">Jakarta Time</option>
              </select>
            </div>
            <div class="form-group">
              <label for="dateFormat">Date Format</label>
              <select id="dateFormat" v-model="settings.general.dateFormat" class="form-control">
                <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                <option value="YYYY-MM-DD">YYYY-MM-DD</option>
              </select>
            </div>
            <div class="form-group">
              <label for="currency">Currency</label>
              <select id="currency" v-model="settings.general.currency" class="form-control">
                <option value="USD">USD ($)</option>
                <option value="EUR">EUR (€)</option>
                <option value="GBP">GBP (£)</option>
                <option value="IDR">IDR (Rp)</option>
                <option value="JPY">JPY (¥)</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="companyAddress">Company Address</label>
            <textarea 
              id="companyAddress"
              v-model="settings.general.companyAddress"
              class="form-control"
              rows="3"
              placeholder="Enter full company address"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Inventory Settings -->
      <div class="settings-section card">
        <div class="card-header">
          <h3>📦 Inventory Settings</h3>
        </div>
        <div class="card-content">
          <div class="form-grid">
            <div class="form-group">
              <label for="defaultMinStock">Default Minimum Stock Level</label>
              <input 
                id="defaultMinStock"
                v-model.number="settings.inventory.defaultMinStock"
                type="number"
                min="0"
                class="form-control"
                placeholder="10"
              >
            </div>
            <div class="form-group">
              <label for="lowStockThreshold">Low Stock Warning Threshold</label>
              <input 
                id="lowStockThreshold"
                v-model.number="settings.inventory.lowStockThreshold"
                type="number"
                min="0"
                class="form-control"
                placeholder="5"
              >
            </div>
            <div class="form-group">
              <label for="autoGenerateSku">Auto Generate SKU</label>
              <div class="toggle-container">
                <input 
                  id="autoGenerateSku"
                  v-model="settings.inventory.autoGenerateSku"
                  type="checkbox"
                  class="toggle-input"
                >
                <label for="autoGenerateSku" class="toggle-label">
                  <span class="toggle-switch"></span>
                  <span class="toggle-text">{{ settings.inventory.autoGenerateSku ? 'Enabled' : 'Disabled' }}</span>
                </label>
              </div>
            </div>
            <div class="form-group">
              <label for="stockAlerts">Enable Stock Alerts</label>
              <div class="toggle-container">
                <input 
                  id="stockAlerts"
                  v-model="settings.inventory.stockAlerts"
                  type="checkbox"
                  class="toggle-input"
                >
                <label for="stockAlerts" class="toggle-label">
                  <span class="toggle-switch"></span>
                  <span class="toggle-text">{{ settings.inventory.stockAlerts ? 'Enabled' : 'Disabled' }}</span>
                </label>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="skuPrefix">SKU Prefix</label>
            <input 
              id="skuPrefix"
              v-model="settings.inventory.skuPrefix"
              type="text"
              class="form-control"
              placeholder="PRD"
              maxlength="5"
            >
            <small class="form-hint">Used when auto-generating SKUs</small>
          </div>
        </div>
      </div>

      <!-- Notification Settings -->
      <div class="settings-section card">
        <div class="card-header">
          <h3>🔔 Notification Settings</h3>
        </div>
        <div class="card-content">
          <div class="form-grid">
            <div class="form-group">
              <label for="emailNotifications">Email Notifications</label>
              <div class="toggle-container">
                <input 
                  id="emailNotifications"
                  v-model="settings.notifications.emailNotifications"
                  type="checkbox"
                  class="toggle-input"
                >
                <label for="emailNotifications" class="toggle-label">
                  <span class="toggle-switch"></span>
                  <span class="toggle-text">{{ settings.notifications.emailNotifications ? 'Enabled' : 'Disabled' }}</span>
                </label>
              </div>
            </div>
            <div class="form-group">
              <label for="pushNotifications">Push Notifications</label>
              <div class="toggle-container">
                <input 
                  id="pushNotifications"
                  v-model="settings.notifications.pushNotifications"
                  type="checkbox"
                  class="toggle-input"
                >
                <label for="pushNotifications" class="toggle-label">
                  <span class="toggle-switch"></span>
                  <span class="toggle-text">{{ settings.notifications.pushNotifications ? 'Enabled' : 'Disabled' }}</span>
                </label>
              </div>
            </div>
            <div class="form-group">
              <label for="smsNotifications">SMS Notifications</label>
              <div class="toggle-container">
                <input 
                  id="smsNotifications"
                  v-model="settings.notifications.smsNotifications"
                  type="checkbox"
                  class="toggle-input"
                >
                <label for="smsNotifications" class="toggle-label">
                  <span class="toggle-switch"></span>
                  <span class="toggle-text">{{ settings.notifications.smsNotifications ? 'Enabled' : 'Disabled' }}</span>
                </label>
              </div>
            </div>
          </div>
          
          <div class="notification-types">
            <h4>Notification Types</h4>
            <div class="checkbox-list">
              <div class="checkbox-item">
                <input 
                  id="notifyLowStock"
                  v-model="settings.notifications.types.lowStock"
                  type="checkbox"
                  class="checkbox-input"
                >
                <label for="notifyLowStock" class="checkbox-label">
                  📦 Low stock alerts
                </label>
              </div>
              <div class="checkbox-item">
                <input 
                  id="notifyNewOrders"
                  v-model="settings.notifications.types.newOrders"
                  type="checkbox"
                  class="checkbox-input"
                >
                <label for="notifyNewOrders" class="checkbox-label">
                  🛍️ New order notifications
                </label>
              </div>
              <div class="checkbox-item">
                <input 
                  id="notifySystemUpdates"
                  v-model="settings.notifications.types.systemUpdates"
                  type="checkbox"
                  class="checkbox-input"
                >
                <label for="notifySystemUpdates" class="checkbox-label">
                  🔄 System update notifications
                </label>
              </div>
              <div class="checkbox-item">
                <input 
                  id="notifyReports"
                  v-model="settings.notifications.types.reports"
                  type="checkbox"
                  class="checkbox-input"
                >
                <label for="notifyReports" class="checkbox-label">
                  📊 Report generation notifications
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Security Settings (Admin Only) -->
      <div v-if="user.role === 'admin'" class="settings-section card">
        <div class="card-header">
          <h3>🔒 Security Settings</h3>
        </div>
        <div class="card-content">
          <div class="form-grid">
            <div class="form-group">
              <label for="sessionTimeout">Session Timeout (minutes)</label>
              <input 
                id="sessionTimeout"
                v-model.number="settings.security.sessionTimeout"
                type="number"
                min="15"
                max="1440"
                class="form-control"
                placeholder="60"
              >
            </div>
            <div class="form-group">
              <label for="maxLoginAttempts">Max Login Attempts</label>
              <input 
                id="maxLoginAttempts"
                v-model.number="settings.security.maxLoginAttempts"
                type="number"
                min="3"
                max="10"
                class="form-control"
                placeholder="5"
              >
            </div>
            <div class="form-group">
              <label for="passwordMinLength">Minimum Password Length</label>
              <input 
                id="passwordMinLength"
                v-model.number="settings.security.passwordMinLength"
                type="number"
                min="6"
                max="50"
                class="form-control"
                placeholder="8"
              >
            </div>
            <div class="form-group">
              <label for="requireTwoFactor">Require Two-Factor Authentication</label>
              <div class="toggle-container">
                <input 
                  id="requireTwoFactor"
                  v-model="settings.security.requireTwoFactor"
                  type="checkbox"
                  class="toggle-input"
                >
                <label for="requireTwoFactor" class="toggle-label">
                  <span class="toggle-switch"></span>
                  <span class="toggle-text">{{ settings.security.requireTwoFactor ? 'Required' : 'Optional' }}</span>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Backup Settings (Admin Only) -->
      <div v-if="user.role === 'admin'" class="settings-section card">
        <div class="card-header">
          <h3>💾 Backup & Maintenance</h3>
        </div>
        <div class="card-content">
          <div class="form-grid">
            <div class="form-group">
              <label for="autoBackup">Enable Automatic Backups</label>
              <div class="toggle-container">
                <input 
                  id="autoBackup"
                  v-model="settings.backup.autoBackup"
                  type="checkbox"
                  class="toggle-input"
                >
                <label for="autoBackup" class="toggle-label">
                  <span class="toggle-switch"></span>
                  <span class="toggle-text">{{ settings.backup.autoBackup ? 'Enabled' : 'Disabled' }}</span>
                </label>
              </div>
            </div>
            <div class="form-group">
              <label for="backupFrequency">Backup Frequency</label>
              <select id="backupFrequency" v-model="settings.backup.frequency" class="form-control">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
              </select>
            </div>
            <div class="form-group">
              <label for="retainBackups">Retain Backups (days)</label>
              <input 
                id="retainBackups"
                v-model.number="settings.backup.retainDays"
                type="number"
                min="7"
                max="365"
                class="form-control"
                placeholder="30"
              >
            </div>
          </div>
          
          <div class="backup-actions">
            <button @click="createBackup" :disabled="creatingBackup" class="btn btn-secondary">
              <span v-if="creatingBackup">💾</span>
              {{ creatingBackup ? 'Creating Backup...' : '💾 Create Backup Now' }}
            </button>
            <button @click="downloadBackup" class="btn btn-info">
              📥 Download Latest Backup
            </button>
          </div>
        </div>
      </div>

      <!-- System Information -->
      <div class="settings-section card">
        <div class="card-header">
          <h3>ℹ️ System Information</h3>
        </div>
        <div class="card-content">
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Application Version</span>
              <span class="info-value">{{ systemInfo.version }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Database Version</span>
              <span class="info-value">{{ systemInfo.databaseVersion }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Last Backup</span>
              <span class="info-value">{{ formatDate(systemInfo.lastBackup) }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Server Status</span>
              <span class="info-value" :class="{ 'status-online': systemInfo.serverStatus === 'online' }">
                {{ systemInfo.serverStatus }}
              </span>
            </div>
            <div class="info-item">
              <span class="info-label">Total Users</span>
              <span class="info-value">{{ systemInfo.totalUsers }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Total Products</span>
              <span class="info-value">{{ systemInfo.totalProducts }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="success-banner">
      <p>✅ {{ successMessage }}</p>
    </div>
  </div>
</template>

<script>
import axios from '../../services/axios';

export default {
  name: "SystemSettingsView",
  data() {
    return {
      loading: false,
      saving: false,
      creatingBackup: false,
      error: null,
      successMessage: '',
      user: {},
      settings: {
        general: {
          companyName: '',
          companyEmail: '',
          companyPhone: '',
          companyAddress: '',
          timezone: 'UTC',
          dateFormat: 'MM/DD/YYYY',
          currency: 'USD'
        },
        inventory: {
          defaultMinStock: 10,
          lowStockThreshold: 5,
          autoGenerateSku: true,
          stockAlerts: true,
          skuPrefix: 'PRD'
        },
        notifications: {
          emailNotifications: true,
          pushNotifications: true,
          smsNotifications: false,
          types: {
            lowStock: true,
            newOrders: true,
            systemUpdates: true,
            reports: false
          }
        },
        security: {
          sessionTimeout: 60,
          maxLoginAttempts: 5,
          passwordMinLength: 8,
          requireTwoFactor: false
        },
        backup: {
          autoBackup: true,
          frequency: 'weekly',
          retainDays: 30
        }
      },
      systemInfo: {
        version: '1.0.0',
        databaseVersion: 'MySQL 8.0',
        lastBackup: null,
        serverStatus: 'online',
        totalUsers: 0,
        totalProducts: 0
      }
    };
  },
  async mounted() {
    this.loadUserData();
    await this.loadSettings();
    await this.loadSystemInfo();
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

    async loadSettings() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.get('/settings');
        if (response.data?.settings) {
          this.settings = { ...this.settings, ...response.data.settings };
        }
      } catch (error) {
        console.error('Error loading settings:', error);
        this.error = error.response?.data?.message || 'Failed to load settings';
      } finally {
        this.loading = false;
      }
    },

    async loadSystemInfo() {
      try {
        const response = await axios.get('/system-info');
        if (response.data?.info) {
          this.systemInfo = { ...this.systemInfo, ...response.data.info };
        }
      } catch (error) {
        console.error('Error loading system info:', error);
      }
    },

    async saveAllSettings() {
      this.saving = true;
      this.successMessage = '';

      try {
        await axios.post('/settings', { settings: this.settings });
        this.successMessage = 'Settings saved successfully!';
        
        setTimeout(() => {
          this.successMessage = '';
        }, 3000);
      } catch (error) {
        console.error('Error saving settings:', error);
        this.$toast?.error(error.response?.data?.message || 'Failed to save settings');
      } finally {
        this.saving = false;
      }
    },

    async resetToDefaults() {
      if (!confirm('Are you sure you want to reset all settings to default values?')) {
        return;
      }

      try {
        await axios.post('/settings/reset');
        await this.loadSettings();
        this.successMessage = 'Settings reset to defaults successfully!';
        
        setTimeout(() => {
          this.successMessage = '';
        }, 3000);
      } catch (error) {
        console.error('Error resetting settings:', error);
        this.$toast?.error(error.response?.data?.message || 'Failed to reset settings');
      }
    },

    async createBackup() {
      this.creatingBackup = true;

      try {
        await axios.post('/system/backup');
        this.successMessage = 'Backup created successfully!';
        await this.loadSystemInfo(); // Refresh system info
        
        setTimeout(() => {
          this.successMessage = '';
        }, 3000);
      } catch (error) {
        console.error('Error creating backup:', error);
        this.$toast?.error(error.response?.data?.message || 'Failed to create backup');
      } finally {
        this.creatingBackup = false;
      }
    },

    async downloadBackup() {
      try {
        const response = await axios.get('/system/backup/latest', {
          responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `inventory_backup_${new Date().toISOString().split('T')[0]}.sql`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        this.successMessage = 'Backup downloaded successfully!';
        setTimeout(() => {
          this.successMessage = '';
        }, 3000);
      } catch (error) {
        console.error('Error downloading backup:', error);
        this.$toast?.error(error.response?.data?.message || 'Failed to download backup');
      }
    },

    formatDate(dateString) {
      if (!dateString) return 'Never';
      return new Date(dateString).toLocaleString();
    }
  }
};
</script>

<style scoped>
@import "../../styles/layout.css";
@import "../../styles/settings.css";
@import "../../styles/settings-additional.css";
@import "../../styles/layout-enhancements.css";
@import "../../styles/responsive-fixes.css";

/* Enhance card sizing in settings */
.settings-section.card {
  display: flex;
  flex-direction: column;
}

.card-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

/* Make sure toggles don't overflow */
.toggle-container {
  flex-wrap: wrap;
}
</style>
