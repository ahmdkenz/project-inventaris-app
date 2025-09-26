<template>
  <div class="app-layout">
    <div class="main-content">
      <div class="card">
        <div class="card-header">
          <h1 class="card-title">Create New Product</h1>
          <router-link to="/products" class="btn btn-secondary">← Back to Products</router-link>
        </div>
        
        <form @submit.prevent="createProduct" class="product-form">
          <div class="grid-2">
            <div class="form-group">
              <label for="name">Product Name *</label>
              <input 
                id="name" 
                v-model="product.name" 
                type="text" 
                class="form-control"
                required 
                placeholder="Enter product name"
              />
            </div>
            
            <div class="form-group">
              <label for="category">Category *</label>
              <select id="category" v-model="product.category" class="form-control" required>
                <option value="">Select Category</option>
                <option v-for="category in categories" :key="category" :value="category">
                  {{ category }}
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="purchase_price">Purchase Price *</label>
              <input 
                id="purchase_price" 
                v-model="product.purchase_price" 
                type="number" 
                step="0.01" 
                class="form-control"
                required 
                placeholder="0.00"
              />
            </div>
            
            <div class="form-group">
              <label for="selling_price">Selling Price *</label>
              <input 
                id="selling_price" 
                v-model="product.selling_price" 
                type="number" 
                step="0.01" 
                class="form-control"
                required 
                placeholder="0.00"
              />
            </div>
            
            <div class="form-group">
              <label for="stock">Initial Stock *</label>
              <input 
                id="stock" 
                v-model="product.stock" 
                type="number" 
                class="form-control"
                required 
                placeholder="0"
              />
            </div>
            
            <div class="form-group">
              <label for="min_stock">Minimum Stock</label>
              <input 
                id="min_stock" 
                v-model="product.min_stock" 
                type="number" 
                class="form-control"
                placeholder="10"
              />
            </div>
          </div>
          
          <div class="form-group">
            <label for="description">Description</label>
            <textarea 
              id="description" 
              v-model="product.description" 
              rows="4"
              class="form-control"
              placeholder="Enter product description"
            ></textarea>
          </div>
          
          <div class="flex-center">
            <button type="submit" class="btn btn-primary btn-lg" :disabled="isSubmitting">
              {{ isSubmitting ? 'Creating...' : 'Create Product' }}
            </button>
          </div>
        </form>
        
        <div v-if="error" class="error">
          {{ error }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../services/axios';

export default {
  name: 'ProductCreateView',
  data() {
    return {
      product: {
        name: '',
        description: '',
        purchase_price: '',
        selling_price: '',
        stock: '',
        category: '',
        min_stock: 10
      },
      categories: [
        'Electronics',
        'Clothing', 
        'Books',
        'Home & Garden',
        'Sports',
        'Toys',
        'Automotive',
        'Health & Beauty'
      ],
      isSubmitting: false,
      error: null
    };
  },
  methods: {
    async createProduct() {
      this.isSubmitting = true;
      this.error = null;

      try {
        console.log('Sending product data:', this.product);
        const response = await axios.post('/products', {
          name: this.product.name,
          description: this.product.description || null,
          category: this.product.category,
          purchase_price: parseFloat(this.product.purchase_price),
          selling_price: parseFloat(this.product.selling_price),
          stock: Number(this.product.stock),
          min_stock: Number(this.product.min_stock || 10),
        });

        console.log('API response:', response.data);

        if (response.data) {
          this.$router.push('/products');
        }
      } catch (error) {
        console.error('Error creating product:', error.response || error.message);
        if (error.response) {
          console.error('Error details:', error.response.data);
        }
        // Show validation messages if present
        if (error.response?.status === 422) {
          const errs = error.response.data.errors || {};
          const firstKey = Object.keys(errs)[0];
          this.error = errs[firstKey]?.[0] || 'Form validation failed. Please review your input.';
        } else {
          this.error = error.response?.data?.message || 'Failed to create product. Please try again.';
        }
      } finally {
        this.isSubmitting = false;
      }
    }
  }
};
</script>

<style scoped>
@import "../../styles/product-create.css";
</style>
