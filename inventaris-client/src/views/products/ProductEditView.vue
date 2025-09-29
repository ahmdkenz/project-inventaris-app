<template>
  <AppLayout>
    <div class="product-edit-container">
      <div class="card">
        <div class="card-header">
          <h1 class="card-title">Edit Produk</h1>
          <div>
            <router-link :to="`/products/${$route.params.id}`" class="btn btn-secondary">Batal</router-link>
            <router-link to="/products" class="btn btn-secondary ml-2">← Kembali ke Daftar Produk</router-link>
          </div>
        </div>
        
        <div v-if="loading" class="loading-spinner">
          <div class="spinner"></div>
          <p>Memuat data produk...</p>
        </div>

        <form v-else @submit.prevent="updateProduct" class="product-form">
          <div class="grid-2">
            <div class="form-group">
              <label for="name">Nama Produk *</label>
              <input 
                id="name" 
                v-model="product.name" 
                type="text" 
                class="form-control"
                required 
                placeholder="Masukkan nama produk"
              />
            </div>
            
            <div class="form-group">
              <label for="category">Kategori *</label>
              <select id="category" v-model="product.category" class="form-control" required>
                <option value="">Pilih Kategori</option>
                <option v-for="category in categories" :key="category" :value="category">
                  {{ category }}
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="purchase_price">Harga Beli *</label>
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
              <label for="selling_price">Harga Jual *</label>
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
              <label for="stock">Stok *</label>
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
              <label for="min_stock">Stok Minimum</label>
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
                placeholder="SKU akan dibuat otomatis jika kosong"
                readonly
              />
            </div>

            <div class="form-group">
              <label for="status">Status</label>
              <select id="status" v-model="product.status" class="form-control">
                <option value="active">Aktif</option>
                <option value="inactive">Tidak Aktif</option>
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea 
              id="description" 
              v-model="product.description" 
              rows="4"
              class="form-control"
              placeholder="Masukkan deskripsi produk"
            ></textarea>
          </div>
          
          <div class="flex-center">
            <button type="submit" class="btn btn-primary btn-lg" :disabled="isSubmitting">
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </form>
        
        <div v-if="error" class="error">
          <div class="error-icon">⚠️</div>
          <div class="error-message">{{ error }}</div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import axios from '@/services/axios';
import AppLayout from '@/components/layout/AppLayout.vue';
import '@/styles/product-edit.css';
import '@/styles/responsive-fixes.css';

