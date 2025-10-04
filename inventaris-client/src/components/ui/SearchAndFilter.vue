<template>
  <div class="search-and-filter">
    <div class="search-bar">
      <AutocompleteSearch
        :placeholder="searchPlaceholder"
        @search="onSearch"
        @select="onSelectSearchResult"
        @clear="onClearSearch"
        ref="autocomplete"
      />
    </div>
    
    <div class="filter-toggle" @click="toggleFilters">
      <span>Filters</span>
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
      </svg>
    </div>
    
    <transition name="slide">
      <div v-if="showFilters" class="filters-panel">
        <AdvancedFilters
          title="Product Filters"
          :initialFilters="initialFilters"
          :filterLabels="filterLabels"
          @apply="onApplyFilters"
          @clear="onClearFilters"
        >
          <div class="filter-fields">
            <div class="filter-group">
              <label>Category</label>
              <select v-model="filters.category">
                <option value="">All Categories</option>
                <option v-for="category in categories" :key="category" :value="category">
                  {{ category }}
                </option>
              </select>
            </div>
            
            <div class="filter-group">
              <label>Status</label>
              <select v-model="filters.status">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="out_of_stock">Out of Stock</option>
              </select>
            </div>
            
            <div class="filter-group">
              <label>Price Range</label>
              <div class="price-range">
                <input 
                  type="number" 
                  placeholder="Min"
                  v-model.number="filters.min_price"
                  min="0"
                />
                <span>to</span>
                <input 
                  type="number" 
                  placeholder="Max"
                  v-model.number="filters.max_price"
                  min="0"
                />
              </div>
            </div>
            
            <div class="filter-group">
              <label>Stock</label>
              <select v-model="filters.stock_status">
                <option value="">All</option>
                <option value="in_stock">In Stock</option>
                <option value="low_stock">Low Stock</option>
                <option value="out_of_stock">Out of Stock</option>
              </select>
            </div>
          </div>
        </AdvancedFilters>
      </div>
    </transition>
  </div>
</template>

<script>
import AutocompleteSearch from './AutocompleteSearch.vue';
import AdvancedFilters from './AdvancedFilters.vue';

export default {
  name: 'SearchAndFilter',
  components: {
    AutocompleteSearch,
    AdvancedFilters
  },
  props: {
    searchPlaceholder: {
      type: String,
      default: 'Search products...'
    },
    categories: {
      type: Array,
      default: () => []
    },
    initialFilters: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      showFilters: false,
      filters: {
        category: '',
        status: '',
        min_price: null,
        max_price: null,
        stock_status: ''
      },
      filterLabels: {
        category: 'Category',
        status: 'Status',
        min_price: 'Minimum Price',
        max_price: 'Maximum Price',
        stock_status: 'Stock Status'
      }
    };
  },
  methods: {
    toggleFilters() {
      this.showFilters = !this.showFilters;
    },
    
    onSearch(query, callback) {
      // Emit the search event for parent component to handle
      this.$emit('search', query, callback);
    },
    
    onSelectSearchResult(result) {
      this.$emit('select-result', result);
    },
    
    onClearSearch() {
      this.$emit('clear-search');
    },
    
    onApplyFilters(filters) {
      this.$emit('apply-filters', filters);
    },
    
    onClearFilters() {
      this.filters = {
        category: '',
        status: '',
        min_price: null,
        max_price: null,
        stock_status: ''
      };
      this.$emit('clear-filters');
    },
    
    focus() {
      this.$refs.autocomplete.focus();
    }
  },
  created() {
    // Initialize filters with any passed in initialFilters
    this.filters = { ...this.filters, ...this.initialFilters };
  }
};
</script>

<style scoped>
.search-and-filter {
  width: 100%;
  margin-bottom: 24px;
}

.search-bar {
  margin-bottom: 12px;
}

.filter-toggle {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  color: #4b5563;
  transition: all 0.2s;
}

.filter-toggle:hover {
  background-color: #f3f4f6;
}

.filters-panel {
  margin-top: 12px;
}

.filter-fields {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-group label {
  font-size: 14px;
  color: #4b5563;
}

.filter-group select,
.filter-group input {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
}

.filter-group select:focus,
.filter-group input:focus {
  border-color: #4f46e5;
  outline: none;
  box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.2);
}

.price-range {
  display: flex;
  align-items: center;
  gap: 8px;
}

.price-range input {
  width: 100%;
}

.price-range span {
  color: #6b7280;
}

/* Transitions */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>