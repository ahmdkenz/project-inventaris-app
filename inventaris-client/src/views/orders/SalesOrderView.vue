<template>
  <div class="sales-orders">
    <div class="page-header">
      <h1>Sales Orders</h1>
      <button v-if="canCreate" @click="showCreateModal" class="btn-primary">
        <span class="icon">➕</span>
        New Sales Order
      </button>
    </div>

    <!-- Filters -->
    <div class="filters">
      <div class="filter-group">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search sales orders..."
          class="search-input"
        >
      </div>
      <div class="filter-group">
        <select v-model="statusFilter" class="filter-select">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="filter-group">
        <input 
          v-model="dateFrom" 
          type="date" 
          class="filter-input"
          placeholder="Date From"
        >
        <input 
          v-model="dateTo" 
          type="date" 
          class="filter-input"
          placeholder="Date To"
        >
      </div>
    </div>

    <!-- Sales Orders Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>SO Number</th>
            <th>Customer</th>
            <th>Order Date</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Actions</th>
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
                {{ order.status }}
              </span>
            </td>
            <td class="actions">
              <button @click="viewOrder(order)" class="btn-info" title="View Details">
                👁️
              </button>
              <button v-if="canEdit && order.status === 'pending'" @click="editOrder(order)" class="btn-warning" title="Edit">
                ✏️
              </button>
              <button v-if="canConfirm && order.status === 'pending'" @click="confirmOrder(order)" class="btn-success" title="Confirm">
                ✅
              </button>
              <button v-if="canShip && order.status === 'confirmed'" @click="shipOrder(order)" class="btn-primary" title="Ship Order">
                🚚
              </button>
              <button v-if="canDeliver && order.status === 'shipped'" @click="deliverOrder(order)" class="btn-success" title="Mark as Delivered">
                📦
              </button>
              <button v-if="canDelete && order.status === 'pending'" @click="deleteOrder(order)" class="btn-danger" title="Delete">
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
        Previous
      </button>
      <span class="pagination-info">
        Page {{ currentPage }} of {{ totalPages }}
      </span>
      <button 
        @click="currentPage++" 
        :disabled="currentPage === totalPages"
        class="pagination-btn"
      >
        Next
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click="closeModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>{{ editingOrder ? 'Edit' : 'Create' }} Sales Order</h2>
          <button @click="closeModal" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitOrder">
            <div class="form-group">
              <label>SO Number</label>
              <input 
                v-model="form.so_number" 
                type="text" 
                :readonly="editingOrder"
                required
              >
            </div>
            <div class="form-group">
              <label>Customer Name</label>
              <input 
                v-model="form.customer_name" 
                type="text"
                required
              >
            </div>
            <div class="form-group">
              <label>Customer Email</label>
              <input 
                v-model="form.customer_email" 
                type="email"
              >
            </div>
            <div class="form-group">
              <label>Customer Phone</label>
              <input 
                v-model="form.customer_phone" 
                type="tel"
              >
            </div>
            <div class="form-group">
              <label>Order Date</label>
              <input 
                v-model="form.order_date" 
                type="date" 
                required
              >
            </div>
            <div class="form-group">
              <label>Expected Delivery</label>
              <input 
                v-model="form.expected_delivery" 
                type="date"
              >
            </div>
            
            <!-- Order Items -->
            <div class="order-items">
              <h3>Order Items</h3>
              <div v-for="(item, index) in form.items" :key="index" class="item-row">
                <select v-model="item.product_id" class="item-select" required>
                  <option value="">Select Product</option>
                  <option v-for="product in products" :key="product.id" :value="product.id">
                    {{ product.name }} (Stock: {{ product.stock }})
                  </option>
                </select>
                <input 
                  v-model.number="item.quantity" 
                  type="number" 
                  placeholder="Quantity" 
                  min="1"
                  required
                >
                <input 
                  v-model.number="item.unit_price" 
                  type="number" 
                  step="0.01"
                  placeholder="Unit Price"
                  required
                >
                <button type="button" @click="removeItem(index)" class="btn-danger">Remove</button>
              </div>
              <button type="button" @click="addItem" class="btn-secondary">Add Item</button>
            </div>

            <div class="form-group">
              <label>Shipping Address</label>
              <textarea v-model="form.shipping_address" rows="3" required></textarea>
            </div>

            <div class="form-group">
              <label>Notes</label>
              <textarea v-model="form.notes" rows="3"></textarea>
            </div>

            <div class="modal-footer">
              <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
              <button type="submit" class="btn-primary">
                {{ editingOrder ? 'Update' : 'Create' }} Order
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Modal -->
    <div v-if="showViewModal" class="modal-overlay" @click="closeViewModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>Sales Order Details</h2>
          <button @click="closeViewModal" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div v-if="selectedOrder" class="order-details">
            <div class="detail-row">
              <label>SO Number:</label>
              <span>{{ selectedOrder.so_number }}</span>
            </div>
            <div class="detail-row">
              <label>Customer:</label>
              <span>{{ selectedOrder.customer_name }}</span>
            </div>
            <div class="detail-row">
              <label>Email:</label>
              <span>{{ selectedOrder.customer_email }}</span>
            </div>
            <div class="detail-row">
              <label>Phone:</label>
              <span>{{ selectedOrder.customer_phone }}</span>
            </div>
            <div class="detail-row">
              <label>Order Date:</label>
              <span>{{ formatDate(selectedOrder.order_date) }}</span>
            </div>
            <div class="detail-row">
              <label>Status:</label>
              <span :class="'status status-' + selectedOrder.status">
                {{ selectedOrder.status }}
              </span>
            </div>
            <div class="detail-row">
              <label>Total Amount:</label>
              <span class="total-amount">{{ formatCurrency(selectedOrder.total_amount) }}</span>
            </div>
            
            <h3>Order Items</h3>
            <table class="items-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Quantity</th>
                  <th>Unit Price</th>
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
              <h3>Shipping Information</h3>
              <div class="detail-row">
                <label>Address:</label>
                <span>{{ selectedOrder.shipping_address }}</span>
              </div>
              <div v-if="selectedOrder.expected_delivery" class="detail-row">
                <label>Expected Delivery:</label>
                <span>{{ formatDate(selectedOrder.expected_delivery) }}</span>
              </div>
            </div>

            <div v-if="selectedOrder.notes" class="notes">
              <label>Notes:</label>
              <p>{{ selectedOrder.notes }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../services/axios';

export default {
  name: 'SalesOrderView',
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
    async loadData() {
      try {
        // Mock data - replace with actual API calls
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

        this.products = [
          { id: 1, name: 'Mouse Wireless', stock: 50 },
          { id: 2, name: 'Keyboard Mechanical', stock: 30 },
          { id: 3, name: 'Laptop Dell', stock: 15 },
          { id: 4, name: 'Monitor 24 inch', stock: 25 }
        ];
      } catch (error) {
        console.error('Error loading data:', error);
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
      if (confirm('Confirm this sales order?')) {
        try {
          // Mock API call
          order.status = 'confirmed';
          alert('Sales order confirmed successfully!');
        } catch (error) {
          console.error('Error confirming order:', error);
          alert('Failed to confirm order');
        }
      }
    },
    async shipOrder(order) {
      if (confirm('Mark this order as shipped?')) {
        try {
          // Mock API call
          order.status = 'shipped';
          alert('Order marked as shipped successfully!');
        } catch (error) {
          console.error('Error shipping order:', error);
          alert('Failed to mark as shipped');
        }
      }
    },
    async deliverOrder(order) {
      if (confirm('Mark this order as delivered?')) {
        try {
          // Mock API call
          order.status = 'delivered';
          alert('Order marked as delivered successfully!');
        } catch (error) {
          console.error('Error delivering order:', error);
          alert('Failed to mark as delivered');
        }
      }
    },
    async deleteOrder(order) {
      if (confirm('Are you sure you want to delete this sales order?')) {
        try {
          // Mock API call
          const index = this.orders.findIndex(o => o.id === order.id);
          if (index > -1) {
            this.orders.splice(index, 1);
          }
          alert('Sales order deleted successfully!');
        } catch (error) {
          console.error('Error deleting order:', error);
          alert('Failed to delete order');
        }
      }
    },
    async submitOrder() {
      try {
        if (this.editingOrder) {
          // Update existing order
          const index = this.orders.findIndex(o => o.id === this.editingOrder.id);
          if (index > -1) {
            this.orders[index] = { ...this.orders[index], ...this.form };
          }
          alert('Sales order updated successfully!');
        } else {
          // Create new order
          const newOrder = {
            id: Date.now(),
            ...this.form,
            status: 'pending',
            total_amount: this.calculateTotal()
          };
          this.orders.unshift(newOrder);
          alert('Sales order created successfully!');
        }
        this.closeModal();
      } catch (error) {
        console.error('Error submitting order:', error);
        alert('Failed to save order');
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