export default {
  name: "ProductEditView",
  components: {
    AppLayout
  },
  created() {
    console.log('ProductEditView created with route params:', this.$route.params);
  },
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
        'Elektronik',
        'Pakaian', 
        'Buku',
        'Rumah & Taman',
        'Olahraga',
        'Mainan',
        'Otomotif',
        'Kesehatan & Kecantikan'
      ],
      isSubmitting: false,
      loading: true,
      error: null
    };
  },
  async mounted() {
    console.log('ProductEditView mounted, ID:', this.$route.params.id);
    await this.loadProductData();
    await this.loadCategories();
  },
  methods: {
    async loadProductData() {
      try {
        const productId = this.$route.params.id;
        console.log('Loading product data for ID:', productId);
        
        if (!productId) {
          this.error = 'Product ID is missing. Please return to the product list and try again.';
          this.loading = false;
          return;
        }
        
        const response = await axios.get(`/products/${productId}`);
        console.log('API response:', response);
        
        if (response.data && response.data.product) {
          const productData = response.data.product;
          console.log('Product data loaded:', productData);
          
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
        } else if (response.data) {
          console.error('Invalid response format:', response.data);
          this.error = 'Received invalid product data format from server';
        } else {
          this.error = 'Product data not found';
        }
      } catch (error) {
        console.error('Error loading product:', error);
        if (error.response) {
          console.error('Error response:', error.response);
          this.error = `Failed to load product data: ${error.response.data?.message || error.response.statusText || 'Server error'}`;
        } else if (error.request) {
          this.error = 'Network error - server did not respond. Please check your connection.';
        } else {
          this.error = `Error loading product data: ${error.message}`;
        }
      } finally {
        this.loading = false;
      }
    },
    async loadCategories() {
      try {
        console.log('Loading product categories...');
        const response = await axios.get('/products-categories');
        console.log('Categories response:', response.data);
        
        if (response.data && Array.isArray(response.data)) {
          // Add current category if not in the list
          if (!response.data.includes(this.product.category) && this.product.category) {
            this.categories = [...response.data, this.product.category].sort();
          } else {
            this.categories = response.data;
          }
          console.log('Categories loaded successfully:', this.categories);
        } else if (response.data) {
          console.warn('Unexpected categories data format:', response.data);
          // Try to extract categories if the API returns a different format
          if (typeof response.data === 'object') {
            try {
              // Handle if categories are in a nested property
              if (response.data.categories && Array.isArray(response.data.categories)) {
                this.categories = response.data.categories;
              }
              // If categories are key-value pairs
              else {
                const extractedCategories = [];
                for (const key in response.data) {
                  if (typeof response.data[key] === 'string') {
                    extractedCategories.push(response.data[key]);
                  }
                }
                if (extractedCategories.length > 0) {
                  this.categories = extractedCategories.sort();
                }
              }
              console.log('Categories extracted from non-standard format:', this.categories);
            } catch (extractError) {
              console.error('Failed to extract categories from response:', extractError);
            }
          }
        }
      } catch (error) {
        console.error('Error loading categories:', error);
        if (error.response) {
          console.error('Error response details:', error.response.data);
        }
        // Keep default categories if API fails
        console.log('Using default categories:', this.categories);
      }
    },
    async updateProduct() {
      this.isSubmitting = true;
      this.error = null;

      try {
        // Extended validation
        if (!this.product.name) {
          this.error = 'Nama produk tidak boleh kosong';
          this.isSubmitting = false;
          return;
        }
        
        if (!this.product.category) {
          this.error = 'Kategori produk harus dipilih';
          this.isSubmitting = false;
          return;
        }
        
        if (!this.product.purchase_price) {
          this.error = 'Harga beli produk tidak boleh kosong';
          this.isSubmitting = false;
          return;
        }
        
        if (!this.product.selling_price) {
          this.error = 'Harga jual produk tidak boleh kosong';
          this.isSubmitting = false;
          return;
        }
        
        if (parseFloat(this.product.purchase_price) < 0) {
          this.error = 'Harga beli produk tidak boleh negatif';
          this.isSubmitting = false;
          return;
        }
        
        if (parseFloat(this.product.selling_price) < 0) {
          this.error = 'Harga jual produk tidak boleh negatif';
          this.isSubmitting = false;
          return;
        }
        
        if (parseInt(this.product.stock) < 0) {
          this.error = 'Stok produk tidak boleh negatif';
          this.isSubmitting = false;
          return;
        }
        
        // Send update request to API
        const productId = this.$route.params.id;
        console.log('Updating product with ID:', productId);
        
        if (!productId) {
          this.error = 'Product ID is missing. Please return to the product list and try again.';
          this.isSubmitting = false;
          return;
        }
        
        const productData = {
          name: this.product.name,
          description: this.product.description,
          category: this.product.category,
          purchase_price: parseFloat(this.product.purchase_price),
          selling_price: parseFloat(this.product.selling_price),
          stock: parseInt(this.product.stock),
          min_stock: parseInt(this.product.min_stock),
          status: this.product.status
        };
        
        console.log('Sending product data:', productData);
        const response = await axios.put(`/products/${productId}`, productData);

        console.log('Update response:', response.data);

        // Redirect to product detail page on success
        this.$router.push(`/products/${productId}`);
      } catch (error) {
        console.error('Error updating product:', error);
        if (error.response) {
          console.error('Error response details:', error.response.data);
          
          if (error.response.status === 422) {
            const errs = error.response.data.errors || {};
            if (Object.keys(errs).length > 0) {
              const firstKey = Object.keys(errs)[0];
              this.error = `Kesalahan validasi: ${errs[firstKey]?.[0]}`;
            } else {
              this.error = 'Validasi formulir gagal. Silakan periksa input Anda.';
            }
          } else if (error.response.status === 404) {
            this.error = 'Produk tidak ditemukan. Produk mungkin telah dihapus.';
          } else if (error.response.status === 403) {
            this.error = 'Anda tidak memiliki izin untuk mengedit produk ini.';
          } else {
            this.error = `Gagal memperbarui produk: ${error.response.data?.message || error.response.statusText || 'Kesalahan server'}`;
          }
        } else if (error.request) {
          this.error = 'Kesalahan jaringan - server tidak merespons. Silakan periksa koneksi Anda.';
        } else {
          this.error = `Gagal memperbarui produk: ${error.message}`;
        }
      } finally {
        this.isSubmitting = false;
      }
    }
  }
};
</script>

<style scoped>
/* Menghapus aturan inline CSS yang sudah dipindahkan */
</style>
