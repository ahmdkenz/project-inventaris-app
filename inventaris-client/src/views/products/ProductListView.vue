<template>
  <AppLayout>
    <template #header>
      <h1 class="page-title">Daftar Produk</h1>
     </template>
    
    <div class="card">
      <div class="card-header">
        <div class="actions flex-between">
          <router-link 
            v-if="user.role === 'admin'" 
            to="/products/create" 
            class="btn btn-primary"
          >
            Tambah Produk Baru
          </router-link>
          <button 
            @click="refreshProducts" 
            class="btn btn-secondary" 
            :disabled="isRefreshing"
          >
            <span v-if="isRefreshing">Memperbarui...</span>
            <span v-else>Refresh</span>
          </button>
        </div>
      </div>
    
    <!-- Refresh Notification -->
    <div v-if="showRefreshMessage" class="refresh-message">
      <span>✅ Daftar produk telah diperbarui</span>
      <span v-if="lastUpdate" class="update-time">Pembaruan terakhir: {{ formatUpdateTime() }}</span>
    </div>

    <!-- Search and Filter -->
    <div class="filters">
      <div class="search-box">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari produk..." 
          @input="handleSearch"
        >
      </div>
      <div class="filter-group">
        <select v-model="categoryFilter" @change="handleFilter">
          <option value="">Semua Kategori</option>
          <option v-for="category in categories" :key="category" :value="category">
            {{ category }}
          </option>
        </select>
        <select v-model="statusFilter" @change="handleFilter">
          <option value="">Semua Status</option>
          <option value="active">Aktif</option>
          <option value="inactive">Tidak Aktif</option>
        </select>
      </div>
    </div>

    <!-- Product Table -->
    <div class="table-container">
      <table class="product-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Deskripsi</th>
            <th>SKU</th>
            <th>Kategori</th>
            <th>Harga Beli</th>
            <th>Harga Jual</th>
            <th>Stok</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in paginatedProducts" :key="product.id" :class="{ 'inactive-row': product.status === 'inactive' }">
            <td>{{ product.id }}</td>
            <td>{{ product.name }}</td>
            <td>{{ product.description }}</td>
            <td>{{ product.sku }}</td>
            <td>{{ product.category }}</td>
            <td>Rp {{ formatNumber(product.purchase_price) }}</td>
            <td>Rp {{ formatNumber(product.selling_price) }}</td>
            <td :class="{ 'low-stock': product.stock < 10 }">{{ product.stock }}</td>
            <td>
              <span :class="`status ${product.status}`">{{ product.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}</span>
            </td>
            <td>
              <div class="action-buttons">
                <router-link 
                  :to="`/products/${product.id}`" 
                  class="btn btn-sm btn-info"
                >
                  Lihat
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
                  Hapus
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
        Sebelumnya
      </button>
      <span class="page-info">
        Halaman {{ currentPage }} dari {{ totalPages }}
      </span>
      <button 
        @click="nextPage" 
        :disabled="currentPage >= totalPages"
        class="btn btn-secondary"
      >
        Selanjutnya
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
      categories: ['Elektronik', 'Pakaian', 'Buku', 'Rumah & Taman'],
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
      console.log('Deleting product with ID:', id);
      
      // Dapatkan produk untuk mendapatkan nama
      let productName = "produk ini";
      try {
        const checkResponse = await axios.get(`/products/${id}`);
        if (checkResponse.data && checkResponse.data.product) {
          productName = checkResponse.data.product.name;
        }
      } catch (error) {
        console.log('Could not get product details:', error);
      }
      
      // Tampilkan dialog konfirmasi sederhana
      if (confirm(`Apakah Anda yakin ingin menghapus "${productName}"?`)) {
        try {
          // Coba hapus produk
          await axios.delete(`/products/${id}`);
          
          // Jika berhasil, refresh daftar dan tunjukkan pesan sukses
          await this.loadProducts();
        } catch (error) {
          console.error('Error deleting product:', error.response || error.message);
          
          // Tampilkan pesan error yang jelas
          let errorMessage = 'Gagal menghapus produk.';
          
          if (error.response && error.response.data && error.response.data.message) {
            errorMessage = `${errorMessage} ${error.response.data.message}`;
          } else if (error.response && error.response.status === 400) {
            errorMessage = 'Produk ini tidak dapat dihapus karena memiliki riwayat transaksi. Coba nonaktifkan produk sebagai gantinya.';
          }
          
          if (errorMessage.includes('transaksi') || errorMessage.includes('transaction')) {
            // Tawarkan opsi untuk menonaktifkan sebagai gantinya
            if (confirm(`${errorMessage}\n\nApakah Anda ingin menonaktifkan produk ini sebagai gantinya?`)) {
              await this.deactivateProduct(id, productName);
            }
          } else {
            alert(errorMessage);
          }
        }
      }
    },
    
    // Fungsi terpisah untuk menonaktifkan produk
    async deactivateProduct(id, productName) {
      try {
        const response = await axios.put(`/products/${id}`, {
          status: 'inactive'
        });
        
        console.log('Deactivate response:', response.data);
        alert(`Produk "${productName}" berhasil dinonaktifkan.`);
        
        // Refresh daftar produk
        await this.loadProducts();
      } catch (error) {
        console.error('Error deactivating product:', error.response || error.message);
        alert('Gagal menonaktifkan produk. Silakan coba lagi.');
      }
    },
    

    
    formatUpdateTime() {
      if (!this.lastUpdate) return '';
      return this.lastUpdate.toLocaleTimeString();
    },
    
    formatNumber(value) {
      if (!value) return '0';
      // Format angka ke format Indonesia (misalnya 1.000.000,00)
      return parseFloat(value).toLocaleString('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      });
    }
  }
};
</script>

<style>
@import "@/styles/products.css";
@import '@/styles/minimal-product.css';

/* Custom styles for product list */
.inactive-row {
  opacity: 0.7;
  background-color: #f8f9fa !important;
}

.inactive-row td {
  color: #6c757d !important;
  font-style: italic;
}
</style>
