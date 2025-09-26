<template>
  <div class="product-detail">
    <!-- Header -->
    <div class="page-header">
      <h1>Product Detail</h1>
      <div class="actions">
        <router-link to="/products" class="btn btn-secondary">
          ← Back to Products
        </router-link>
        <router-link 
          v-if="user.role === 'admin'" 
          :to="`/products/${productId}/edit`" 
          class="btn btn-primary"
        >
          Edit Product
        </router-link>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loader"></div>
      <p>Loading product data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <p>{{ error }}</p>
      <button @click="loadProductData" class="btn btn-primary">Retry</button>
    </div>

    <!-- Product Details -->
    <div v-else class="product-info-container">
      <div class="product-info-card">
        <h2>General Information</h2>
        <div class="info-grid">
          <div class="info-item">
            <label>Name:</label>
            <span>{{ product.name }}</span>
          </div>
          <div class="info-item">
            <label>SKU:</label>
            <span>{{ product.sku }}</span>
          </div>
          <div class="info-item">
            <label>Category:</label>
            <span>{{ product.category }}</span>
          </div>
          <div class="info-item">
            <label>Status:</label>
            <span :class="`status ${product.status}`">{{ product.status }}</span>
          </div>
          <div class="info-item full-width">
            <label>Description:</label>
            <p>{{ product.description || 'No description available' }}</p>
          </div>
        </div>
      </div>

      <div class="product-info-card">
        <h2>Stock & Price Information</h2>
        <div class="info-grid">
          <div class="info-item">
            <label>Current Stock:</label>
            <span :class="{ 'low-stock': product.stock <= product.min_stock }">
              {{ product.stock }} units
            </span>
          </div>
          <div class="info-item">
            <label>Minimum Stock:</label>
            <span>{{ product.min_stock }} units</span>
          </div>
          <div class="info-item">
            <label>Purchase Price:</label>
            <span>${{ formatPrice(product.purchase_price) }}</span>
          </div>
          <div class="info-item">
            <label>Selling Price:</label>
            <span>${{ formatPrice(product.selling_price) }}</span>
          </div>
          <div class="info-item">
            <label>Profit Margin:</label>
            <span>{{ calculateMargin() }}%</span>
          </div>
        </div>
      </div>

      <div class="product-info-card">
        <h2>Recent Transactions</h2>
        <div v-if="transactions.length === 0" class="no-data">
          No recent transactions found for this product.
        </div>
        <table v-else class="transactions-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Type</th>
              <th>Quantity</th>
              <th>Old Stock</th>
              <th>New Stock</th>
              <th>Reason</th>
              <th>User</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="transaction in transactions" :key="transaction.id">
              <td>{{ formatDate(transaction.created_at) }}</td>
              <td>
                <span :class="{'in': transaction.type === 'in', 'out': transaction.type === 'out'}">
                  {{ transaction.type === 'in' ? 'Stock In' : 'Stock Out' }}
                </span>
              </td>
              <td>{{ transaction.quantity }}</td>
              <td>{{ transaction.old_stock }}</td>
              <td>{{ transaction.new_stock }}</td>
              <td>{{ transaction.reason || 'N/A' }}</td>
              <td>{{ transaction.user ? transaction.user.name : 'System' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="actions-grid">
          <router-link to="/stocks/adjustment" class="action-card">
            <span class="icon">📦</span>
            <h3>Adjust Stock</h3>
            <p>Increase or decrease stock</p>
          </router-link>
          <router-link 
            v-if="user.role === 'admin'" 
            :to="`/products/${productId}/edit`" 
            class="action-card"
          >
            <span class="icon">✏️</span>
            <h3>Edit Product</h3>
            <p>Update product information</p>
          </router-link>
          <button 
            v-if="user.role === 'admin'"
            @click="deleteProduct"
            class="action-card delete"
          >
            <span class="icon">🗑️</span>
            <h3>Delete Product</h3>
            <p>Remove this product</p>
          </button>
          <router-link to="/reports" class="action-card">
            <span class="icon">📊</span>
            <h3>View Reports</h3>
            <p>See product performance</p>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../services/axios';

export default {
  name: "ProductDetailView",
  data() {
    return {
      productId: null,
      product: {
        name: '',
        sku: '',
        description: '',
        category: '',
        purchase_price: 0,
        selling_price: 0,
        stock: 0,
        min_stock: 10,
        status: 'active'
      },
      transactions: [],
      loading: true,
      error: null,
      user: {}
    };
  },
  async mounted() {
    this.productId = this.$route.params.id;
    this.loadUserData();
    await this.loadProductData();
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    async loadProductData() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`/products/${this.productId}`);
        
        if (response.data && response.data.product) {
          this.product = response.data.product;
          
          // Load transactions if available
          if (response.data.product.transactions) {
            this.transactions = response.data.product.transactions;
          }
        } else {
          this.error = 'Product data not found';
        }
      } catch (error) {
        console.error('Error loading product:', error);
        this.error = 'Failed to load product data. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleString();
    },
    formatPrice(price) {
      return parseFloat(price).toFixed(2);
    },
    calculateMargin() {
      if (!this.product.purchase_price || this.product.purchase_price <= 0) {
        return 'N/A';
      }
      
      const purchase = parseFloat(this.product.purchase_price);
      const selling = parseFloat(this.product.selling_price);
      const margin = ((selling - purchase) / purchase) * 100;
      
      return margin.toFixed(2);
    },
    async deleteProduct() {
      if (confirm(`Are you sure you want to delete "${this.product.name}"?`)) {
        try {
          await axios.delete(`/products/${this.productId}`);
          // Redirect to products list
          this.$router.push('/products');
        } catch (error) {
          console.error('Error deleting product:', error);
          alert('Failed to delete product. It might be referenced in transactions or orders.');
        }
      }
    }
  }
};
</script>

