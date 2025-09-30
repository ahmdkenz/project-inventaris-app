<template>
  <AppLayout>
    <div class="admin-purchase-orders">
      <div class="page-header">
        <div class="header-left">
          <router-link to="/admin/dashboard" class="back-link">← Back to Dashboard</router-link>
          <h1>Approve Purchase Orders</h1>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters">
        <div class="filter-group">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search purchase orders..."
            class="search-input"
          >
        </div>
        <div class="filter-group">
          <select v-model="statusFilter" class="filter-select">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="received">Received</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="filter-group">
          <div class="date-input-container filter-date-container">
            <input 
              v-model="dateFrom" 
              type="date" 
              class="date-input filter-input"
              placeholder="Date From"
            >
            <span class="date-icon">📅</span>
          </div>
          <div class="date-input-container filter-date-container">
            <input 
              v-model="dateTo" 
              type="date" 
              class="date-input filter-input"
              placeholder="Date To"
            >
            <span class="date-icon">📅</span>
          </div>
        </div>
      </div>

      <!-- Admin Approval Section -->
      <div class="approval-section" v-if="pendingOrders.length > 0">
        <h2>Pending Orders Requiring Approval</h2>
        <div class="card-container">
          <div v-for="order in pendingOrders" :key="order.id" class="approval-card">
            <div class="card-header">
              <span class="po-number">{{ order.po_number }}</span>
              <span :class="'status status-' + order.status">{{ order.status }}</span>
            </div>
            <div class="card-body">
              <div class="order-info">
                <div class="info-row">
                  <span class="label">Supplier:</span>
                  <span class="value">{{ order.supplier_name }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Order Date:</span>
                  <span class="value">{{ formatDate(order.order_date) }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Total Items:</span>
                  <span class="value">{{ order.items ? order.items.length : 0 }}</span>
                </div>
                <div class="info-row">
                  <span class="label">Total Amount:</span>
                  <span class="value total-amount">{{ formatCurrency(order.total_amount) }}</span>
                </div>
              </div>
              <div class="action-buttons">
                <button @click="viewOrder(order)" class="btn-info">
                  <span class="icon">👁️</span> View Details
                </button>
                <button @click="approveOrder(order)" class="btn-success">
                  <span class="icon">✅</span> Approve
                </button>
                <button @click="rejectOrder(order)" class="btn-danger">
                  <span class="icon">❌</span> Reject
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="no-pending" v-else>
        <div class="empty-state">
          <div class="icon">✓</div>
          <h3>No Pending Orders</h3>
          <p>There are no purchase orders waiting for your approval at this time.</p>
        </div>
      </div>

      <!-- All Purchase Orders Table -->
      <div class="section-header">
        <h2>All Purchase Orders</h2>
      </div>
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>PO Number</th>
              <th>Supplier</th>
              <th>Created By</th>
              <th>Order Date</th>
              <th>Total Amount</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in filteredOrders" :key="order.id">
              <td>{{ order.po_number }}</td>
              <td>{{ order.supplier_name }}</td>
              <td>{{ order.created_by || 'N/A' }}</td>
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
                <button v-if="order.status === 'pending'" @click="approveOrder(order)" class="btn-success" title="Approve">
                  ✅
                </button>
                <button v-if="order.status === 'approved'" @click="receiveOrder(order)" class="btn-primary" title="Mark as Received">
                  📦
                </button>
                <button v-if="order.status === 'pending'" @click="rejectOrder(order)" class="btn-danger" title="Reject">
                  ❌
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

      <!-- View Modal -->
      <div v-if="showViewModal" class="modal-overlay" @click="closeViewModal">
        <div class="modal" @click.stop>
          <div class="modal-header">
            <h2>Purchase Order Details</h2>
            <button @click="closeViewModal" class="close-btn">×</button>
          </div>
          <div class="modal-body">
            <div v-if="selectedOrder" class="order-details">
              <div class="detail-row">
                <label>PO Number:</label>
                <span>{{ selectedOrder.po_number }}</span>
              </div>
              <div class="detail-row">
                <label>Supplier:</label>
                <span>{{ selectedOrder.supplier_name }}</span>
              </div>
              <div class="detail-row">
                <label>Created By:</label>
                <span>{{ selectedOrder.created_by || 'N/A' }}</span>
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

              <div v-if="selectedOrder.notes" class="notes">
                <label>Notes:</label>
                <p>{{ selectedOrder.notes }}</p>
              </div>
              
              <div v-if="selectedOrder.status === 'pending'" class="approval-actions">
                <button @click="approveOrderFromModal(selectedOrder)" class="btn-success btn-large">
                  <span class="icon">✅</span> Approve Order
                </button>
                <button @click="rejectOrderFromModal(selectedOrder)" class="btn-danger btn-large">
                  <span class="icon">❌</span> Reject Order
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Reject Reason Modal -->
      <div v-if="showRejectModal" class="modal-overlay" @click="closeRejectModal">
        <div class="modal reject-modal" @click.stop>
          <div class="modal-header">
            <h2>Reject Purchase Order</h2>
            <button @click="closeRejectModal" class="close-btn">×</button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitReject">
              <div class="form-group">
                <label>Reason for Rejection</label>
                <textarea 
                  v-model="rejectReason" 
                  rows="4" 
                  placeholder="Please provide a reason for rejecting this purchase order..."
                  required
                ></textarea>
              </div>
              <div class="modal-footer">
                <button type="button" @click="closeRejectModal" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-danger">Confirm Rejection</button>
              </div>
            </form>
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
  name: 'AdminPurchaseOrderView',
  components: {
    AppLayout
  },
  data() {
    return {
      orders: [],
      searchQuery: '',
      statusFilter: '',
      dateFrom: '',
      dateTo: '',
      currentPage: 1,
      itemsPerPage: 10,
      showViewModal: false,
      showRejectModal: false,
      selectedOrder: null,
      rejectReason: '',
      orderToReject: null,
      user: {}
    };
  },
  computed: {
    filteredOrders() {
      let filtered = this.orders;
      
      if (this.searchQuery) {
        filtered = filtered.filter(order => 
          order.po_number.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          order.supplier_name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          (order.created_by && order.created_by.toLowerCase().includes(this.searchQuery.toLowerCase()))
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
    pendingOrders() {
      return this.orders.filter(order => order.status === 'pending');
    },
    totalPages() {
      return Math.ceil(this.filteredOrders.length / this.itemsPerPage);
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
        // Load purchase orders
        const ordersResponse = await axios.get('/purchase-orders');
        if (ordersResponse.data && ordersResponse.data.data) {
          this.orders = ordersResponse.data.data;
        } else {
          // Fallback mock data
          this.orders = [
            {
              id: 1,
              po_number: 'PO001',
              supplier_name: 'Tech Supplier Co.',
              created_by: 'John Staff',
              order_date: '2024-01-15',
              total_amount: 1500000,
              status: 'pending',
              items: [
                { id: 1, product_name: 'Mouse Wireless', quantity: 10, unit_price: 75000 },
                { id: 2, product_name: 'Keyboard Mechanical', quantity: 5, unit_price: 150000 }
              ]
            },
            {
              id: 2,
              po_number: 'PO002',
              supplier_name: 'Office Supplies Ltd.',
              created_by: 'Sarah Staff',
              order_date: '2024-01-10',
              total_amount: 2500000,
              status: 'approved',
              items: [
                { id: 3, product_name: 'Laptop Dell', quantity: 2, unit_price: 1250000 }
              ]
            },
            {
              id: 3,
              po_number: 'PO003',
              supplier_name: 'Hardware Solutions',
              created_by: 'Mark Staff',
              order_date: '2024-01-05',
              total_amount: 800000,
              status: 'received',
              items: [
                { id: 4, product_name: 'Monitor 24 inch', quantity: 4, unit_price: 200000 }
              ]
            }
          ];
        }
      } catch (error) {
        console.error('Error loading data:', error);
        // Set fallback data
        this.orders = [
          {
            id: 1,
            po_number: 'PO001',
            supplier_name: 'Tech Supplier Co.',
            created_by: 'John Staff',
            order_date: '2024-01-15',
            total_amount: 1500000,
            status: 'pending',
            items: [
              { id: 1, product_name: 'Mouse Wireless', quantity: 10, unit_price: 75000 },
              { id: 2, product_name: 'Keyboard Mechanical', quantity: 5, unit_price: 150000 }
            ]
          }
        ];
      }
    },
    viewOrder(order) {
      this.selectedOrder = order;
      this.showViewModal = true;
    },
    async approveOrder(order) {
      if (confirm('Approve this purchase order?')) {
        try {
          const response = await axios.post(`/purchase-orders/${order.id}/approve`);
          if (response.data && response.data.data) {
            // Update the order in the list with the returned data
            const index = this.orders.findIndex(o => o.id === order.id);
            if (index > -1) {
              this.orders[index].status = 'approved';
            }
            alert('Purchase order approved successfully!');
          }
        } catch (error) {
          console.error('Error approving order:', error);
          alert('Failed to approve order');
        }
      }
    },
    approveOrderFromModal(order) {
      this.closeViewModal();
      this.approveOrder(order);
    },
    async receiveOrder(order) {
      if (confirm('Mark this purchase order as received?')) {
        try {
          const response = await axios.post(`/purchase-orders/${order.id}/receive`);
          if (response.data && response.data.data) {
            // Update the order in the list with the returned data
            const index = this.orders.findIndex(o => o.id === order.id);
            if (index > -1) {
              this.orders[index].status = 'received';
            }
            alert('Purchase order marked as received successfully!');
          }
        } catch (error) {
          console.error('Error receiving order:', error);
          alert('Failed to mark as received');
        }
      }
    },
    rejectOrder(order) {
      this.orderToReject = order;
      this.rejectReason = '';
      this.showRejectModal = true;
    },
    rejectOrderFromModal(order) {
      this.closeViewModal();
      this.rejectOrder(order);
    },
    async submitReject() {
      if (this.rejectReason.trim() === '') {
        alert('Please provide a reason for rejection');
        return;
      }
      
      try {
        const response = await axios.post(`/purchase-orders/${this.orderToReject.id}/reject`, {
          reason: this.rejectReason
        });
        
        if (response.data && response.data.data) {
          // Update the order in the list with the returned data
          const index = this.orders.findIndex(o => o.id === this.orderToReject.id);
          if (index > -1) {
            this.orders[index].status = 'cancelled';
            this.orders[index].rejection_reason = this.rejectReason;
          }
          alert('Purchase order rejected successfully!');
          this.closeRejectModal();
        }
      } catch (error) {
        console.error('Error rejecting order:', error);
        alert('Failed to reject order');
      }
    },
    closeViewModal() {
      this.showViewModal = false;
      this.selectedOrder = null;
    },
    closeRejectModal() {
      this.showRejectModal = false;
      this.orderToReject = null;
      this.rejectReason = '';
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      return new Date(dateString).toLocaleDateString(undefined, options);
    },
    formatCurrency(amount) {
      if (amount === undefined || amount === null) return 'Rp 0';
      
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
      }).format(amount);
    }
  }
};
</script>

<style scoped>
@import "../../styles/purchase-orders.css";
@import "../../styles/admin-purchase-orders.css";
@import "../../styles/date-price-inputs.css";
</style>