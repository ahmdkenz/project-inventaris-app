<template>
  <AppLayout>
    <!-- Adjustment Form -->
    <div class="adjustment-form">
      <h2>Buat Penyesuaian Stok</h2>
      <form @submit.prevent="createAdjustment">
        <div class="form-row">
          <div class="form-group">
            <label for="product">Produk:</label>
            <select id="product" v-model="adjustment.productId" required>
              <option value="">Pilih Produk</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }} (Stok Saat Ini: {{ product.stock }})
              </option>
            </select>
          </div>
          <div class="form-group">
            <label for="type">Jenis Penyesuaian:</label>
            <select id="type" v-model="adjustment.type" required>
              <option value="increase">Tambah Stok</option>
              <option value="decrease">Kurangi Stok</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="quantity">Jumlah:</label>
            <input 
              id="quantity" 
              v-model="adjustment.quantity" 
              type="number" 
              min="1" 
              required 
            />
          </div>
          <div class="form-group">
            <label for="reason">Alasan:</label>
            <select id="reason" v-model="adjustment.reason" required>
              <option v-for="option in reasonOptions[adjustment.type]" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="notes">Catatan (Opsional):</label>
          <textarea 
            id="notes" 
            v-model="adjustment.notes" 
            rows="3" 
            placeholder="Tambahkan catatan tambahan..."
          ></textarea>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            Simpan Penyesuaian
          </button>
          <button type="button" @click="refreshData" class="btn btn-secondary">
            Segarkan Data
          </button>
        </div>
      </form>
    </div>

    <!-- Recent Adjustments -->
    <div class="recent-adjustments">
      <h2>Penyesuaian Stok Terbaru</h2>
      <div class="table-container">
        <table class="adjustments-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Produk</th>
              <th>Jenis</th>
              <th>Jumlah</th>
              <th>Stok Lama</th>
              <th>Stok Baru</th>
              <th>Alasan</th>
              <th>Pengguna</th>
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
              <td>{{ adj.old_stock }}</td>
              <td>{{ adj.new_stock }}</td>
              <td>{{ adj.reason }}</td>
              <td>{{ adj.user_name }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import axios from '@/services/axios';
import AppLayout from '@/components/layout/AppLayout.vue';

export default {
  name: 'StockAdjustmentView',
  components: {
    AppLayout
  },
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
      user: {},
      reasonOptions: {
        increase: [
          { value: 'recount', label: 'Penghitungan Ulang' },
          { value: 'return', label: 'Pengembalian Barang' }
        ],
        decrease: [
          { value: 'sold', label: 'Terjual' },
          { value: 'return', label: 'Pengembalian Barang' },
          { value: 'damaged', label: 'Barang Rusak' }
        ]
      }
    };
  },
  async mounted() {
    this.loadUserData();
    await this.loadProducts();
    await this.loadRecentAdjustments();
    
    // Set polling untuk update produk secara otomatis setiap 30 detik
    this._productUpdateInterval = setInterval(async () => {
      await this.loadProducts();
    }, 30000);
  },
  beforeUnmount() {
    if (this._productUpdateInterval) clearInterval(this._productUpdateInterval);
  },
  watch: {
    'adjustment.type': {
      immediate: true,
      handler(newType) {
        // Set alasan default berdasarkan jenis penyesuaian
        const defaultReason = newType === 'increase' ? 'recount' : 'sold';
        // Hanya ubah alasan jika alasan saat ini tidak ada di opsi baru
        const availableReasons = this.reasonOptions[newType].map(option => option.value);
        if (!availableReasons.includes(this.adjustment.reason)) {
          this.adjustment.reason = defaultReason;
        }
      }
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
        // Permintaan dengan parameter sort untuk mendapatkan produk terbaru di awal
        const response = await axios.get('/products', {
          params: {
            sort_by: 'created_at',
            sort_order: 'desc',
            per_page: 100 // Ambil lebih banyak produk
          }
        });
        
        if (response.data && Array.isArray(response.data.data)) {
          // Pertahankan produk yang sudah ada dan tambahkan produk baru
          const updatedProducts = response.data.data.map(product => ({
            id: product.id,
            name: product.name,
            stock: product.stock,
            min_stock: product.min_stock || 10
          }));
          
          // Perbarui daftar produk
          this.products = updatedProducts;
          
          console.log(`Loaded ${this.products.length} products from API`);
        } else {
          console.warn('Unexpected API response format for products');
          // Jangan gunakan fallback mock data jika sudah ada produk yang dimuat sebelumnya
          if (this.products.length === 0) {
            this.products = [
              { id: 1, name: 'Laptop Dell', stock: 15 },
              { id: 2, name: 'Mouse Wireless', stock: 5 },
              { id: 3, name: 'T-Shirt', stock: 50 },
              { id: 4, name: 'Programming Book', stock: 8 }
            ];
          }
        }
      } catch (error) {
        console.error('Error loading products:', error);
        // Jangan gunakan fallback mock data jika sudah ada produk yang dimuat sebelumnya
        if (this.products.length === 0) {
          this.products = [
            { id: 1, name: 'Laptop Dell', stock: 15 },
            { id: 2, name: 'Mouse Wireless', stock: 5 },
            { id: 3, name: 'T-Shirt', stock: 50 },
            { id: 4, name: 'Programming Book', stock: 8 }
          ];
        }
      }
    },
    async loadRecentAdjustments() {
      try {
        // Get recent transactions from API
        const response = await axios.get('/transactions/recent');
        
        if (response.data && Array.isArray(response.data.transactions)) {
          this.recentAdjustments = response.data.transactions.map(transaction => {
            return {
              id: transaction.id,
              product_name: transaction.product ? transaction.product.name : 'Unknown Product',
              type: transaction.type === 'in' ? 'increase' : 
                   transaction.type === 'out' ? 'decrease' : 'adjustment',
              quantity: transaction.quantity,
              reason: transaction.reason || 'N/A',
              user_name: transaction.user ? transaction.user.name : 'System',
              status: 'approved', // Assuming all records in database are approved
              created_at: transaction.created_at,
              old_stock: transaction.old_stock,
              new_stock: transaction.new_stock
            };
          });
        } else {
          // Fallback to mock data if API response is not as expected
          this.recentAdjustments = [
            {
              id: 1,
              product_name: 'Laptop Dell',
              type: 'decrease',
              quantity: 2,
              reason: 'damaged',
              user_name: this.user.name || 'Admin',
              status: 'approved',
              created_at: new Date().toISOString(),
              old_stock: 17,
              new_stock: 15
            },
            {
              id: 2,
              product_name: 'Mouse Wireless',
              type: 'increase',
              quantity: 10,
              reason: 'recount',
              user_name: this.user.name || 'Staff',
              status: 'approved',
              created_at: new Date(Date.now() - 3600000).toISOString(),
              old_stock: 0,
              new_stock: 10
            }
          ];
        }
      } catch (error) {
        console.error('Error loading adjustments:', error);
        // Fallback to mock data
        this.recentAdjustments = [
          {
            id: 1,
            product_name: 'Laptop Dell',
            type: 'decrease',
            quantity: 2,
            reason: 'damaged',
            user_name: this.user.name || 'Admin',
            status: 'approved',
            created_at: new Date().toISOString(),
            old_stock: 17,
            new_stock: 15
          },
          {
            id: 2,
            product_name: 'Mouse Wireless',
            type: 'increase',
            quantity: 10,
            reason: 'recount',
            user_name: this.user.name || 'Staff',
            status: 'approved',
            created_at: new Date(Date.now() - 3600000).toISOString(),
            old_stock: 0,
            new_stock: 10
          }
        ];
      }
    },
    async createAdjustment() {
      try {
        // Validate form data
        if (!this.adjustment.productId || !this.adjustment.quantity) {
          alert('Silakan lengkapi semua field yang diperlukan');
          return;
        }

        // Validasi bahwa kuantitas harus angka positif
        const quantity = parseInt(this.adjustment.quantity);
        if (isNaN(quantity) || quantity <= 0) {
          alert('Jumlah harus berupa angka positif');
          return;
        }

        // Map frontend fields to API expected format
        const adjustmentData = {
          product_id: this.adjustment.productId,
          quantity: quantity,
          type: this.adjustment.type === 'increase' ? 'in' : 'out',
          reason: this.adjustment.reason,
          notes: this.adjustment.notes
        };
        
        console.log('Sending stock adjustment:', adjustmentData);
        
        // Submit adjustment to API
        const response = await axios.post('/stocks/adjust', adjustmentData);
        
        // Reset form dengan default alasan yang sesuai
        const defaultType = 'increase';
        const defaultReason = this.reasonOptions[defaultType][0].value;
        
        this.adjustment = {
          productId: '',
          type: defaultType,
          quantity: '',
          reason: defaultReason,
          notes: ''
        };
        
        // Refresh product list and recent adjustments
        await this.loadProducts();
        await this.loadRecentAdjustments();
        alert('Penyesuaian stok berhasil dibuat!');
      } catch (error) {
        console.error('Error creating adjustment:', error);
        
        // Show specific error message if available
        if (error.response && error.response.data && error.response.data.message) {
          alert(`Error: ${error.response.data.message}`);
        } else {
          alert('Terjadi kesalahan saat membuat penyesuaian. Silakan coba lagi.');
        }
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
@import "../../styles/minimal-stock.css";
@import "../../styles/stock-actions.css";
</style>