<style scoped>
.product-detail {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.actions {
  display: flex;
  gap: 1rem;
}

.btn {
  padding: 0.6rem 1.2rem;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  border: none;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
}

.loader {
  border: 5px solid #f3f3f3;
  border-top: 5px solid #007bff;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-container {
  background-color: #f8d7da;
  border-radius: 6px;
  padding: 1.5rem;
  text-align: center;
  margin: 2rem 0;
}

.product-info-container {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.product-info-card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
}

.product-info-card h2 {
  margin-top: 0;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #e9ecef;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.info-item {
  display: flex;
  flex-direction: column;
}

.full-width {
  grid-column: 1 / -1;
}

.info-item label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #6c757d;
}

.info-item span {
  font-size: 1.1rem;
}

.status {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status.active {
  background-color: #d4edda;
  color: #155724;
}

.status.inactive {
  background-color: #f8d7da;
  color: #721c24;
}

.low-stock {
  color: #dc3545;
  font-weight: 600;
}

.transactions-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}

.transactions-table th,
.transactions-table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #dee2e6;
}

.transactions-table th {
  background-color: #f8f9fa;
  font-weight: 600;
}

.in {
  color: #28a745;
  font-weight: 500;
}

.out {
  color: #dc3545;
  font-weight: 500;
}

.no-data {
  text-align: center;
  padding: 2rem 0;
  color: #6c757d;
}

.quick-actions {
  margin-top: 1rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 1rem;
}

.action-card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  padding: 1.5rem;
  text-align: center;
  text-decoration: none;
  color: inherit;
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: pointer;
  border: none;
  font-family: inherit;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.action-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.action-card .icon {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.action-card h3 {
  margin: 0;
  margin-bottom: 0.5rem;
}

.action-card p {
  margin: 0;
  color: #6c757d;
}

.action-card.delete:hover {
  background-color: #f8d7da;
}
</style>
