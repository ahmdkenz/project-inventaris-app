<template>
  <AppLayout>
    <template #header>
     </template>
    
    <div class="card">
      <div class="card-header">
        <div class="actions flex-between">
          <router-link 
            v-if="user.role === 'admin'" 
            to="/products/create" 
            class="btn btn-primary"
          >
            Add New Product
          </router-link>
          <button 
            @click="refreshProducts" 
            class="btn btn-secondary" 
            :disabled="isRefreshing"
          >
            <span v-if="isRefreshing">Refreshing...</span>
            <span v-else>Refresh</span>
          </button>
        </div>
      </div>
    
    <!-- Refresh Notification -->
    <div v-if="showRefreshMessage" class="refresh-message">
      <span>✅ Product list has been updated</span>
      <span v-if="lastUpdate" class="update-time">Last updated: {{ formatUpdateTime() }}</span>
    </div>

    <!-- Search and Filter -->
    <div class="filters">
      <div class="search-box">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search products..." 
          @input="handleSearch"
        >
      </div>
      <div class="filter-group">
        <select v-model="categoryFilter" @change="handleFilter">
          <option value="">All Categories</option>
          <option v-for="category in categories" :key="category" :value="category">
            {{ category }}
          </option>
        </select>
        <select v-model="statusFilter" @change="handleFilter">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
    </div>

    <!-- Product Table -->
    <div class="table-container">
      <table class="product-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>SKU</th>
            <th>Category</th>
            <th>Purchase Price</th>
            <th>Selling Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in paginatedProducts" :key="product.id">
            <td>{{ product.id }}</td>
            <td>{{ product.name }}</td>
            <td>{{ product.description }}</td>
            <td>{{ product.sku }}</td>
            <td>{{ product.category }}</td>
            <td>${{ product.purchase_price }}</td>
            <td>${{ product.selling_price }}</td>
            <td :class="{ 'low-stock': product.stock < 10 }">{{ product.stock }}</td>
            <td>
              <span :class="`status ${product.status}`">{{ product.status }}</span>
            </td>
            <td>
              <div class="action-buttons">
                <router-link 
                  :to="`/products/${product.id}`" 
                  class="btn btn-sm btn-info"
                >
                  View
                </router-link>
                <router-link 
                  v-if="user.role === 'admin'"
                  :to="`/products/${product.id}/edit`" 
                  class="btn btn-sm btn-warning"
                  title="Edit produk ini"
                >
                  Edit
                </router-link>
                <button 
                  v-if="user.role === 'admin'"
                  @click="deleteProduct(product.id)"
                  class="btn btn-sm btn-danger"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination">
      <button 
        @click="previousPage" 
        :disabled="currentPage <= 1"
        class="btn btn-secondary"
      >
        Previous
      </button>
      <span class="page-info">
        Page {{ currentPage }} of {{ totalPages }}
      </span>
      <button 
        @click="nextPage" 
        :disabled="currentPage >= totalPages"
        class="btn btn-secondary"
      >
        Next
      </button>
    </div>
    </div>
  </AppLayout>
</template>

<script>
import axios from '@/services/axios';
import AppLayout from '@/components/layout/AppLayout.vue';

export default {
  name: 'ProductListView',
  components: {
    AppLayout
  },
  data() {
    return {
      products: [],
      filteredProducts: [],
      searchQuery: '',
      categoryFilter: '',
      statusFilter: '',
      categories: ['Electronics', 'Clothing', 'Books', 'Home & Garden'],
      currentPage: 1,
      itemsPerPage: 10,
      user: {},
      lastUpdate: null,
      isRefreshing: false,
      showRefreshMessage: false
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.filteredProducts.length / this.itemsPerPage);
    },
    paginatedProducts() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredProducts.slice(start, end);
    }
  },
  async mounted() {
    this.loadUserData();
    await this.loadProducts();
    
    // Setup auto refresh interval
    this._refreshInterval = setInterval(() => {
      this.refreshProducts(true);
    }, 30000); // Auto refresh every 30 seconds
  },
  
  beforeUnmount() {
    if (this._refreshInterval) {
      clearInterval(this._refreshInterval);
    }
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    async loadProducts() {
      try {
        const response = await axios.get('/products', {
          params: {
            search: this.searchQuery,
            category: this.categoryFilter,
            status: this.statusFilter,
            page: this.currentPage,
            per_page: this.itemsPerPage,
            sort_by: 'updated_at',
            sort_order: 'desc'
          }
        });
        
        if (response.data && response.data.data) {
          this.products = response.data.data;
          this.filteredProducts = [...this.products];
          // Extract unique categories from products
          this.categories = [...new Set(this.products.map(product => product.category).filter(Boolean))];
          
          // Save last update timestamp
          this.lastUpdate = new Date();
        }
      } catch (error) {
        console.error('Error loading products:', error);
        // Only use fallback mock data if no products are loaded yet
        if (!this.products.length) {
          this.products = [
            { id: 1, name: 'Laptop Dell', category: 'Electronics', purchase_price: 800, selling_price: 999, stock: 15, status: 'active' },
            { id: 2, name: 'Mouse Wireless', category: 'Electronics', purchase_price: 15, selling_price: 25, stock: 5, status: 'active' },
            { id: 3, name: 'T-Shirt', category: 'Clothing', purchase_price: 10, selling_price: 20, stock: 50, status: 'active' },
            { id: 4, name: 'Programming Book', category: 'Books', purchase_price: 30, selling_price: 45, stock: 8, status: 'inactive' }
          ];
          this.filteredProducts = [...this.products];
        }
      }
    },
    handleSearch() {
      this.filterProducts();
    },
    handleFilter() {
      this.filterProducts();
    },
    filterProducts() {
      let filtered = [...this.products];
      
      if (this.searchQuery) {
        filtered = filtered.filter(product => 
          product.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          product.category.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
      }
      
      if (this.categoryFilter) {
        filtered = filtered.filter(product => product.category === this.categoryFilter);
      }
      
      if (this.statusFilter) {
        filtered = filtered.filter(product => product.status === this.statusFilter);
      }
      
      this.filteredProducts = filtered;
      this.currentPage = 1;
    },
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
    async refreshProducts(silent = false) {
      if (!silent) {
        // Show loading indicator only if manually refreshed
        this.isRefreshing = true;
      }
      
      await this.loadProducts();
      
      if (!silent) {
        // Show success message
        this.showRefreshMessage = true;
        this.isRefreshing = false;
        
        // Hide message after 3 seconds
        setTimeout(() => {
          this.showRefreshMessage = false;
        }, 3000);
      }
    },
    async deleteProduct(id) {
      console.log('Deleting product with ID:', id); // Log ID produk yang akan dihapus
      if (confirm('Are you sure you want to delete this product?')) {
        try {
          const response = await axios.delete(`/products/${id}`);
          console.log('Delete response:', response.data); // Log respons dari API
          await this.loadProducts();
        } catch (error) {
          console.error('Error deleting product:', error.response || error.message);
          alert('Failed to delete product. Please try again.');
        }
      }
    },
    
    formatUpdateTime() {
      if (!this.lastUpdate) return '';
      return this.lastUpdate.toLocaleTimeString();
    }
  }
};
</script>

<style>
@import "@/styles/products.css";
</style>
