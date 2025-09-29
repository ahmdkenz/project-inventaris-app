<template>
  <AppLayout>
    <template #header>
      <h1 class="page-title">Detail Produk</h1>
    </template>
    <div class="product-detail">
      <!-- Header -->
      <div class="page-header">
        <div class="actions">
          <router-link to="/products" class="btn btn-secondary">
            ← Kembali ke Daftar Produk
          </router-link>
          <router-link 
            v-if="user.role === 'admin'" 
            :to="`/products/${productId}/edit`" 
            class="btn btn-primary"
          >
            Edit Produk
          </router-link>
        </div>
      </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loader"></div>
      <p>Memuat data produk...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <p>{{ error }}</p>
      <button @click="loadProductData" class="btn btn-primary">Coba Lagi</button>
    </div>

    <!-- Product Details -->
    <div v-else class="product-info-container">
      <!-- Status Banner untuk produk tidak aktif -->
      <div v-if="product.status === 'inactive'" class="status-banner inactive-banner">
        <span class="icon">⚠️</span>
        <span class="message">Produk ini sudah dinonaktifkan dan tidak akan muncul dalam transaksi baru.</span>
      </div>
      
      <div class="detail-sections-grid">
        <div class="product-info-card">
          <h2>Informasi Umum</h2>
          <div class="info-grid two-column-fields">
            <div class="info-item">
              <label>Nama:</label>
              <span>{{ product.name }}</span>
            </div>
            <div class="info-item">
              <label>SKU:</label>
              <span>{{ product.sku }}</span>
            </div>
            <div class="info-item">
              <label>Kategori:</label>
              <span>{{ product.category }}</span>
            </div>
            <div class="info-item">
              <label>Status:</label>
              <span :class="`status ${product.status}`">{{ product.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}</span>
            </div>
            <div class="info-item full-width">
              <label>Deskripsi:</label>
              <p>{{ product.description || 'Tidak ada deskripsi tersedia' }}</p>
            </div>
          </div>
        </div>

        <div class="product-info-card">
          <h2>Informasi Stok & Harga</h2>
          <div class="info-grid two-column-fields">
            <div class="info-item">
              <label>Stok Saat Ini:</label>
              <span :class="{ 'low-stock': product.stock <= product.min_stock }">
                {{ product.stock }} unit
              </span>
            </div>
            <div class="info-item">
              <label>Stok Minimum:</label>
              <span>{{ product.min_stock }} unit</span>
            </div>
            <div class="info-item">
              <label>Harga Beli:</label>
              <span>Rp {{ formatPrice(product.purchase_price) }}</span>
            </div>
            <div class="info-item">
              <label>Harga Jual:</label>
              <span>Rp {{ formatPrice(product.selling_price) }}</span>
            </div>
            <div class="info-item">
              <label>Margin Keuntungan:</label>
              <span>{{ calculateMargin() }}%</span>
            </div>
          </div>
        </div>

        <div class="product-info-card">
          <h2>Transaksi Terbaru</h2>
          <div v-if="transactions.length === 0" class="no-data">
            Tidak ada transaksi terbaru untuk produk ini.
          </div>
          <table v-else class="transactions-table">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Stok Lama</th>
                <th>Stok Baru</th>
                <th>Alasan</th>
                <th>Pengguna</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="transaction in transactions" :key="transaction.id">
                <td>{{ formatDate(transaction.created_at) }}</td>
                <td>
                  <span :class="{'in': transaction.type === 'in', 'out': transaction.type === 'out'}">
                    {{ transaction.type === 'in' ? 'Stok Masuk' : 'Stok Keluar' }}
                  </span>
                </td>
                <td>{{ transaction.quantity }}</td>
                <td>{{ transaction.old_stock }}</td>
                <td>{{ transaction.new_stock }}</td>
                <td>{{ transaction.reason || 'N/A' }}</td>
                <td>{{ transaction.user ? transaction.user.name : 'Sistem' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="product-info-card quick-actions">
          <h2>Aksi Cepat</h2>
          <div class="actions-grid">
            <router-link to="/stocks/adjustment" class="action-card">
              <span class="icon">📦</span>
              <h3>Atur Stok</h3>
              <p>Tambah atau kurangi stok</p>
            </router-link>
            <router-link 
              v-if="user.role === 'admin'" 
              :to="`/products/${productId}/edit`" 
              class="action-card"
            >
              <span class="icon">✏️</span>
              <h3>Edit Produk</h3>
              <p>Perbarui informasi produk</p>
            </router-link>
            <button 
              v-if="user.role === 'admin'"
              @click="deleteProduct"
              class="action-card delete"
            >
              <span class="icon">🗑️</span>
              <h3>Hapus Produk</h3>
              <p>Hapus produk ini</p>
            </button>
            <router-link to="/reports" class="action-card">
              <span class="icon">📊</span>
              <h3>Lihat Laporan</h3>
              <p>Lihat kinerja produk</p>
            </router-link>
          </div>
        </div>
      </div>
    </div>
    </div>
  </AppLayout>
</template>

<script>
import axios from '@/services/axios';
import AppLayout from '@/components/layout/AppLayout.vue';
import '@/styles/product-detail.css';

export default {
  components: {
    AppLayout
  },
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
          this.error = 'Data produk tidak ditemukan';
        }
      } catch (error) {
        console.error('Error loading product:', error);
        this.error = 'Gagal memuat data produk. Silakan coba lagi.';
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
        return 'Tidak tersedia';
      }
      
      const purchase = parseFloat(this.product.purchase_price);
      const selling = parseFloat(this.product.selling_price);
      const margin = ((selling - purchase) / purchase) * 100;
      
      return margin.toFixed(2);
    },
    async deleteProduct() {
      // Konfirmasi penghapusan dengan satu dialog
      if (confirm(`Apakah Anda yakin ingin menghapus "${this.product.name}"?`)) {
        try {
          // Coba hapus produk
          await axios.delete(`/products/${this.productId}`);
          
          // Jika berhasil, redirect ke daftar produk
          this.$router.push('/products');
        } catch (error) {
          console.error('Error deleting product:', error);
          
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
              await this.deactivateProduct();
            }
          } else {
            alert(errorMessage);
          }
        }
      }
    },
    
    // Fungsi untuk menonaktifkan produk
    async deactivateProduct() {
      try {
        const response = await axios.put(`/products/${this.productId}`, {
          status: 'inactive'
        });
        
        console.log('Deactivate response:', response.data);
        alert(`Produk "${this.product.name}" berhasil dinonaktifkan.`);
        
        // Update status produk di tampilan detail
        this.product.status = 'inactive';
        
        // Opsional: reload data produk untuk mendapatkan status terbaru
        await this.loadProductData();
      } catch (error) {
        console.error('Error deactivating product:', error);
        alert('Gagal menonaktifkan produk. Silakan coba lagi.');
      }
    }
  }
};
</script>

<style scoped>
/* Menghapus inline CSS yang sudah dipindahkan */
@import "@/styles/layout-enhancements.css";
@import "@/styles/responsive-fixes.css";
@import "@/styles/minimal-product.css";

/* Fix for transaction table */
.transactions-table {
  width: 100%;
  overflow-x: auto;
  font-size: 0.9rem;
}

.transactions-table th, .transactions-table td {
  padding: 0.5rem 0.75rem;
  text-align: left;
  white-space: nowrap;
}

/* Status Banner for inactive products */
.status-banner {
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.inactive-banner {
  background-color: #fff3cd;
  border-left: 4px solid #ffc107;
  color: #856404;
}

.status-banner .icon {
  font-size: 1.2rem;
}

.status-banner .message {
  font-weight: 500;
}

@media (max-width: 992px) {
  .product-info-card:has(.transactions-table) {
    padding: 0.5rem;
    overflow-x: auto;
  }
}
</style>
