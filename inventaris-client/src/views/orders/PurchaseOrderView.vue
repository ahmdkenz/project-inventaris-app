<template>
  <AppLayout>
    <div class="purchase-orders">
      <div class="page-header">
        <div class="header-left">
          <router-link to="/orders" class="back-link">← Back to Orders</router-link>
          <h1>Purchase Orders</h1>
        </div>
        <button v-if="canCreate" @click="showCreateModal" class="btn-primary">
          <span class="icon">➕</span>
          New Purchase Order
        </button>
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

      <!-- Purchase Orders Table -->
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>PO Number</th>
              <th>Supplier</th>
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
                <button v-if="canApprove && order.status === 'pending'" @click="approveOrder(order)" class="btn-success" title="Approve">
                  ✅
                </button>
                <button v-if="canReceive && order.status === 'approved'" @click="receiveOrder(order)" class="btn-primary" title="Mark as Received">
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
            <h2>{{ editingOrder ? 'Edit' : 'Create' }} Purchase Order</h2>
            <button @click="closeModal" class="close-btn">×</button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitOrder">
              <div class="form-group">
                <label>PO Number</label>
                <input 
                  v-model="form.po_number" 
                  type="text" 
                  :readonly="editingOrder"
                  required
                >
              </div>
              <div class="form-group">
                <label>Supplier</label>
                <select v-model="form.supplier_id" required>
                  <option value="">Select Supplier</option>
                  <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                    {{ supplier.name }}
                  </option>
                </select>
                <div class="quick-add-supplier">
                  <span>Can't find your supplier?</span>
                  <button type="button" @click="showQuickAddSupplierModal">+ Add New Supplier</button>
                </div>
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
                      {{ product.name }}
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

      <!-- Quick Add Supplier Modal -->
      <div v-if="showQuickSupplierModal" class="modal-overlay quick-supplier-modal" @click="closeQuickSupplierModal">
        <div class="modal" @click.stop>
          <div class="modal-header">
            <h2>Add New Supplier</h2>
            <button @click="closeQuickSupplierModal" class="close-btn">×</button>
          </div>
          <div class="modal-body">
            <p class="supplier-modal-tip">Add a new supplier to be used immediately in your purchase order</p>
            <form @submit.prevent="submitQuickSupplier">
              <div class="form-group">
                <label class="required">Name</label>
                <input 
                  v-model="supplierForm.name" 
                  type="text"
                  required
                  placeholder="Enter supplier name"
                >
              </div>
              <div class="form-group">
                <label>Contact Person</label>
                <input 
                  v-model="supplierForm.contact_person" 
                  type="text"
                  placeholder="Enter contact person name"
                >
              </div>
              <div class="form-group">
                <label>Phone</label>
                <input 
                  v-model="supplierForm.phone" 
                  type="text"
                  placeholder="Enter phone number"
                >
              </div>
              
              <div v-if="supplierAddedMessage" class="supplier-added-success">
                {{ supplierAddedMessage }}
              </div>

              <div class="modal-footer">
                <button type="button" @click="closeQuickSupplierModal" class="btn-secondary">Close</button>
                <button type="submit" class="btn-primary">Add Supplier</button>
              </div>
            </form>
          </div>
        </div>
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
  name: 'PurchaseOrderView',
  components: {
    AppLayout
  },
  data() {
    return {
      orders: [],
      suppliers: [],
      products: [],
      searchQuery: '',
      statusFilter: '',
      dateFrom: '',
      dateTo: '',
      currentPage: 1,
      itemsPerPage: 10,
      showModal: false,
      showViewModal: false,
      showQuickSupplierModal: false,
      editingOrder: null,
      selectedOrder: null,
      supplierAddedMessage: '',
      form: {
        po_number: '',
        supplier_id: '',
        order_date: '',
        expected_delivery: '',
        notes: '',
        items: []
      },
      supplierForm: {
        name: '',
        contact_person: '',
        phone: '',
        email: '',
        address: '',
        notes: ''
      },
      user: {}
    };
  },
  computed: {
    filteredOrders() {
      let filtered = this.orders;
      
      if (this.searchQuery) {
        filtered = filtered.filter(order => 
          order.po_number.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          order.supplier_name.toLowerCase().includes(this.searchQuery.toLowerCase())
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
    canApprove() {
      return this.user.role === 'admin';
    },
    canReceive() {
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
              order_date: '2024-01-05',
              total_amount: 800000,
              status: 'received',
              items: [
                { id: 4, product_name: 'Monitor 24 inch', quantity: 4, unit_price: 200000 }
              ]
            }
          ];
        }

        // Load products for order items
        const productsResponse = await axios.get('/products');
        if (productsResponse.data && productsResponse.data.data) {
          this.products = productsResponse.data.data;
        } else {
          // Fallback mock data
          this.products = [
            { id: 1, name: 'Mouse Wireless' },
            { id: 2, name: 'Keyboard Mechanical' },
            { id: 3, name: 'Laptop Dell' },
            { id: 4, name: 'Monitor 24 inch' }
          ];
        }

        // For suppliers, we could get them from the backend if you have a suppliers table
        // For now, we'll extract unique suppliers from the orders
        const uniqueSuppliers = new Map();
        this.orders.forEach(order => {
          if (!uniqueSuppliers.has(order.supplier_name)) {
            uniqueSuppliers.set(order.supplier_name, {
              id: uniqueSuppliers.size + 1,
              name: order.supplier_name
            });
          }
        });
        this.suppliers = Array.from(uniqueSuppliers.values());

        if (this.suppliers.length === 0) {
          // Fallback mock data
          this.suppliers = [
            { id: 1, name: 'Tech Supplier Co.' },
            { id: 2, name: 'Office Supplies Ltd.' },
            { id: 3, name: 'Hardware Solutions' }
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
            order_date: '2024-01-15',
            total_amount: 1500000,
            status: 'pending',
            items: [
              { id: 1, product_name: 'Mouse Wireless', quantity: 10, unit_price: 75000 },
              { id: 2, product_name: 'Keyboard Mechanical', quantity: 5, unit_price: 150000 }
            ]
          }
        ];
        
        this.suppliers = [
          { id: 1, name: 'Tech Supplier Co.' },
          { id: 2, name: 'Office Supplies Ltd.' },
          { id: 3, name: 'Hardware Solutions' }
        ];
        
        this.products = [
          { id: 1, name: 'Mouse Wireless' },
          { id: 2, name: 'Keyboard Mechanical' },
          { id: 3, name: 'Laptop Dell' },
          { id: 4, name: 'Monitor 24 inch' }
        ];
      }
    },
    showCreateModal() {
      this.editingOrder = null;
      this.resetForm();
      this.generatePONumber();
      this.showModal = true;
    },
    editOrder(order) {
      this.editingOrder = order;
      this.form = {
        po_number: order.po_number,
        supplier_id: order.supplier_id,
        order_date: order.order_date,
        expected_delivery: order.expected_delivery,
        notes: order.notes,
        items: [...order.items]
      };
      this.showModal = true;
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
    async deleteOrder(order) {
      if (confirm('Are you sure you want to delete this purchase order?')) {
        try {
          await axios.delete(`/purchase-orders/${order.id}`);
          
          // Remove from local list
          const index = this.orders.findIndex(o => o.id === order.id);
          if (index > -1) {
            this.orders.splice(index, 1);
          }
          alert('Purchase order deleted successfully!');
        } catch (error) {
          console.error('Error deleting order:', error);
          alert('Failed to delete order');
        }
      }
    },
    async submitOrder() {
      try {
        const formData = {
          ...this.form,
          // Ensure we have all required fields
          supplier_name: this.form.supplier_name || this.suppliers.find(s => s.id === this.form.supplier_id)?.name,
          items: this.form.items.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            unit_price: item.unit_price
          }))
        };
        
        if (this.editingOrder) {
          // Update existing order
          const response = await axios.put(`/purchase-orders/${this.editingOrder.id}`, formData);
          
          if (response.data && response.data.data) {
            const index = this.orders.findIndex(o => o.id === this.editingOrder.id);
            if (index > -1) {
              this.orders[index] = response.data.data;
            }
            alert('Purchase order updated successfully!');
          }
        } else {
          // Create new order
          const response = await axios.post('/purchase-orders', formData);
          
          if (response.data && response.data.data) {
            this.orders.unshift(response.data.data);
            alert('Purchase order created successfully!');
          }
        }
        this.closeModal();
      } catch (error) {
        console.error('Error submitting order:', error);
        const errorMessage = error.response?.data?.message || 'Failed to save order';
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
    
    showQuickAddSupplierModal() {
      this.supplierForm = {
        name: '',
        contact_person: '',
        phone: '',
        email: '',
        address: '',
        notes: ''
      };
      this.supplierAddedMessage = '';
      this.showQuickSupplierModal = true;
    },
    
    closeQuickSupplierModal() {
      this.showQuickSupplierModal = false;
    },
    
    async submitQuickSupplier() {
      try {
        const response = await axios.post('/suppliers', this.supplierForm);
        
        if (response.data && response.data.data) {
          const newSupplier = response.data.data;
          
          // Add to suppliers list
          this.suppliers.unshift(newSupplier);
          
          // Select the new supplier in the form
          this.form.supplier_id = newSupplier.id;
          
          // Show success message
          this.supplierAddedMessage = 'Supplier added successfully!';
          
          // Clear form fields but don't close modal yet
          this.supplierForm = {
            name: '',
            contact_person: '',
            phone: '',
            email: '',
            address: '',
            notes: ''
          };
          
          // Close modal after 1.5 seconds
          setTimeout(() => {
            this.closeQuickSupplierModal();
          }, 1500);
        }
      } catch (error) {
        console.error('Error adding supplier:', error);
        const errorMessage = error.response?.data?.message || 'Failed to add supplier';
        alert(errorMessage);
      }
    },
    resetForm() {
      this.form = {
        po_number: '',
        supplier_id: '',
        order_date: '',
        expected_delivery: '',
        notes: '',
        items: []
      };
    },
    generatePONumber() {
      const date = new Date();
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
      this.form.po_number = `PO${year}${month}${random}`;
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
@import "../../styles/purchase-orders.css";
@import "../../styles/quick-supplier.css";
</style>