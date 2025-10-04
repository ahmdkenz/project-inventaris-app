<template>
  <div class="stat-card" :class="{ [`theme-${theme}`]: true }">
    <div class="card-icon" v-if="icon">
      <slot name="icon">
        <span class="default-icon" v-html="getDefaultIcon"></span>
      </slot>
    </div>
    <div class="card-content">
      <h3 class="card-title">{{ title }}</h3>
      <div class="card-value">{{ formattedValue }}</div>
      <div class="card-trend" v-if="showTrend">
        <div :class="['trend-indicator', getTrendClass]">
          <span class="trend-icon" v-html="getTrendIcon"></span>
          <span class="trend-value">{{ Math.abs(trend) }}%</span>
        </div>
        <span class="trend-period">{{ trendPeriod }}</span>
      </div>
      <div class="card-subtitle" v-if="subtitle">{{ subtitle }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StatCard',
  props: {
    title: {
      type: String,
      required: true
    },
    value: {
      type: [Number, String],
      required: true
    },
    format: {
      type: String,
      default: 'number', // 'number', 'currency', 'percent', 'custom'
    },
    formatOptions: {
      type: Object,
      default: () => ({})
    },
    subtitle: {
      type: String,
      default: ''
    },
    icon: {
      type: Boolean,
      default: true
    },
    iconType: {
      type: String,
      default: 'default' // 'default', 'revenue', 'users', 'orders', 'products'
    },
    theme: {
      type: String,
      default: 'blue' // 'blue', 'green', 'purple', 'orange', 'red'
    },
    trend: {
      type: Number,
      default: null
    },
    trendPeriod: {
      type: String,
      default: 'vs last period'
    },
    showTrend: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    formattedValue() {
      if (this.format === 'currency') {
        const options = {
          style: 'currency',
          currency: 'IDR',
          minimumFractionDigits: 0,
          maximumFractionDigits: 0,
          ...this.formatOptions
        };
        return new Intl.NumberFormat('id-ID', options).format(this.value);
      } else if (this.format === 'percent') {
        const options = {
          style: 'percent',
          minimumFractionDigits: 1,
          maximumFractionDigits: 1,
          ...this.formatOptions
        };
        return new Intl.NumberFormat('id-ID', options).format(this.value / 100);
      } else if (this.format === 'number') {
        const options = {
          minimumFractionDigits: 0,
          maximumFractionDigits: 0,
          ...this.formatOptions
        };
        return new Intl.NumberFormat('id-ID', options).format(this.value);
      }
      return this.value;
    },
    
    getDefaultIcon() {
      switch (this.iconType) {
        case 'revenue':
          return `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="16"></line>
            <line x1="8" y1="12" x2="16" y2="12"></line>
          </svg>`;
        case 'users':
          return `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>`;
        case 'orders':
          return `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
            <line x1="3" y1="9" x2="21" y2="9"></line>
            <line x1="9" y1="21" x2="9" y2="9"></line>
          </svg>`;
        case 'products':
          return `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            <polyline points="3.29 7 12 12 20.71 7"></polyline>
            <line x1="12" y1="22" x2="12" y2="12"></line>
          </svg>`;
        default:
          return `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
            <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
          </svg>`;
      }
    },
    
    getTrendClass() {
      if (this.trend === null) return '';
      return this.trend > 0 ? 'trend-up' : this.trend < 0 ? 'trend-down' : 'trend-neutral';
    },
    
    getTrendIcon() {
      if (this.trend === null) return '';
      if (this.trend > 0) {
        return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="18 15 12 9 6 15"></polyline>
        </svg>`;
      } else if (this.trend < 0) {
        return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>`;
      }
      return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>`;
    }
  }
};
</script>

<style scoped>
.stat-card {
  background-color: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  gap: 16px;
  transition: transform 0.2s, box-shadow 0.2s;
  position: relative;
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
}

.card-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  color: white;
  flex-shrink: 0;
}

.card-content {
  flex-grow: 1;
}

.card-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  margin-bottom: 4px;
}

.card-value {
  font-size: 1.75rem;
  font-weight: 600;
  margin-bottom: 4px;
}

.card-subtitle {
  font-size: 0.75rem;
  color: #6b7280;
}

.card-trend {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
  font-size: 0.75rem;
}

.trend-indicator {
  display: flex;
  align-items: center;
  gap: 2px;
  font-weight: 600;
}

.trend-up {
  color: #10B981;
}

.trend-down {
  color: #EF4444;
}

.trend-neutral {
  color: #6B7280;
}

.trend-period {
  color: #6b7280;
}

/* Themes */
.theme-blue .card-icon {
  background-color: rgba(59, 130, 246, 0.15);
  color: #3B82F6;
}

.theme-blue .card-value {
  color: #1E40AF;
}

.theme-green .card-icon {
  background-color: rgba(16, 185, 129, 0.15);
  color: #10B981;
}

.theme-green .card-value {
  color: #047857;
}

.theme-purple .card-icon {
  background-color: rgba(139, 92, 246, 0.15);
  color: #8B5CF6;
}

.theme-purple .card-value {
  color: #6D28D9;
}

.theme-orange .card-icon {
  background-color: rgba(245, 158, 11, 0.15);
  color: #F59E0B;
}

.theme-orange .card-value {
  color: #B45309;
}

.theme-red .card-icon {
  background-color: rgba(239, 68, 68, 0.15);
  color: #EF4444;
}

.theme-red .card-value {
  color: #B91C1C;
}

/* Before element for decoration */
.stat-card::before {
  content: "";
  position: absolute;
  top: 0;
  right: 0;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  transform: translate(50%, -50%);
  opacity: 0.05;
}

.theme-blue::before {
  background-color: #3B82F6;
}

.theme-green::before {
  background-color: #10B981;
}

.theme-purple::before {
  background-color: #8B5CF6;
}

.theme-orange::before {
  background-color: #F59E0B;
}

.theme-red::before {
  background-color: #EF4444;
}
</style>