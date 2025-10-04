<template>
  <AppLayout>
    <div class="sales-orders">
      <div class="page-header">
        <div class="header-left">
          <h1>Pesanan Penjualan</h1>
        </div>
        <button v-if="canCreate" @click="showCreateModal" class="btn-primary">
          <span class="icon">➕</span>
          Pesanan Baru
        </button>
      </div>

    <!-- Filter -->
    <div class="filters">
      <div class="filter-group">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari pesanan penjualan..."
          class="search-input"
        >
      </div>
      <!-- Status filter removed -->
      <div class="filter-group">
        <input 
          v-model="dateFrom" 
          type="date" 
          class="filter-input"
          placeholder="Tanggal Mulai"
        >
        <input 
          v-model="dateTo" 
          type="date" 
          class="filter-input"
          placeholder="Tanggal Selesai"
        >
      </div>
    </div>

    <!-- Tabel Pesanan Penjualan -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nomor SO</th>
            <th>Pelanggan</th>
            <th>Tanggal Pesanan</th>
            <th>Total</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in filteredOrders" :key="order.id">
            <td>{{ order.so_number }}</td>
            <td>{{ order.customer_name }}</td>
            <td>{{ formatDate(order.order_date) }}</td>
            <td>{{ formatCurrency(order.total_amount) }}</td>
            <td class="actions">
              <button @click="viewOrder(order)" class="btn-info" title="Lihat Detail">
                👁️
              </button>
              <button v-if="canEdit" @click="editOrder(order)" class="btn-warning" title="Edit">
                ✏️
              </button>
              <button v-if="canDelete" @click="deleteOrder(order)" class="btn-danger" title="Hapus">
                🗑️
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination">
      <button 
        @click="currentPage--" 
        :disabled="currentPage === 1"
        class="pagination-btn"
      >
        Sebelumnya
      </button>
      <span class="pagination-info">
        Halaman {{ currentPage }} dari {{ totalPages }}
      </span>
      <button 
        @click="currentPage++" 
        :disabled="currentPage === totalPages"
        class="pagination-btn"
      >
        Selanjutnya
      </button>
    </div>

    <!-- Modal Buat/Edit -->
    <div v-if="showModal" class="modal-overlay" @click="closeModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>{{ editingOrder ? 'Edit' : 'Buat' }} Pesanan Penjualan</h2>
          <button @click="closeModal" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitOrder">
            <div class="form-group">
              <label>Nomor SO</label>
              <input 
                v-model="form.so_number" 
                type="text" 
                :readonly="editingOrder"
                required
              >
            </div>
            <div class="form-group">
              <label>Nama Pelanggan</label>
              <input 
                v-model="form.customer_name" 
                type="text" 
                required
                class="customer-input"
              >
            </div>
            <div class="form-group">
              <label>Tanggal Pesanan</label>
              <div class="date-input-container">
                <input 
                  v-model="form.order_date" 
                  type="date" 
                  class="date-input"
                  required
                >
                <span class="date-icon">📅</span>
              </div>
            </div>
            <div class="form-group">
              <label>Perkiraan Pengiriman</label>
              <div class="date-input-container">
                <input 
                  v-model="form.expected_delivery" 
                  type="date"
                  class="date-input"
                >
                <span class="date-icon">📅</span>
              </div>
            </div>
            
            <div class="form-group">
              <label>Email Pelanggan</label>
              <input 
                v-model="form.customer_email" 
                type="email"
                placeholder="Email pelanggan (opsional)"
              >
            </div>
            
            <div class="form-group">
              <label>Telepon Pelanggan</label>
              <input 
                v-model="form.customer_phone" 
                type="tel"
                placeholder="Nomor telepon pelanggan (opsional)"
              >
            </div>
            
            <div class="form-group">
              <label>Alamat Pengiriman</label>
              <textarea 
                v-model="form.shipping_address" 
                rows="3" 
                required
                placeholder="Masukkan alamat pengiriman lengkap"
              ></textarea>
            </div>
            
            <!-- Order Items -->
            <div class="order-items">
              <h3>Item Pesanan</h3>
              <div v-for="(item, index) in form.items" :key="index" class="item-row">
                <select 
                  v-model="item.product_id" 
                  class="item-select customer-input" 
                  required
                  @change="updateProductPrice(index, item.product_id)"
                >
                  <option value="">Pilih Produk</option>
                  <option v-for="product in products" :key="product.id" :value="product.id">
                    {{ product.name }} (Stok: {{ product.stock }})
                  </option>
                </select>
                <input 
                  v-model.number="item.quantity" 
                  type="number" 
                  placeholder="Jumlah" 
                  min="1"
                  required
                  @input="updateItemTotal(index)"
                >
                <input 
                  :value="formatPriceDisplay(item.unit_price)" 
                  @input="handleUnitPriceInput($event, index)"
                  type="text" 
                  placeholder="Harga Satuan (contoh: 1.000.000)"
                  required
                  class="formatted-price-input"
                >
                <div class="item-subtotal">
                  {{ formatCurrency(item.quantity * item.unit_price) }}
                </div>
                <button type="button" @click="removeItem(index)" class="btn-danger">Hapus</button>
              </div>
              <button type="button" @click="addItem" class="btn-secondary">Tambah Item</button>
            </div>

            <div class="order-summary">
              <div class="summary-row">
                <span class="summary-label">Total Pesanan:</span>
                <span class="summary-value">{{ formatCurrency(calculateTotal()) }}</span>
              </div>
            </div>

            <div class="form-group">
              <label>Catatan</label>
              <textarea v-model="form.notes" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
            </div>

            <div class="modal-footer">
              <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
              <button type="submit" class="btn-primary">
                {{ editingOrder ? 'Perbarui' : 'Buat' }} Pesanan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Lihat Detail -->
    <div v-if="showViewModal" class="modal-overlay" @click="closeViewModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>Detail Pesanan Penjualan</h2>
          <button @click="closeViewModal" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div v-if="selectedOrder" class="order-details">
            <div class="detail-row">
              <label>Nomor SO:</label>
              <span>{{ selectedOrder.so_number }}</span>
            </div>
            <div class="detail-row">
              <label>Pelanggan:</label>
              <span>{{ selectedOrder.customer_name }}</span>
            </div>
            <div class="detail-row">
              <label>Email:</label>
              <span>{{ selectedOrder.customer_email }}</span>
            </div>
            <div class="detail-row">
              <label>Telepon:</label>
              <span>{{ selectedOrder.customer_phone }}</span>
            </div>
            <div class="detail-row">
              <label>Tanggal Pesanan:</label>
              <span>{{ formatDate(selectedOrder.order_date) }}</span>
            </div>
            <div class="detail-row">
              <label>Total:</label>
              <span class="total-amount">{{ formatCurrency(selectedOrder.total_amount) }}</span>
            </div>
            
            <h3>Item Pesanan</h3>
            <table class="items-table">
              <thead>
                <tr>
                  <th>Produk</th>
                  <th>Jumlah</th>
                  <th>Harga Satuan</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in selectedOrder.items" :key="item.id">
                  <td>{{ item.product_name }}</td>
                  <td>{{ item.quantity }}</td>
                  <td>{{ formatCurrency(item.unit_price) }}</td>
                  <td>{{ formatCurrency(item.quantity * item.unit_price) }}</td>
                </tr>
              </tbody>
            </table>

            <div class="shipping-info">
              <h3>Informasi Pengiriman</h3>
              <div class="detail-row">
                <label>Alamat:</label>
                <span>{{ selectedOrder.shipping_address }}</span>
              </div>
              <div v-if="selectedOrder.expected_delivery" class="detail-row">
                <label>Perkiraan Pengiriman:</label>
                <span>{{ formatDate(selectedOrder.expected_delivery) }}</span>
              </div>
            </div>

            <div v-if="selectedOrder.notes" class="notes">
              <label>Catatan:</label>
              <p>{{ selectedOrder.notes }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>


    </div>
  </AppLayout>
</template>

<script>
import axios from '../../services/axios';
import AppLayout from '../../components/layout/AppLayout.vue';

export default {
  name: 'SalesOrderView',
  components: {
    AppLayout
  },
  data() {
    return {
      orders: [],
      products: [],
      searchQuery: '',
      dateFrom: '',
      dateTo: '',
      currentPage: 1,
      itemsPerPage: 10,
      showModal: false,
      showViewModal: false,
      editingOrder: null,
      selectedOrder: null,
      form: {
        so_number: '',
        customer_name: '',
        customer_email: '',
        customer_phone: '',
        order_date: '',
        expected_delivery: '',
        shipping_address: '',
        notes: '',
        items: [],
        total_amount: 0
      },
      user: {}
    };
  },
  computed: {
    filteredOrders() {
      let filtered = this.orders;
      
      if (this.searchQuery) {
        filtered = filtered.filter(order => 
          order.so_number.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          order.customer_name.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
      }
      
      if (this.dateFrom) {
        filtered = filtered.filter(order => order.order_date >= this.dateFrom);
      }
      
      if (this.dateTo) {
        filtered = filtered.filter(order => order.order_date <= this.dateTo);
      }
      
      return filtered;
    },
    totalPages() {
      return Math.ceil(this.filteredOrders.length / this.itemsPerPage);
    },
    canCreate() {
      return this.user.role === 'admin' || this.user.role === 'staff';
    },
    canEdit() {
      return this.user.role === 'admin';
    },
    canDelete() {
      return this.user.role === 'admin';
    }
  },
  async mounted() {
    this.loadUserData();
    await this.loadData();
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    // translateStatus function kept for backward compatibility but hidden in UI
    translateStatus(status) {
      const statusMap = {
        'pending': 'Menunggu',
        'confirmed': 'Dikonfirmasi',
        'shipped': 'Dikirim',
        'delivered': 'Diterima',
        'cancelled': 'Dibatalkan'
      };
      return statusMap[status] || status;
    },
    async loadData() {
      try {
        // Load sales orders
        const ordersResponse = await axios.get('/sales-orders');
        if (ordersResponse.data && ordersResponse.data.data) {
          this.orders = ordersResponse.data.data;
        } else {
          // Fallback mock data
          this.orders = [
            {
              id: 1,
              so_number: 'SO001',
              customer_name: 'PT. ABC Company',
              customer_email: 'order@abc.com',
              customer_phone: '021-1234567',
              order_date: '2024-01-15',
              expected_delivery: '2024-01-20',
              shipping_address: 'Jl. Sudirman No. 123, Jakarta Pusat',
              total_amount: 2250000,
              status: 'pending',
              items: [
                { id: 1, product_id: 1, product_name: 'Mouse Wireless', quantity: 15, unit_price: 75000 },
                { id: 2, product_id: 2, product_name: 'Keyboard Mechanical', quantity: 10, unit_price: 150000 }
              ]
            },
            {
              id: 2,
              so_number: 'SO002',
              customer_name: 'CV. XYZ Solutions',
              customer_email: 'purchase@xyz.com',
              customer_phone: '021-9876543',
              order_date: '2024-01-12',
              expected_delivery: '2024-01-18',
              shipping_address: 'Jl. Gatot Subroto No. 456, Jakarta Selatan',
              total_amount: 3750000,
              status: 'confirmed',
              items: [
                { id: 3, product_id: 3, product_name: 'Laptop Dell', quantity: 3, unit_price: 1250000 }
              ]
            },
            {
              id: 3,
              so_number: 'SO003',
              customer_name: 'Toko Komputer Maju',
              customer_email: 'info@tokomaju.com',
              customer_phone: '021-5555555',
              order_date: '2024-01-08',
              expected_delivery: '2024-01-15',
              shipping_address: 'Jl. Mangga Besar No. 789, Jakarta Barat',
              total_amount: 1200000,
              status: 'delivered',
              items: [
                { id: 4, product_id: 4, product_name: 'Monitor 24 inch', quantity: 6, unit_price: 200000 }
              ]
            }
          ];
        }

        // Load products with stock info
        const productsResponse = await axios.get('/products');
        if (productsResponse.data && productsResponse.data.data) {
          this.products = productsResponse.data.data.map(product => ({
            ...product,
            stock: product.stock || 0,
            price: product.price || 0,
            selling_price: product.selling_price || product.price || 0
          }));
        } else {
          // Fallback mock data
          this.products = [
            { id: 1, name: 'Mouse Wireless', stock: 50, price: 70000, selling_price: 75000 },
            { id: 2, name: 'Keyboard Mechanical', stock: 30, price: 130000, selling_price: 150000 },
            { id: 3, name: 'Laptop Dell', stock: 15, price: 1150000, selling_price: 1250000 },
            { id: 4, name: 'Monitor 24 inch', stock: 25, price: 180000, selling_price: 200000 }
          ];
        }
      } catch (error) {
        console.error('Error loading data:', error);
        // Set fallback data
        this.orders = [
          {
            id: 1,
            so_number: 'SO001',
            customer_name: 'PT. ABC Company',
            customer_email: 'order@abc.com',
            customer_phone: '021-1234567',
            order_date: '2024-01-15',
            expected_delivery: '2024-01-20',
            shipping_address: 'Jl. Sudirman No. 123, Jakarta Pusat',
            total_amount: 2250000,
            status: 'pending',
            items: [
              { id: 1, product_id: 1, product_name: 'Mouse Wireless', quantity: 15, unit_price: 75000 },
              { id: 2, product_id: 2, product_name: 'Keyboard Mechanical', quantity: 10, unit_price: 150000 }
            ]
          }
        ];
        
        this.products = [
            { id: 1, name: 'Mouse Wireless', stock: 50, price: 75000 },
            { id: 2, name: 'Keyboard Mechanical', stock: 30, price: 150000 },
            { id: 3, name: 'Laptop Dell', stock: 15, price: 1250000 },
            { id: 4, name: 'Monitor 24 inch', stock: 25, price: 200000 }
        ];
      }
    },
    showCreateModal() {
      this.editingOrder = null;
      this.resetForm();
      this.generateSONumber();
      
      // Set default order date to today
      const today = new Date().toISOString().split('T')[0];
      this.form.order_date = today;
      
      // Add default empty item
      this.addItem();
      
      this.showModal = true;
    },
    editOrder(order) {
      this.editingOrder = order;
      this.form = {
        so_number: order.so_number,
        customer_name: order.customer_name,
        customer_email: order.customer_email,
        customer_phone: order.customer_phone,
        order_date: order.order_date,
        expected_delivery: order.expected_delivery,
        shipping_address: order.shipping_address,
        notes: order.notes,
        items: [...order.items]
      };
      this.showModal = true;
    },
    viewOrder(order) {
      this.selectedOrder = order;
      this.showViewModal = true;
    },
    // Fungsi-fungsi confirmOrder, shipOrder, dan deliverOrder telah dihapus karena tidak diperlukan
    async deleteOrder(order) {
      if (confirm('Apakah Anda yakin ingin menghapus pesanan penjualan ini?')) {
        try {
          await axios.delete(`/sales-orders/${order.id}`);
          
          // Remove from local list
          const index = this.orders.findIndex(o => o.id === order.id);
          if (index > -1) {
            this.orders.splice(index, 1);
          }
          alert('Pesanan penjualan berhasil dihapus!');
        } catch (error) {
          console.error('Error deleting order:', error);
          alert('Gagal menghapus pesanan');
        }
      }
    },
    async submitOrder() {
      try {
        const formData = {
          ...this.form,
          items: this.form.items.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            unit_price: item.unit_price
          }))
        };
        
        if (this.editingOrder) {
          // Update existing order
          const response = await axios.put(`/sales-orders/${this.editingOrder.id}`, formData);
          
          if (response.data && response.data.data) {
            const index = this.orders.findIndex(o => o.id === this.editingOrder.id);
            if (index > -1) {
              this.orders[index] = response.data.data;
            }
            alert('Pesanan penjualan berhasil diperbarui!');
          }
        } else {
          // Create new order
          const response = await axios.post('/sales-orders', formData);
          
          if (response.data && response.data.data) {
            this.orders.unshift(response.data.data);
            alert('Pesanan penjualan berhasil dibuat!');
          }
        }
        this.closeModal();
      } catch (error) {
        console.error('Error submitting order:', error);
        const errorMessage = error.response?.data?.message || 'Gagal menyimpan pesanan';
        alert(errorMessage);
      }
    },

    closeModal() {
      this.showModal = false;
      this.editingOrder = null;
      this.resetForm();
    },
    closeViewModal() {
      this.showViewModal = false;
      this.selectedOrder = null;
    },
    resetForm() {
      this.form = {
        so_number: '',
        customer_name: '',
        customer_email: '',
        customer_phone: '',
        order_date: '',
        expected_delivery: '',
        shipping_address: '',
        notes: '',
        items: [],
        total_amount: 0
      };
    },
    generateSONumber() {
      const date = new Date();
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
      this.form.so_number = `SO${year}${month}${random}`;
    },
    addItem() {
      this.form.items.push({
        product_id: '',
        quantity: 1,
        unit_price: 0
      });
      // Update total setelah menambah item baru
      this.form.total_amount = this.calculateTotal();
    },
    removeItem(index) {
      this.form.items.splice(index, 1);
      // Update total setelah menghapus item
      this.form.total_amount = this.calculateTotal();
    },
    updateProductPrice(index, productId) {
      if (!productId) return;
      
      const product = this.products.find(p => p.id == productId);
      if (product) {
        // Gunakan harga jual untuk Sales Order
        if (product.selling_price !== undefined) {
          this.form.items[index].unit_price = product.selling_price;
        } else if (product.price !== undefined) {
          // Fallback ke price jika selling_price tidak ada
          this.form.items[index].unit_price = product.price;
        } else {
          // Coba cari dari data order yang ada
          const existingItems = this.orders.flatMap(order => order.items || []);
          const existingItem = existingItems.find(item => item.product_id == productId);
          
          if (existingItem) {
            this.form.items[index].unit_price = existingItem.unit_price;
          } else {
            // Default ke 0 jika tidak ada harga yang ditemukan
            this.form.items[index].unit_price = 0;
          }
        }
      } else {
        // Produk tidak ditemukan, set harga default
        this.form.items[index].unit_price = 0;
      }
      
      // Update total setelah mengubah harga
      this.updateItemTotal(index);
    },
    
    updateItemTotal(index) {
      // Update total pesanan setelah perubahan kuantitas atau harga
      this.form.total_amount = this.calculateTotal();
    },
    
    calculateTotal() {
      return this.form.items.reduce((total, item) => {
        return total + (item.quantity * item.unit_price);
      }, 0);
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
      }).format(amount);
    },
    // Format unit price untuk tampilan (dengan pemisah ribuan)
    formatPriceDisplay(price) {
      if (price === null || price === undefined || price === '') return '';
      return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 0
      }).format(price);
    },
    
    // Handle input pada unit price dengan format angka
    handleUnitPriceInput(event, index) {
      // Ambil nilai dari input
      let value = event.target.value;
      
      // Hapus semua karakter non-digit (titik, koma, spasi, dll)
      value = value.replace(/[^\d]/g, '');
      
      // Convert ke number
      const numericValue = value ? parseInt(value, 10) : 0;
      
      // Update nilai pada item
      this.form.items[index].unit_price = numericValue;
      
      // Update total setelah mengubah harga
      this.updateItemTotal(index);
    }
  }
};
</script>

<style>
/* Pindahkan gaya ke file CSS terpusat */
@import "../../styles/sales-orders.css";
@import "../../styles/date-price-inputs.css";
@import "../../styles/status-notes.css";
@import "../../styles/order-summary.css";
</style>
