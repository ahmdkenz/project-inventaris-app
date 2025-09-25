<template>
  <div class="order-detail">
    <div class="page-header">
      <div class="header-left">
        <router-link :to="order.type === 'sales' ? '/orders/sales' : '/orders/purchase'" class="back-link">
          ← Back to {{ order.type === 'sales' ? 'Sales' : 'Purchase' }} Orders
        </router-link>
        <h1>{{ getOrderTitle() }}</h1>
      </div>
      <div class="order-status">
        <span :class="['status', 'status-' + order.status]">{{ order.status }}</span>
      </div>
    </div>

    <div class="order-info-cards">
      <div class="info-card">
        <h3>{{ order.type === 'sales' ? 'Customer' : 'Supplier' }} Information</h3>
        <div class="info-item">
          <span class="info-label">{{ order.type === 'sales' ? 'Customer' : 'Supplier' }}:</span>
          <span class="info-value">{{ order.type === 'sales' ? order.customer_name : order.supplier_name }}</span>
        </div>
        <div v-if="order.customer_email" class="info-item">
          <span class="info-label">Email:</span>
          <span class="info-value">{{ order.customer_email }}</span>
        </div>
        <div v-if="order.customer_phone" class="info-item">
          <span class="info-label">Phone:</span>
          <span class="info-value">{{ order.customer_phone }}</span>
        </div>
      </div>

      <div class="info-card">
        <h3>Order Information</h3>
        <div class="info-item">
          <span class="info-label">Order Number:</span>
          <span class="info-value">{{ order.orderNumber }}</span>
        </div>
        <div class="info-item">
          <span class="info-label">Order Date:</span>
          <span class="info-value">{{ formatDate(order.order_date) }}</span>
        </div>
        <div v-if="order.expected_delivery" class="info-item">
          <span class="info-label">Expected Delivery:</span>
          <span class="info-value">{{ formatDate(order.expected_delivery) }}</span>
        </div>
      </div>
    </div>

    <div class="order-content">
      <h2>Order Items</h2>
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
          <tr v-for="item in order.items" :key="item.id">
            <td>{{ item.product_name }}</td>
            <td>{{ item.quantity }}</td>
            <td>{{ formatCurrency(item.unit_price) }}</td>
            <td>{{ formatCurrency(item.quantity * item.unit_price) }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
            <td>{{ formatCurrency(order.total_amount) }}</td>
          </tr>
        </tfoot>
      </table>

      <div v-if="order.shipping_address" class="shipping-section">
        <h2>Shipping Information</h2>
        <div class="shipping-address">
          <p><strong>Address:</strong></p>
          <p>{{ order.shipping_address }}</p>
        </div>
      </div>

      <div v-if="order.notes" class="notes-section">
        <h2>Notes</h2>
        <div class="order-notes">{{ order.notes }}</div>
      </div>
      
      <div class="order-timeline">
        <h2>Order Timeline</h2>
        <div class="timeline">
          <div v-for="(event, index) in orderTimeline" :key="index" class="timeline-item">
            <div class="timeline-icon" :class="{ 'active': event.isCompleted }">
              {{ event.icon }}
            </div>
            <div class="timeline-content">
              <h3>{{ event.title }}</h3>
              <p>{{ event.description }}</p>
              <span class="timeline-date">{{ event.date ? formatDate(event.date) : 'Pending' }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="action-buttons">
        <button v-if="canUpdateStatus" class="btn-primary" @click="updateOrderStatus">
          {{ getNextActionLabel() }}
        </button>
        <button v-if="canCancel" class="btn-danger" @click="cancelOrder">
          Cancel Order
        </button>
        <button class="btn-secondary" @click="printOrder">
          Print Order
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../services/axios';

export default {
  name: "OrderDetailView",
  data() {
    return {
      orderId: this.$route.params.id,
      order: {
        id: 1,
        type: 'sales', // or 'purchase'
        orderNumber: 'SO2024090123',
        customer_name: 'PT. ABC Company',
        customer_email: 'order@abc.com',
        customer_phone: '021-1234567',
        supplier_name: '',
        order_date: '2024-09-15',
        expected_delivery: '2024-09-20',
        shipping_address: 'Jl. Sudirman No. 123, Jakarta Pusat',
        status: 'pending', // pending, confirmed/approved, shipped/received, delivered, cancelled
        total_amount: 2250000,
        notes: 'Please deliver during office hours (9 AM - 5 PM)',
        items: [
          { id: 1, product_name: 'Mouse Wireless', quantity: 15, unit_price: 75000 },
          { id: 2, product_name: 'Keyboard Mechanical', quantity: 10, unit_price: 150000 }
        ]
      },
      user: {}
    };
  },
  computed: {
    orderTimeline() {
      // This will be different based on order type (sales vs purchase)
      if (this.order.type === 'sales') {
        return [
          {
            title: 'Order Created',
            description: 'Sales order was created in the system',
            date: this.order.order_date,
            icon: '📝',
            isCompleted: true
          },
          {
            title: 'Order Confirmed',
            description: 'Sales order was confirmed by admin',
            date: this.order.status === 'pending' ? null : '2024-09-16',
            icon: '✅',
            isCompleted: this.order.status !== 'pending'
          },
          {
            title: 'Order Shipped',
            description: 'Items have been shipped to the customer',
            date: this.order.status === 'pending' || this.order.status === 'confirmed' ? null : '2024-09-17',
            icon: '🚚',
            isCompleted: this.order.status === 'shipped' || this.order.status === 'delivered'
          },
          {
            title: 'Order Delivered',
            description: 'Items have been delivered to the customer',
            date: this.order.status === 'delivered' ? '2024-09-18' : null,
            icon: '📦',
            isCompleted: this.order.status === 'delivered'
          }
        ];
      } else {
        // Purchase order timeline
        return [
          {
            title: 'Order Created',
            description: 'Purchase order was created in the system',
            date: this.order.order_date,
            icon: '📝',
            isCompleted: true
          },
          {
            title: 'Order Approved',
            description: 'Purchase order was approved by admin',
            date: this.order.status === 'pending' ? null : '2024-09-16',
            icon: '✅',
            isCompleted: this.order.status !== 'pending'
          },
          {
            title: 'Order Received',
            description: 'Items have been received from supplier',
            date: this.order.status === 'received' ? '2024-09-18' : null,
            icon: '📦',
            isCompleted: this.order.status === 'received'
          }
        ];
      }
    },
    canUpdateStatus() {
      if (this.order.status === 'cancelled' || this.order.status === 'delivered' || this.order.status === 'received') {
        return false;
      }
      
      // Check user permissions based on status and role
      if (this.order.type === 'sales') {
        if (this.order.status === 'pending' && this.user.role === 'admin') {
          return true; // Admin can confirm sales orders
        }
        if (this.order.status === 'confirmed' && (this.user.role === 'admin' || this.user.role === 'staff')) {
          return true; // Admin or staff can ship confirmed sales orders
        }
        if (this.order.status === 'shipped' && (this.user.role === 'admin' || this.user.role === 'staff')) {
          return true; // Admin or staff can mark sales orders as delivered
        }
      } else {
        // Purchase order flow
        if (this.order.status === 'pending' && this.user.role === 'admin') {
          return true; // Only admin can approve purchase orders
        }
        if (this.order.status === 'approved' && (this.user.role === 'admin' || this.user.role === 'staff')) {
          return true; // Admin or staff can mark purchase orders as received
        }
      }
      
      return false;
    },
    canCancel() {
      // Only pending orders can be cancelled, and only by admin
      return (this.order.status === 'pending' && this.user.role === 'admin');
    }
  },
  mounted() {
    this.loadUserData();
    this.loadOrderDetails();
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    async loadOrderDetails() {
      try {
        // In a real app, this would fetch order data from the API
        // For demo, we're using mock data already in the data() function
        console.log('Loading order details for order ID:', this.orderId);
        
        // Determine if it's a sales or purchase order based on the order number prefix
        if (this.orderId.startsWith('SO')) {
          this.order.type = 'sales';
        } else if (this.orderId.startsWith('PO')) {
          this.order.type = 'purchase';
          this.order.orderNumber = 'PO2024090089';
          this.order.customer_name = '';
          this.order.customer_email = '';
          this.order.customer_phone = '';
          this.order.supplier_name = 'Tech Supplier Co.';
          this.order.shipping_address = '';
        }
      } catch (error) {
        console.error('Error loading order details:', error);
      }
    },
    getOrderTitle() {
      return this.order.type === 'sales' 
        ? `Sales Order: ${this.order.orderNumber}`
        : `Purchase Order: ${this.order.orderNumber}`;
    },
    getNextActionLabel() {
      if (this.order.type === 'sales') {
        switch (this.order.status) {
          case 'pending': return 'Confirm Order';
          case 'confirmed': return 'Ship Order';
          case 'shipped': return 'Mark as Delivered';
          default: return 'Update Status';
        }
      } else {
        // Purchase order
        switch (this.order.status) {
          case 'pending': return 'Approve Order';
          case 'approved': return 'Mark as Received';
          default: return 'Update Status';
        }
      }
    },
    async updateOrderStatus() {
      try {
        // This would normally call an API to update the status
        if (this.order.type === 'sales') {
          switch (this.order.status) {
            case 'pending':
              this.order.status = 'confirmed';
              break;
            case 'confirmed':
              this.order.status = 'shipped';
              break;
            case 'shipped':
              this.order.status = 'delivered';
              break;
          }
        } else {
          // Purchase order
          switch (this.order.status) {
            case 'pending':
              this.order.status = 'approved';
              break;
            case 'approved':
              this.order.status = 'received';
              break;
          }
        }
        
        alert('Order status updated successfully');
      } catch (error) {
        console.error('Error updating order status:', error);
        alert('Failed to update order status');
      }
    },
    async cancelOrder() {
      if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
        try {
          // This would normally call an API to cancel the order
          this.order.status = 'cancelled';
          alert('Order cancelled successfully');
        } catch (error) {
          console.error('Error cancelling order:', error);
          alert('Failed to cancel order');
        }
      }
    },
    printOrder() {
      window.print();
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
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
.order-detail {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e1e5e9;
}

.header-left {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.page-header h1 {
  margin: 0;
  color: #2c3e50;
  font-size: 1.8rem;
  font-weight: 600;
}

.back-link {
  color: #007bff;
  text-decoration: none;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.back-link:hover {
  text-decoration: underline;
}

.order-status .status {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 1rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-confirmed, .status-approved {
  background: #d1ecf1;
  color: #0c5460;
}

.status-shipped {
  background: #d4edda;
  color: #155724;
}

.status-delivered, .status-received {
  background: #d1ecf1;
  color: #0c5460;
}

.status-cancelled {
  background: #f8d7da;
  color: #721c24;
}

.order-info-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.info-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.info-card h3 {
  margin-top: 0;
  margin-bottom: 1rem;
  color: #333;
  font-size: 1.2rem;
}

.info-item {
  display: flex;
  margin-bottom: 0.75rem;
}

.info-label {
  font-weight: 600;
  min-width: 120px;
  color: #666;
}

.info-value {
  color: #333;
}

.order-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.order-content h2 {
  margin-top: 0;
  margin-bottom: 1rem;
  color: #333;
  font-size: 1.4rem;
  border-bottom: 1px solid #eee;
  padding-bottom: 0.75rem;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 2rem;
}

.items-table th, 
.items-table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.items-table th {
  background: #f8f9fa;
  color: #333;
  font-weight: 600;
}

.items-table tfoot {
  font-weight: bold;
}

.text-right {
  text-align: right;
}

.shipping-section,
.notes-section {
  margin-bottom: 2rem;
}

.shipping-address,
.order-notes {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 5px;
  border-left: 3px solid #007bff;
}

.order-timeline {
  margin-bottom: 2rem;
}

.timeline {
  position: relative;
  padding-left: 40px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 15px;
  top: 0;
  height: 100%;
  width: 2px;
  background: #e1e5e9;
}

.timeline-item {
  position: relative;
  margin-bottom: 2rem;
}

.timeline-item:last-child {
  margin-bottom: 0;
}

.timeline-icon {
  position: absolute;
  left: -40px;
  top: 0;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: #f8f9fa;
  border: 2px solid #e1e5e9;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}

.timeline-icon.active {
  background: #d4edda;
  border-color: #28a745;
  color: #155724;
}

.timeline-content {
  background: white;
  padding: 1rem;
  border-radius: 5px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.timeline-content h3 {
  margin-top: 0;
  margin-bottom: 0.5rem;
  font-size: 1.1rem;
  color: #333;
}

.timeline-content p {
  margin: 0 0 0.5rem 0;
  color: #666;
}

.timeline-date {
  display: block;
  font-size: 0.8rem;
  color: #999;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  justify-content: flex-end;
}

.btn-primary,
.btn-danger,
.btn-secondary {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 500;
  font-size: 0.95rem;
  transition: all 0.2s ease;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover {
  background: #0056b3;
  transform: translateY(-1px);
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover {
  background: #c82333;
  transform: translateY(-1px);
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #5a6268;
  transform: translateY(-1px);
}

@media (max-width: 768px) {
  .order-detail {
    padding: 1rem;
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .order-info-cards {
    grid-template-columns: 1fr;
  }
  
  .info-item {
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .info-label {
    min-width: auto;
  }
  
  .action-buttons {
    flex-direction: column;
    width: 100%;
  }
  
  .btn-primary, 
  .btn-danger, 
  .btn-secondary {
    width: 100%;
    text-align: center;
  }
}

@media print {
  .back-link, 
  .action-buttons {
    display: none;
  }
  
  .order-detail {
    padding: 0;
  }
  
  .info-card,
  .order-content {
    box-shadow: none;
    border: 1px solid #ddd;
  }
}
</style>
