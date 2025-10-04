<template>
  <div class="advanced-filters">
    <div class="filter-header">
      <h3>{{ title }}</h3>
      <div class="filter-actions">
        <button @click="clearFilters" class="clear-btn">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="15" y1="9" x2="9" y2="15"></line>
              <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
          </span>
          Clear
        </button>
        <button @click="applyFilters" class="apply-btn">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </span>
          Apply
        </button>
      </div>
    </div>
    
    <div class="filter-body">
      <slot></slot>
      
      <div v-if="hasActiveFilters" class="active-filters">
        <div class="active-filters-title">Active filters:</div>
        <div class="active-filters-list">
          <div v-for="(value, key) in activeFilters" :key="key" class="active-filter-tag">
            {{ formatFilterLabel(key) }}: {{ formatFilterValue(key, value) }}
            <button @click="removeFilter(key)" class="remove-filter">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdvancedFilters',
  props: {
    title: {
      type: String,
      default: 'Filters'
    },
    initialFilters: {
      type: Object,
      default: () => ({})
    },
    filterLabels: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      filters: { ...this.initialFilters },
      activeFilters: {}
    };
  },
  computed: {
    hasActiveFilters() {
      return Object.keys(this.activeFilters).length > 0;
    }
  },
  methods: {
    updateFilter(key, value) {
      this.$set(this.filters, key, value);
    },
    
    applyFilters() {
      // Filter out empty values
      const appliedFilters = {};
      for (const [key, value] of Object.entries(this.filters)) {
        if (value !== null && value !== undefined && value !== '' && 
            !(Array.isArray(value) && value.length === 0)) {
          appliedFilters[key] = value;
        }
      }
      
      this.activeFilters = { ...appliedFilters };
      this.$emit('apply', appliedFilters);
    },
    
    clearFilters() {
      this.filters = {};
      this.activeFilters = {};
      this.$emit('clear');
    },
    
    removeFilter(key) {
      const newActiveFilters = { ...this.activeFilters };
      delete newActiveFilters[key];
      this.activeFilters = newActiveFilters;
      
      this.$set(this.filters, key, null);
      this.$emit('remove', key);
      this.$emit('apply', this.activeFilters);
    },
    
    formatFilterLabel(key) {
      if (this.filterLabels[key]) {
        return this.filterLabels[key];
      }
      return key.split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
    },
    
    formatFilterValue(key, value) {
      if (Array.isArray(value)) {
        return value.join(', ');
      } else if (typeof value === 'boolean') {
        return value ? 'Yes' : 'No';
      } else if (value instanceof Date) {
        return value.toLocaleDateString();
      }
      return value;
    }
  }
};
</script>

<style scoped>
.advanced-filters {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  margin-bottom: 20px;
}

.filter-header {
  padding: 16px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.filter-header h3 {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
  color: #333;
}

.filter-actions {
  display: flex;
  gap: 8px;
}

button {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.clear-btn {
  background-color: #f1f1f1;
  color: #666;
}

.clear-btn:hover {
  background-color: #e5e5e5;
}

.apply-btn {
  background-color: #4f46e5;
  color: white;
}

.apply-btn:hover {
  background-color: #4338ca;
}

.icon {
  display: flex;
  align-items: center;
}

.filter-body {
  padding: 16px;
}

.active-filters {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px dashed #eee;
}

.active-filters-title {
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 8px;
  color: #666;
}

.active-filters-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.active-filter-tag {
  background-color: #f1f1f1;
  border-radius: 4px;
  padding: 4px 8px;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.remove-filter {
  background: none;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2px;
  color: #666;
  border-radius: 50%;
}

.remove-filter:hover {
  background-color: rgba(0, 0, 0, 0.1);
}
</style>