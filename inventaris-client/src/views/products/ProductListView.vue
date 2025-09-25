<template>
  <div class="product-list">
    <!-- Header -->
    <div class="page-header">
      <h1>Product Management</h1>
      <div class="actions">
        <router-link 
          v-if="user.role === 'admin'" 
          to="/products/create" 
          class="btn btn-primary"
        >
          Add New Product
        </router-link>
        <button @click="refreshProducts" class="btn btn-secondary">
          Refresh
        </button>
      </div>
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
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in paginatedProducts" :key="product.id">
            <td>{{ product.id }}</td>
            <td>{{ product.name }}</td>
            <td>{{ product.category }}</td>
            <td>${{ product.price }}</td>
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
</template>

<script>
import axios from '../../services/axios';

export default {
  name: 'ProductListView',
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
      user: {}
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
        // Mock data - replace with actual API call
        this.products = [
          { id: 1, name: 'Laptop Dell', category: 'Electronics', price: 999, stock: 15, status: 'active' },
          { id: 2, name: 'Mouse Wireless', category: 'Electronics', price: 25, stock: 5, status: 'active' },
          { id: 3, name: 'T-Shirt', category: 'Clothing', price: 20, stock: 50, status: 'active' },
          { id: 4, name: 'Programming Book', category: 'Books', price: 45, stock: 8, status: 'inactive' }
        ];
        this.filteredProducts = [...this.products];
      } catch (error) {
        console.error('Error loading products:', error);
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
    async refreshProducts() {
      await this.loadProducts();
    },
    async deleteProduct(id) {
      if (confirm('Are you sure you want to delete this product?')) {
        try {
          await axios.delete(`/products/${id}`);
          await this.loadProducts();
        } catch (error) {
          console.error('Error deleting product:', error);
        }
      }
    }
  }
};
</script>

<style scoped>
@import "../../styles/products.css";
</style>
