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
      <div class="filter-group">
        <select v-model="statusFilter" class="filter-select">
          <option value="">Semua Status</option>
          <option value="pending">Menunggu</option>
          <option value="confirmed">Dikonfirmasi</option>
          <option value="shipped">Dikirim</option>
          <option value="delivered">Diterima</option>
          <option value="cancelled">Dibatalkan</option>
        </select>
      </div>
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
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in filteredOrders" :key="order.id">
            <td>{{ order.so_number }}</td>
            <td>{{ order.customer_name }}</td>
            <td>{{ formatDate(order.order_date) }}</td>
            <td>{{ formatCurrency(order.total_amount) }}</td>
            <td>
              <span :class="'status status-' + order.status">
                {{ translateStatus(order.status) }}
              </span>
            </td>
            <td class="actions">
              <button @click="viewOrder(order)" class="btn-info" title="Lihat Detail">
                👁️
              </button>
              <button v-if="canEdit && order.status === 'pending'" @click="editOrder(order)" class="btn-warning" title="Edit">
                ✏️
              </button>
              <button v-if="canConfirm && order.status === 'pending'" @click="confirmOrder(order)" class="btn-success" title="Konfirmasi">
                ✅
              </button>
              <button v-if="canShip && order.status === 'confirmed'" @click="shipOrder(order)" class="btn-primary" title="Kirim Pesanan">
                🚚
              </button>
              <button v-if="canDeliver && order.status === 'shipped'" @click="deliverOrder(order)" class="btn-success" title="Tandai Diterima">
                📦
              </button>
              <button v-if="canDelete && order.status === 'pending'" @click="deleteOrder(order)" class="btn-danger" title="Hapus">
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
              >
            </div>
            <div class="form-group">
              <label>Email Pelanggan</label>
              <input 
                v-model="form.customer_email" 
                type="email"
              >
            </div>
            <div class="form-group">
              <label>Telepon Pelanggan</label>
              <input 
                v-model="form.customer_phone" 
                type="tel"
              >
            </div>
            <div class="form-group">
              <label>Tanggal Pesanan</label>
              <input 
                v-model="form.order_date" 
                type="date" 
                required
              >
            </div>
            <div class="form-group">
              <label>Perkiraan Pengiriman</label>
              <input 
                v-model="form.expected_delivery" 
                type="date"
              >
            </div>            <!-- Item Pesanan -->
            <div class="order-items">
              <h3>Item Pesanan</h3>
              <div v-for="(item, index) in form.items" :key="index" class="item-row">
                <select v-model="item.product_id" class="item-select" required>
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
                >
                <input 
                  v-model.number="item.unit_price" 
                  type="number" 
                  step="0.01"
                  placeholder="Harga Satuan"
                  required
                >
                <button type="button" @click="removeItem(index)" class="btn-danger">Hapus</button>
              </div>
              <button type="button" @click="addItem" class="btn-secondary">Tambah Item</button>
            </div>

            <div class="form-group">
              <label>Alamat Pengiriman</label>
              <textarea v-model="form.shipping_address" rows="3" required></textarea>
            </div>

            <div class="form-group">
              <label>Catatan</label>
              <textarea v-model="form.notes" rows="3"></textarea>
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
              <label>Status:</label>
              <span :class="'status status-' + selectedOrder.status">
                {{ translateStatus(selectedOrder.status) }}
              </span>
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
      statusFilter: '',
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
        items: []
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
      
      if (this.statusFilter) {
        filtered = filtered.filter(order => order.status === this.statusFilter);
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
    canConfirm() {
      return this.user.role === 'admin';
    },
    canShip() {
      return this.user.role === 'admin' || this.user.role === 'staff';
    },
    canDeliver() {
      return this.user.role === 'admin' || this.user.role === 'staff';
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
                { id: 1, product_name: 'Mouse Wireless', quantity: 15, unit_price: 75000 },
                { id: 2, product_name: 'Keyboard Mechanical', quantity: 10, unit_price: 150000 }
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
                { id: 3, product_name: 'Laptop Dell', quantity: 3, unit_price: 1250000 }
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
                { id: 4, product_name: 'Monitor 24 inch', quantity: 6, unit_price: 200000 }
              ]
            }
          ];
        }

        // Load products with stock info
        const productsResponse = await axios.get('/products');
        if (productsResponse.data && productsResponse.data.data) {
          this.products = productsResponse.data.data.map(product => ({
            ...product,
            stock: product.stock || 0
          }));
        } else {
          // Fallback mock data
          this.products = [
            { id: 1, name: 'Mouse Wireless', stock: 50 },
            { id: 2, name: 'Keyboard Mechanical', stock: 30 },
            { id: 3, name: 'Laptop Dell', stock: 15 },
            { id: 4, name: 'Monitor 24 inch', stock: 25 }
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
              { id: 1, product_name: 'Mouse Wireless', quantity: 15, unit_price: 75000 },
              { id: 2, product_name: 'Keyboard Mechanical', quantity: 10, unit_price: 150000 }
            ]
          }
        ];
        
        this.products = [
          { id: 1, name: 'Mouse Wireless', stock: 50 },
          { id: 2, name: 'Keyboard Mechanical', stock: 30 },
          { id: 3, name: 'Laptop Dell', stock: 15 },
          { id: 4, name: 'Monitor 24 inch', stock: 25 }
        ];
      }
    },
    showCreateModal() {
      this.editingOrder = null;
      this.resetForm();
      this.generateSONumber();
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
    async confirmOrder(order) {
      if (confirm('Konfirmasi pesanan penjualan ini?')) {
        try {
          const response = await axios.post(`/sales-orders/${order.id}/confirm`);
          if (response.data && response.data.data) {
            // Update the order in the list with the returned data
            const index = this.orders.findIndex(o => o.id === order.id);
            if (index > -1) {
              this.orders[index].status = 'confirmed';
            }
            alert('Pesanan penjualan berhasil dikonfirmasi!');
          }
        } catch (error) {
          console.error('Error confirming order:', error);
          alert('Gagal mengonfirmasi pesanan');
        }
      }
    },
    async shipOrder(order) {
      if (confirm('Tandai pesanan ini sebagai dikirim?')) {
        try {
          const response = await axios.post(`/sales-orders/${order.id}/ship`);
          if (response.data && response.data.data) {
            // Update the order in the list with the returned data
            const index = this.orders.findIndex(o => o.id === order.id);
            if (index > -1) {
              this.orders[index].status = 'shipped';
            }
            alert('Pesanan berhasil ditandai sebagai dikirim!');
          }
        } catch (error) {
          console.error('Error shipping order:', error);
          alert('Gagal menandai sebagai dikirim');
        }
      }
    },
    async deliverOrder(order) {
      if (confirm('Tandai pesanan ini sebagai diterima?')) {
        try {
          const response = await axios.post(`/sales-orders/${order.id}/deliver`);
          if (response.data && response.data.data) {
            // Update the order in the list with the returned data
            const index = this.orders.findIndex(o => o.id === order.id);
            if (index > -1) {
              this.orders[index].status = 'delivered';
            }
            alert('Pesanan berhasil ditandai sebagai diterima!');
          }
        } catch (error) {
          console.error('Error delivering order:', error);
          alert('Gagal menandai sebagai diterima');
        }
      }
    },
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
        items: []
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
    },
    removeItem(index) {
      this.form.items.splice(index, 1);
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
    }
  }
};
</script>

<style scoped>
@import "../../styles/sales-orders.css";
</style>
