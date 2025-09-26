<template>
  <div class="app-layout">
    <div class="main-content">
      <div class="card">
        <div class="card-header">
          <h1 class="card-title">Edit Product</h1>
          <router-link :to="`/products/${$route.params.id}`" class="btn btn-secondary">Cancel</router-link>
        </div>
        
        <div v-if="loading" class="loading-spinner">
          Loading product data...
        </div>

        <form v-else @submit.prevent="updateProduct" class="product-form">
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
              <label for="stock">Stock *</label>
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

            <div class="form-group">
              <label for="sku">SKU</label>
              <input 
                id="sku" 
                v-model="product.sku" 
                type="text" 
                class="form-control"
                placeholder="SKU will be generated if empty"
                readonly
              />
            </div>

            <div class="form-group">
              <label for="status">Status</label>
              <select id="status" v-model="product.status" class="form-control">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
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
              {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
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
  name: "ProductEditView",
  data() {
    return {
      product: {
        name: '',
        description: '',
        category: '',
        purchase_price: '',
        selling_price: '',
        stock: '',
        min_stock: 10,
        sku: '',
        status: 'active'
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
      loading: true,
      error: null
    };
  },
  async mounted() {
    await this.loadProductData();
    await this.loadCategories();
  },
  methods: {
    async loadProductData() {
      try {
        const productId = this.$route.params.id;
        const response = await axios.get(`/products/${productId}`);
        
        if (response.data && response.data.product) {
          const productData = response.data.product;
          
          // Set product data
          this.product = {
            name: productData.name || '',
            description: productData.description || '',
            category: productData.category || '',
            purchase_price: productData.purchase_price || 0,
            selling_price: productData.selling_price || 0,
            stock: productData.stock || 0,
            min_stock: productData.min_stock || 10,
            sku: productData.sku || '',
            status: productData.status || 'active'
          };
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
    async loadCategories() {
      try {
        const response = await axios.get('/products-categories');
        if (response.data && Array.isArray(response.data)) {
          // Add current category if not in the list
          if (!response.data.includes(this.product.category) && this.product.category) {
            this.categories = [...response.data, this.product.category].sort();
          } else {
            this.categories = response.data;
          }
        }
      } catch (error) {
        console.error('Error loading categories:', error);
        // Keep default categories if API fails
      }
    },
    async updateProduct() {
      this.isSubmitting = true;
      this.error = null;

      try {
        // Validation
        if (!this.product.name || !this.product.category || 
            !this.product.purchase_price || !this.product.selling_price) {
          this.error = 'Please fill all required fields';
          this.isSubmitting = false;
          return;
        }
        
        // Send update request to API
        const productId = this.$route.params.id;
        const response = await axios.put(`/products/${productId}`, {
          name: this.product.name,
          description: this.product.description,
          category: this.product.category,
          purchase_price: parseFloat(this.product.purchase_price),
          selling_price: parseFloat(this.product.selling_price),
          stock: parseInt(this.product.stock),
          min_stock: parseInt(this.product.min_stock),
          status: this.product.status
        });

        console.log('Update response:', response.data);

        // Redirect to product detail page on success
        this.$router.push(`/products/${productId}`);
      } catch (error) {
        console.error('Error updating product:', error.response || error.message);
        if (error.response?.status === 422) {
          const errs = error.response.data.errors || {};
          const firstKey = Object.keys(errs)[0];
          this.error = errs[firstKey]?.[0] || 'Form validation failed. Please review your input.';
        } else {
          this.error = error.response?.data?.message || 'Failed to update product. Please try again.';
        }
      } finally {
        this.isSubmitting = false;
      }
    }
  }
};
</script>

<style scoped>
@import "../../styles/product-edit.css";

.card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  margin: 1.5rem auto;
  max-width: 900px;
  overflow: hidden;
}

.card-header {
  align-items: center;
  background-color: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  padding: 1.5rem 2rem;
}

.card-title {
  color: #212529;
  font-size: 1.5rem;
  margin: 0;
}

.product-form {
  padding: 2rem;
}

.grid-2 {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  color: #495057;
  display: block;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.form-control {
  background-color: #fff;
  border: 1px solid #ced4da;
  border-radius: 4px;
  color: #495057;
  display: block;
  font-size: 1rem;
  line-height: 1.5;
  padding: 0.5rem 0.75rem;
  transition: border-color 0.15s ease-in-out;
  width: 100%;
}

.form-control:focus {
  border-color: #80bdff;
  outline: 0;
}

.btn {
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  padding: 0.5rem 1rem;
  text-align: center;
  transition: all 0.2s;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-lg {
  font-size: 1.1rem;
  padding: 0.75rem 1.5rem;
}

.flex-center {
  display: flex;
  justify-content: center;
  margin-top: 1.5rem;
}

.error {
  background-color: #f8d7da;
  border-radius: 4px;
  color: #721c24;
  margin: 1rem 2rem;
  padding: 1rem;
}

.loading-spinner {
  display: flex;
  justify-content: center;
  padding: 2rem;
}
</style>
