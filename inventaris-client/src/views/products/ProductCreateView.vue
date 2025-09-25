<template>
  <div class="product-create">
    <h1>Create New Product</h1>
    <div class="back-link">
      <router-link to="/products">← Back to Products</router-link>
    </div>
    <form @submit.prevent="createProduct">
      <div class="form-group">
        <label for="name">Product Name:</label>
        <input id="name" v-model="product.name" type="text" required />
      </div>
      
      <div class="form-group">
        <label for="description">Description:</label>
        <textarea id="description" v-model="product.description" rows="3"></textarea>
      </div>
      
      <div class="form-group">
        <label for="price">Price:</label>
        <input id="price" v-model="product.price" type="number" step="0.01" required />
      </div>
      
      <div class="form-group">
        <label for="stock">Initial Stock:</label>
        <input id="stock" v-model="product.stock" type="number" required />
      </div>
      
      <button type="submit" class="submit-btn">Create Product</button>
    </form>
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
        price: '',
        stock: ''
      }
    };
  },
  methods: {
    async createProduct() {
      try {
        await axios.post('/products', this.product);
        this.$router.push('/products');
      } catch (error) {
        console.error('Error creating product:', error);
      }
    }
  }
};
</script>

<style scoped>
.product-create {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
}

.back-link {
  margin-bottom: 2rem;
}

.back-link a {
  color: #007bff;
  text-decoration: none;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.submit-btn {
  background: #28a745;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.submit-btn:hover {
  background: #218838;
}
</style>
