<template>
  <div class="stock-adjustment">
    <!-- Header -->
    <div class="page-header">
      <h1>Stock Adjustment</h1>
      <div class="actions">
        <button @click="refreshData" class="btn btn-secondary">
          Refresh
        </button>
      </div>
    </div>

    <!-- Adjustment Form -->
    <div class="adjustment-form">
      <h2>Create Stock Adjustment</h2>
      <form @submit.prevent="createAdjustment">
        <div class="form-row">
          <div class="form-group">
            <label for="product">Product:</label>
            <select id="product" v-model="adjustment.productId" required>
              <option value="">Select Product</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }} (Current Stock: {{ product.stock }})
              </option>
            </select>
          </div>
          <div class="form-group">
            <label for="type">Adjustment Type:</label>
            <select id="type" v-model="adjustment.type" required>
              <option value="increase">Increase Stock</option>
              <option value="decrease">Decrease Stock</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input 
              id="quantity" 
              v-model="adjustment.quantity" 
              type="number" 
              min="1" 
              required 
            />
          </div>
          <div class="form-group">
            <label for="reason">Reason:</label>
            <select id="reason" v-model="adjustment.reason" required>
              <option value="damaged">Damaged Goods</option>
              <option value="expired">Expired</option>
              <option value="recount">Stock Recount</option>
              <option value="return">Customer Return</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="notes">Notes (Optional):</label>
          <textarea 
            id="notes" 
            v-model="adjustment.notes" 
            rows="3" 
            placeholder="Additional notes..."
          ></textarea>
        </div>
        <button type="submit" class="btn btn-primary">
          Create Adjustment
        </button>
      </form>
    </div>

    <!-- Recent Adjustments -->
    <div class="recent-adjustments">
      <h2>Recent Stock Adjustments</h2>
      <div class="table-container">
        <table class="adjustments-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Product</th>
              <th>Type</th>
              <th>Quantity</th>
              <th>Reason</th>
              <th>User</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="adj in recentAdjustments" :key="adj.id">
              <td>{{ formatDate(adj.created_at) }}</td>
              <td>{{ adj.product_name }}</td>
              <td>
                <span :class="`type ${adj.type}`">
                  {{ adj.type === 'increase' ? '+' : '-' }}{{ adj.quantity }}
                </span>
              </td>
              <td>{{ adj.quantity }}</td>
              <td>{{ adj.reason }}</td>
              <td>{{ adj.user_name }}</td>
              <td>
                <span :class="`status ${adj.status}`">{{ adj.status }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../services/axios';

export default {
  name: 'StockAdjustmentView',
  data() {
    return {
      products: [],
      adjustment: {
        productId: '',
        type: 'increase',
        quantity: '',
        reason: 'recount',
        notes: ''
      },
      recentAdjustments: [],
      user: {}
    };
  },
  async mounted() {
    this.loadUserData();
    await this.loadProducts();
    await this.loadRecentAdjustments();
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
          { id: 1, name: 'Laptop Dell', stock: 15 },
          { id: 2, name: 'Mouse Wireless', stock: 5 },
          { id: 3, name: 'T-Shirt', stock: 50 },
          { id: 4, name: 'Programming Book', stock: 8 }
        ];
      } catch (error) {
        console.error('Error loading products:', error);
      }
    },
    async loadRecentAdjustments() {
      try {
        // Mock data - replace with actual API call
        this.recentAdjustments = [
          {
            id: 1,
            product_name: 'Laptop Dell',
            type: 'decrease',
            quantity: 2,
            reason: 'damaged',
            user_name: this.user.name || 'Admin',
            status: 'approved',
            created_at: new Date().toISOString()
          },
          {
            id: 2,
            product_name: 'Mouse Wireless',
            type: 'increase',
            quantity: 10,
            reason: 'recount',
            user_name: this.user.name || 'Staff',
            status: 'pending',
            created_at: new Date(Date.now() - 3600000).toISOString()
          }
        ];
      } catch (error) {
        console.error('Error loading adjustments:', error);
      }
    },
    async createAdjustment() {
      try {
        const adjustmentData = {
          ...this.adjustment,
          user_id: this.user.id,
          created_at: new Date().toISOString()
        };
        
        // Mock API call - replace with actual implementation
        console.log('Creating adjustment:', adjustmentData);
        
        // Reset form
        this.adjustment = {
          productId: '',
          type: 'increase',
          quantity: '',
          reason: 'recount',
          notes: ''
        };
        
        await this.loadRecentAdjustments();
        alert('Stock adjustment created successfully!');
      } catch (error) {
        console.error('Error creating adjustment:', error);
        alert('Error creating adjustment. Please try again.');
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleString();
    },
    async refreshData() {
      await this.loadProducts();
      await this.loadRecentAdjustments();
    }
  }
};
</script>

<style scoped>
@import "../../styles/stock-adjustment.css";
@import "../../styles/stocks.css";
</style>
