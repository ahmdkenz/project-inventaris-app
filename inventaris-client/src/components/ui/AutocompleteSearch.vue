<template>
  <div class="autocomplete-search" v-click-outside="hideResults">
    <div class="search-input-wrapper">
      <input
        type="text"
        class="search-input"
        :placeholder="placeholder"
        v-model="query"
        @input="onInput"
        @keydown.down.prevent="navigateDown"
        @keydown.up.prevent="navigateUp"
        @keydown.enter="selectResult"
        @focus="showResults = true"
        ref="searchInput"
      />
      <div class="search-icon">
        <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <div v-else class="loading-spinner"></div>
      </div>
      <button v-if="query" @click="clearSearch" class="clear-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
    
    <div v-if="showResults && (results.length > 0 || noResults)" class="results-container">
      <div v-if="noResults" class="no-results">No results found</div>
      <ul v-else class="results-list">
        <li 
          v-for="(result, index) in results" 
          :key="result[valueKey]"
          :class="{ 'active': index === selectedIndex }"
          @click="selectResultByIndex(index)"
          class="result-item"
        >
          <slot name="result-item" :result="result">
            <div class="result-content">
              {{ result[labelKey] }}
              <span v-if="result.description" class="result-description">{{ result.description }}</span>
            </div>
          </slot>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AutocompleteSearch',
  props: {
    placeholder: {
      type: String,
      default: 'Search...'
    },
    minChars: {
      type: Number,
      default: 2
    },
    valueKey: {
      type: String,
      default: 'id'
    },
    labelKey: {
      type: String,
      default: 'name'
    },
    debounceTime: {
      type: Number,
      default: 300
    }
  },
  data() {
    return {
      query: '',
      results: [],
      selectedIndex: -1,
      showResults: false,
      loading: false,
      noResults: false,
      debounceTimeout: null
    };
  },
  methods: {
    onInput() {
      clearTimeout(this.debounceTimeout);
      
      if (this.query.length < this.minChars) {
        this.results = [];
        this.showResults = false;
        this.noResults = false;
        return;
      }
      
      this.loading = true;
      this.debounceTimeout = setTimeout(() => {
        this.search();
      }, this.debounceTime);
    },
    
    async search() {
      try {
        this.$emit('search', this.query, (results) => {
          this.results = results || [];
          this.noResults = this.results.length === 0;
          this.selectedIndex = -1;
          this.showResults = true;
          this.loading = false;
        });
      } catch (error) {
        console.error('Search error:', error);
        this.results = [];
        this.noResults = true;
        this.loading = false;
      }
    },
    
    navigateDown() {
      if (this.results.length === 0) return;
      this.selectedIndex = (this.selectedIndex + 1) % this.results.length;
    },
    
    navigateUp() {
      if (this.results.length === 0) return;
      this.selectedIndex = (this.selectedIndex - 1 + this.results.length) % this.results.length;
    },
    
    selectResult() {
      if (this.selectedIndex >= 0 && this.selectedIndex < this.results.length) {
        this.selectResultByIndex(this.selectedIndex);
      } else if (this.results.length > 0) {
        this.selectResultByIndex(0);
      }
    },
    
    selectResultByIndex(index) {
      const selected = this.results[index];
      if (selected) {
        this.query = selected[this.labelKey];
        this.$emit('select', selected);
        this.hideResults();
      }
    },
    
    hideResults() {
      this.showResults = false;
    },
    
    clearSearch() {
      this.query = '';
      this.results = [];
      this.showResults = false;
      this.noResults = false;
      this.selectedIndex = -1;
      this.$emit('clear');
      this.$refs.searchInput.focus();
    },
    
    focus() {
      this.$refs.searchInput.focus();
    }
  },
  directives: {
    'click-outside': {
      mounted(el, binding) {
        el.clickOutsideEvent = function(event) {
          if (!(el == event.target || el.contains(event.target))) {
            binding.value();
          }
        };
        document.addEventListener('click', el.clickOutsideEvent);
      },
      unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent);
      }
    }
  }
};
</script>

<style scoped>
.autocomplete-search {
  position: relative;
  width: 100%;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input {
  width: 100%;
  padding: 10px 16px 10px 40px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 15px;
  line-height: 1.5;
  transition: all 0.2s ease;
}

.search-input:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
  outline: none;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
}

.clear-button {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 4px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.clear-button:hover {
  background-color: #f3f4f6;
}

.results-container {
  position: absolute;
  width: 100%;
  max-height: 300px;
  overflow-y: auto;
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  margin-top: 4px;
  z-index: 10;
}

.no-results {
  padding: 16px;
  text-align: center;
  color: #6b7280;
}

.results-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.result-item {
  padding: 12px 16px;
  cursor: pointer;
  border-bottom: 1px solid #f3f4f6;
}

.result-item:last-child {
  border-bottom: none;
}

.result-item.active, .result-item:hover {
  background-color: #f9fafb;
}

.result-content {
  display: flex;
  flex-direction: column;
}

.result-description {
  font-size: 12px;
  color: #6b7280;
  margin-top: 2px;
}

.loading-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(79, 70, 229, 0.3);
  border-radius: 50%;
  border-top-color: #4f46e5;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>