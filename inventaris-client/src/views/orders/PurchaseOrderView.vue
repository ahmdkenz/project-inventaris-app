<template>
  <AppLayout>
    <div class="purchase-orders">
      <div class="page-header">
        <div class="header-left">
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
                <span v-if="order.status === 'pending'" class="status-note">
                  Waiting for admin approval
                </span>
                <span v-if="order.status === 'cancelled'" class="status-note error">
                  Rejected by admin
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
                <select v-model="form.supplier_id" required class="supplier-input">
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
                <label>Expected Delivery</label>
                <div class="date-input-container">
                  <input 
                    v-model="form.expected_delivery" 
                    type="date"
                    class="date-input"
                  >
                  <span class="date-icon">📅</span>
                </div>
              </div>
              
              <!-- Order Items -->
              <div class="order-items">
                <h3>Order Items</h3>
                <div v-for="(item, index) in form.items" :key="index" class="item-row">
                  <select 
                    v-model="item.product_id" 
                    class="item-select supplier-input" 
                    required
                    @change="updateProductPrice(index, item.product_id)"
                  >
                    <option value="">Select Product</option>
                    <option v-for="product in products" :key="product.id" :value="String(product.id)">
                      {{ product.name }} (ID: {{ product.id }})
                    </option>
                  </select>
                  <input 
                    v-model.number="item.quantity" 
                    type="number" 
                    placeholder="Quantity" 
                    min="1"
                    required
                    @input="updateItemTotal(index)"
                  >
                  <input 
                    :value="formatPriceDisplay(item.unit_price)" 
                    @input="handleUnitPriceInput($event, index)"
                    type="text" 
                    placeholder="Unit Price (contoh: 1.000.000)"
                    required
                    class="formatted-price-input"
                  >
                  <div class="item-subtotal">
                    {{ formatCurrency(item.quantity * item.unit_price) }}
                  </div>
                  <button type="button" @click="removeItem(index)" class="btn-danger">Remove</button>
                </div>
                <button type="button" @click="addItem" class="btn-secondary">Add Item</button>
              </div>

              <div class="order-summary">
                <div class="summary-row">
                  <span class="summary-label">Total Order:</span>
                  <span class="summary-value">{{ formatCurrency(calculateTotal()) }}</span>
                </div>
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
              
              <div class="form-group">
                <label>Email</label>
                <input 
                  v-model="supplierForm.email" 
                  type="email"
                  placeholder="Enter email address"
                >
              </div>
              
              <div class="form-group">
                <label>Address</label>
                <textarea 
                  v-model="supplierForm.address" 
                  rows="3"
                  placeholder="Enter supplier address"
                ></textarea>
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
              
              <!-- Rejection Reason (if cancelled/rejected) -->
              <div v-if="selectedOrder.status === 'cancelled' && selectedOrder.rejection_reason" class="detail-row rejection-reason">
                <label>Rejection Reason:</label>
                <span class="rejection-text">{{ selectedOrder.rejection_reason }}</span>
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
        items: [],
        total_amount: 0
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
        await this.loadProducts();

        // Load suppliers from API
        await this.loadSuppliers();
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
    
    async loadSuppliers() {
      try {
        // Get suppliers from API
        const suppliersResponse = await axios.get('/suppliers');
        if (suppliersResponse.data && suppliersResponse.data.data) {
          // Use actual supplier data from database
          let suppliers = suppliersResponse.data.data;
          
          // Normalize supplier objects to ensure consistent ID field
          suppliers = suppliers.map(supplier => ({
            id: supplier.id || supplier._id, // Handle both SQL and MongoDB style IDs
            name: supplier.name,
            contact_person: supplier.contact_person,
            email: supplier.email,
            phone: supplier.phone,
            address: supplier.address,
            status: supplier.status || 'active'
          }));
          
          // Sort suppliers alphabetically by name for easier selection
          suppliers.sort((a, b) => a.name.localeCompare(b.name));
          
          // Update the suppliers list
          this.suppliers = suppliers;
          
          console.log('Suppliers loaded from API:', this.suppliers.length);
        } else {
          // If API fails or returns no data, extract from orders
          console.log('No suppliers data from API, extracting from orders');
          const uniqueSuppliers = new Map();
          this.orders.forEach(order => {
            if (order.supplier_name && !uniqueSuppliers.has(order.supplier_name)) {
              uniqueSuppliers.set(order.supplier_name, {
                id: order.supplier_id || uniqueSuppliers.size + 1,
                name: order.supplier_name
              });
            }
          });
          this.suppliers = Array.from(uniqueSuppliers.values());
          
          // Sort suppliers alphabetically
          this.suppliers.sort((a, b) => a.name.localeCompare(b.name));
          
          if (this.suppliers.length === 0) {
            // Fallback mock data if no suppliers found
            this.suppliers = [
              { id: 1, name: 'Tech Supplier Co.' },
              { id: 2, name: 'Office Supplies Ltd.' },
              { id: 3, name: 'Hardware Solutions' }
            ];
          }
        }
      } catch (error) {
        console.error('Error loading suppliers:', error);
        
        // Fallback to extracting from orders if API fails
        const uniqueSuppliers = new Map();
        this.orders.forEach(order => {
          if (order.supplier_name && !uniqueSuppliers.has(order.supplier_name)) {
            uniqueSuppliers.set(order.supplier_name, {
              id: order.supplier_id || uniqueSuppliers.size + 1,
              name: order.supplier_name
            });
          }
        });
        this.suppliers = Array.from(uniqueSuppliers.values());
        
        // Sort suppliers alphabetically
        this.suppliers.sort((a, b) => a.name.localeCompare(b.name));
        
        if (this.suppliers.length === 0) {
          // Fallback mock data
          this.suppliers = [
            { id: 1, name: 'Tech Supplier Co.' },
            { id: 2, name: 'Office Supplies Ltd.' },
            { id: 3, name: 'Hardware Solutions' }
          ];
        }
      }
    },
    
    async loadProducts() {
      try {
        // Get products from API
        const productsResponse = await axios.get('/products');
        if (productsResponse.data && productsResponse.data.data) {
          // Use actual product data from database
          let products = productsResponse.data.data;
          
          // Normalize product objects to ensure consistent ID field as string
          products = products.map(product => ({
            id: String(product.id || product._id), // Ensure ID is always string
            name: product.name,
            price: product.price || 0,
            purchase_price: product.purchase_price || product.price || 0, // Tambahkan purchase_price
            originalData: product // Keep original data for debugging
          }));
          
          // Log the products for debugging
          console.log('Raw products from API:', productsResponse.data.data);
          console.log('Normalized product IDs:', products.map(p => ({id: p.id, type: typeof p.id})));
          
          // Sort products alphabetically by name for easier selection
          products.sort((a, b) => a.name.localeCompare(b.name));
          
          // Update the products list
          this.products = products;
          
          console.log('Products loaded from API:', this.products.length);
        } else {
          // Fallback mock data with string IDs
          this.products = [
            { id: "1", name: 'Mouse Wireless', price: 70000, purchase_price: 60000 },
            { id: "2", name: 'Keyboard Mechanical', price: 130000, purchase_price: 110000 },
            { id: "3", name: 'Laptop Dell', price: 1150000, purchase_price: 1000000 },
            { id: "4", name: 'Monitor 24 inch', price: 180000, purchase_price: 160000 }
          ];
        }
      } catch (error) {
        console.error('Error loading products:', error);
        
        // Fallback mock data with string IDs
        this.products = [
          { id: "1", name: 'Mouse Wireless', price: 70000, purchase_price: 60000 },
          { id: "2", name: 'Keyboard Mechanical', price: 130000, purchase_price: 110000 },
          { id: "3", name: 'Laptop Dell', price: 1150000, purchase_price: 1000000 },
          { id: "4", name: 'Monitor 24 inch', price: 180000, purchase_price: 160000 }
        ];
      }
    },
    async showCreateModal() {
      this.editingOrder = null;
      this.resetForm();
      this.generatePONumber();
      
      // Set default order date to today
      const today = new Date().toISOString().split('T')[0];
      this.form.order_date = today;
      
      // Add default empty item
      this.form.items = [{
        product_id: '',
        quantity: 1,
        unit_price: 0
      }];
      
      // Refresh data lists to ensure we have the latest data
      await Promise.all([
        this.loadSuppliers(),
        this.loadProducts()
      ]);
      
      this.showModal = true;
      
      console.log('Create modal opened with form data:', this.form);
    },
    async editOrder(order) {
      // Refresh data lists first to ensure we have the latest data
      await Promise.all([
        this.loadSuppliers(),
        this.loadProducts()
      ]);
      
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
        // Validasi form
        if (!this.form.supplier_id) {
          alert('Supplier harus dipilih!');
          return;
        }

        if (!this.form.order_date) {
          alert('Tanggal order harus diisi!');
          return;
        }

        if (this.form.items.length === 0) {
          alert('Minimal satu item harus ditambahkan!');
          return;
        }

        // Validasi items
        for (const [index, item] of this.form.items.entries()) {
          if (!item.product_id) {
            alert(`Produk pada item ke-${index + 1} harus dipilih!`);
            return;
          }

          if (!item.quantity || item.quantity <= 0) {
            alert(`Kuantitas pada item ke-${index + 1} harus lebih dari 0!`);
            return;
          }

          if (!item.unit_price || item.unit_price <= 0) {
            alert(`Harga satuan pada item ke-${index + 1} harus lebih dari 0!`);
            return;
          }
        }

        // Tambahkan supplier_name jika tersedia
        if (this.form.supplier_id) {
          const selectedSupplier = this.suppliers.find(s => s.id == this.form.supplier_id);
          if (selectedSupplier) {
            this.form.supplier_name = selectedSupplier.name;
          }
        }

        // Enrichment data untuk setiap item dengan nama produk
        for (const item of this.form.items) {
          // Pastikan product_id selalu disimpan sebagai string
          item.product_id = String(item.product_id);
          
          const selectedProduct = this.products.find(p => String(p.id) === item.product_id);
          if (selectedProduct) {
            item.product_name = selectedProduct.name;
          } else {
            console.error(`Produk dengan ID ${item.product_id} tidak ditemukan dalam daftar produk`);
          }
        }

        // Log untuk debugging
        console.log('Submitting form data:', this.form);

        // Log untuk debugging data yang dikirim
        console.log('Data yang dikirim ke backend:', this.form);
        
        // Log khusus untuk items
        console.log('Detail items yang akan dikirim:', JSON.stringify(this.form.items, null, 2));
        
        // Debugging produk yang dipilih
        for (const item of this.form.items) {
          const product = this.products.find(p => String(p.id) === item.product_id);
          console.log('Product details for item:', { 
            product_id: item.product_id, 
            product_id_type: typeof item.product_id,
            product_found: !!product,
            product_details: product
          });
        }

        // Kirim data ke backend
        const response = this.editingOrder
          ? await axios.put(`/purchase-orders/${this.editingOrder.id}`, this.form)
          : await axios.post('/purchase-orders', this.form);

        if (response.data && response.data.data) {
          // Handle sukses dengan data yang dikembalikan dari server
          alert(`Purchase Order berhasil ${this.editingOrder ? 'diperbarui' : 'dibuat'}!`);
          this.closeModal();
          await this.loadData();
        } else if (response.data && response.data.success) {
          // Fallback jika menggunakan format response lama
          alert(`Purchase Order berhasil ${this.editingOrder ? 'diperbarui' : 'dibuat'}!`);
          this.closeModal();
          await this.loadData();
        } else {
          alert('Terjadi kesalahan saat menyimpan Purchase Order.');
        }
      } catch (error) {
        console.error('Respon error dari backend:', error.response);
        console.error('Error submitting order:', error);
        if (error.response && error.response.data && error.response.data.errors) {
          // Tampilkan error validasi dari server jika ada
          const errorMessages = Object.values(error.response.data.errors).flat();
          alert('Error: ' + errorMessages.join('\n'));
        } else if (error.response && error.response.data && error.response.data.message) {
          alert('Error: ' + error.response.data.message);
        } else {
          alert('Gagal menyimpan Purchase Order. Silakan coba lagi.');
        }
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
    
    async showQuickAddSupplierModal() {
      // Pastikan data supplier terupdate terlebih dahulu
      await this.loadSuppliers();
      
      // Reset form supplier
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
          
          // Keep a reference to the newly added supplier ID for dropdown selection
          const addedSupplierId = newSupplier.id || newSupplier._id;
          
          // Reload the full suppliers list from API to ensure consistency
          await this.loadSuppliers();
          
          // Select the new supplier in the form
          this.form.supplier_id = addedSupplierId;
          
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
          
          // Close modal after 1.5 seconds and ensure proper selection
          setTimeout(() => {
            this.closeQuickSupplierModal();
            // Double-check that the supplier is still selected
            this.$nextTick(() => {
              this.form.supplier_id = addedSupplierId;
            });
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
        items: [],
        total_amount: 0
      };
    },
    async generatePONumber() {
      try {
        // Coba gunakan API untuk mendapatkan nomor PO yang unik
        const response = await axios.get('/generate-po-number');
        if (response.data && response.data.po_number) {
          this.form.po_number = response.data.po_number;
        } else {
          // Fallback ke generasi nomor PO lokal
          const date = new Date();
          const year = date.getFullYear();
          const month = String(date.getMonth() + 1).padStart(2, '0');
          const day = String(date.getDate()).padStart(2, '0');
          const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
          this.form.po_number = `PO${year}${month}${day}${random}`;
        }
      } catch (error) {
        console.error('Error generating PO number:', error);
        // Fallback ke generasi nomor PO lokal
        const date = new Date();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
        this.form.po_number = `PO${year}${month}${day}${random}`;
      }
    },
    async addItem() {
      // Refresh product list to ensure latest data is available
      await this.loadProducts();
      
      this.form.items.push({
        product_id: '',
        quantity: 1,
        unit_price: 0  // Akan diformat secara otomatis
      });
      
      // Update total setelah menambah item baru
      this.form.total_amount = this.calculateTotal();
    },
    removeItem(index) {
      this.form.items.splice(index, 1);
      // Update total setelah menghapus item
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
    updateProductPrice(index, productId) {
      if (!productId) return;
      
      const product = this.products.find(p => String(p.id) === String(productId));
      if (product) {
        // Gunakan purchase_price jika tersedia, jika tidak gunakan price
        if (product.purchase_price !== undefined) {
          this.form.items[index].unit_price = product.purchase_price;
        } else if (product.price !== undefined) {
          // Fallback ke price jika purchase_price tidak ada
          this.form.items[index].unit_price = product.price;
        } else {
          // Jika tidak ada harga, coba cari dari data order yang ada
          const existingItems = this.orders.flatMap(order => order.items || []);
          const existingItem = existingItems.find(item => String(item.product_id) === String(productId));
          
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
      
      // Log untuk debugging
      console.log(`Updated unit price for item ${index} to ${numericValue}`);
    }
  }
};
</script>

<style scoped>
@import "../../styles/purchase-orders.css";
@import "../../styles/quick-supplier.css";
@import "../../styles/supplier-dropdown.css";
@import "../../styles/date-price-inputs.css";
@import "../../styles/status-notes.css";
@import "../../styles/order-summary.css";
</style>