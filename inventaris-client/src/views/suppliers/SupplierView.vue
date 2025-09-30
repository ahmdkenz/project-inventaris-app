<template>
  <AppLayout>
    <div class="suppliers">
      <div class="page-header">
        <div class="header-left">
          <h1>Supplier Management</h1>
        </div>
        <button @click="showCreateModal" class="btn-primary">
          <span class="icon">➕</span>
          New Supplier
        </button>
      </div>

      <!-- Filters -->
      <div class="filters">
        <div class="filter-group">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search suppliers..."
            class="search-input supplier-input"
          >
        </div>
        <div class="filter-group">
          <select v-model="statusFilter" class="filter-select supplier-input">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>

      <!-- Suppliers Table -->
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Contact Person</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="supplier in paginatedSuppliers" :key="supplier.id">
              <td>{{ supplier.name }}</td>
              <td>{{ supplier.contact_person || '-' }}</td>
              <td>{{ supplier.email || '-' }}</td>
              <td>{{ supplier.phone || '-' }}</td>
              <td>
                <span :class="'status status-' + supplier.status">
                  {{ supplier.status }}
                </span>
              </td>
              <td class="actions">
                <button @click="viewSupplier(supplier)" class="btn-info" title="View Details">
                  👁️
                </button>
                <button @click="editSupplier(supplier)" class="btn-warning" title="Edit">
                  ✏️
                </button>
                <button 
                  @click="toggleStatus(supplier)"
                  :class="supplier.status === 'active' ? 'btn-danger' : 'btn-success'"
                  :title="supplier.status === 'active' ? 'Deactivate' : 'Activate'"
                >
                  {{ supplier.status === 'active' ? '🚫' : '✅' }}
                </button>
                <button @click="deleteSupplier(supplier)" class="btn-danger" title="Delete">
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
        <div class="modal supplier-form-modal" @click.stop>
          <div class="modal-header">
            <h2>{{ editingSupplier ? 'Edit' : 'Create' }} Supplier</h2>
            <button @click="closeModal" class="close-btn">×</button>
          </div>
          <div class="modal-body">
            <p class="supplier-modal-tip" v-if="!editingSupplier">Add a new supplier to your inventory system</p>
            <form @submit.prevent="submitSupplier">
              <div class="form-group">
                <label class="required">Name</label>
                <input 
                  v-model="form.name" 
                  type="text"
                  required
                  placeholder="Enter supplier name"
                  class="supplier-input"
                >
              </div>
              <div class="form-group">
                <label>Contact Person</label>
                <input 
                  v-model="form.contact_person" 
                  type="text"
                  placeholder="Enter contact person name"
                  class="supplier-input"
                >
              </div>
              <div class="form-group">
                <label>Email</label>
                <input 
                  v-model="form.email" 
                  type="email"
                  placeholder="Enter email address"
                  class="supplier-input"
                >
              </div>
              <div class="form-group">
                <label>Phone</label>
                <input 
                  v-model="form.phone" 
                  type="text"
                  placeholder="Enter phone number"
                  class="supplier-input"
                >
              </div>
              <div class="form-group">
                <label>Address</label>
                <textarea 
                  v-model="form.address"
                  placeholder="Enter full address"
                  rows="3"
                  class="supplier-input"
                ></textarea>
              </div>
              <div class="form-group">
                <label>Notes</label>
                <textarea 
                  v-model="form.notes"
                  placeholder="Enter additional notes"
                  rows="3"
                  class="supplier-input"
                ></textarea>
              </div>
              <div v-if="editingSupplier" class="form-group">
                <label>Status</label>
                <select v-model="form.status" class="supplier-input">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>

              <div v-if="formMessage" class="supplier-form-message" :class="formMessageType">
                {{ formMessage }}
              </div>

              <div class="modal-footer">
                <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">
                  {{ editingSupplier ? 'Update' : 'Create' }} Supplier
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
            <h2>Supplier Details</h2>
            <button @click="closeViewModal" class="close-btn">×</button>
          </div>
          <div class="modal-body">
            <div v-if="selectedSupplier" class="supplier-details">
              <div class="detail-row">
                <label>Name:</label>
                <span>{{ selectedSupplier.name }}</span>
              </div>
              <div class="detail-row">
                <label>Contact Person:</label>
                <span>{{ selectedSupplier.contact_person || '-' }}</span>
              </div>
              <div class="detail-row">
                <label>Email:</label>
                <span>{{ selectedSupplier.email || '-' }}</span>
              </div>
              <div class="detail-row">
                <label>Phone:</label>
                <span>{{ selectedSupplier.phone || '-' }}</span>
              </div>
              <div class="detail-row">
                <label>Address:</label>
                <span>{{ selectedSupplier.address || '-' }}</span>
              </div>
              <div class="detail-row">
                <label>Status:</label>
                <span :class="'status status-' + selectedSupplier.status">
                  {{ selectedSupplier.status }}
                </span>
              </div>
              <div v-if="selectedSupplier.notes" class="detail-row">
                <label>Notes:</label>
                <span>{{ selectedSupplier.notes }}</span>
              </div>
              <div class="detail-row">
                <label>Created:</label>
                <span>{{ formatDate(selectedSupplier.created_at) }}</span>
              </div>
              <div class="detail-row">
                <label>Last Updated:</label>
                <span>{{ formatDate(selectedSupplier.updated_at) }}</span>
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
  name: 'SupplierView',
  components: {
    AppLayout
  },
  data() {
    return {
      suppliers: [],
      searchQuery: '',
      statusFilter: '',
      currentPage: 1,
      itemsPerPage: 10,
      showModal: false,
      showViewModal: false,
      editingSupplier: null,
      selectedSupplier: null,
      formMessage: '',
      formMessageType: 'success',
      form: {
        name: '',
        contact_person: '',
        email: '',
        phone: '',
        address: '',
        notes: '',
        status: 'active'
      },
      user: {}
    };
  },
  computed: {
    filteredSuppliers() {
      let filtered = this.suppliers;
      
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(supplier => 
          supplier.name.toLowerCase().includes(query) ||
          (supplier.contact_person && supplier.contact_person.toLowerCase().includes(query)) ||
          (supplier.email && supplier.email.toLowerCase().includes(query)) ||
          (supplier.phone && supplier.phone.toLowerCase().includes(query))
        );
      }
      
      if (this.statusFilter) {
        filtered = filtered.filter(supplier => supplier.status === this.statusFilter);
      }
      
      return filtered;
    },
    paginatedSuppliers() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredSuppliers.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredSuppliers.length / this.itemsPerPage) || 1;
    }
  },
  async mounted() {
    this.loadUserData();
    await this.loadSuppliers();
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    async loadSuppliers() {
      try {
        const response = await axios.get('/suppliers');
        if (response.data && response.data.data) {
          this.suppliers = response.data.data;
        }
      } catch (error) {
        console.error('Error loading suppliers:', error);
        // Fallback mock data in case of error
        this.suppliers = [
          {
            id: 1,
            name: 'Tech Supplier Co.',
            contact_person: 'John Doe',
            email: 'contact@techsupplier.com',
            phone: '+62123456789',
            address: 'Jakarta, Indonesia',
            status: 'active',
            notes: 'Reliable supplier for tech products',
            created_at: '2024-01-01T00:00:00.000000Z',
            updated_at: '2024-01-01T00:00:00.000000Z'
          },
          {
            id: 2,
            name: 'Office Supplies Ltd.',
            contact_person: 'Jane Smith',
            email: 'jane@officesupplies.com',
            phone: '+62987654321',
            address: 'Bandung, Indonesia',
            status: 'active',
            notes: '',
            created_at: '2024-01-02T00:00:00.000000Z',
            updated_at: '2024-01-02T00:00:00.000000Z'
          },
          {
            id: 3,
            name: 'Hardware Solutions',
            contact_person: 'Bob Johnson',
            email: 'info@hardwaresolutions.com',
            phone: '+62456789123',
            address: 'Surabaya, Indonesia',
            status: 'inactive',
            notes: 'Currently inactive due to stock issues',
            created_at: '2024-01-03T00:00:00.000000Z',
            updated_at: '2024-01-03T00:00:00.000000Z'
          }
        ];
      }
    },
    showCreateModal() {
      this.editingSupplier = null;
      this.resetForm();
      this.showModal = true;
    },
    editSupplier(supplier) {
      this.editingSupplier = supplier;
      this.form = {
        name: supplier.name,
        contact_person: supplier.contact_person || '',
        email: supplier.email || '',
        phone: supplier.phone || '',
        address: supplier.address || '',
        notes: supplier.notes || '',
        status: supplier.status || 'active'
      };
      this.showModal = true;
    },
    viewSupplier(supplier) {
      this.selectedSupplier = supplier;
      this.showViewModal = true;
    },
    async toggleStatus(supplier) {
      const newStatus = supplier.status === 'active' ? 'inactive' : 'active';
      const actionText = newStatus === 'active' ? 'activate' : 'deactivate';
      
      if (confirm(`Are you sure you want to ${actionText} this supplier?`)) {
        try {
          const response = await axios.patch(`/suppliers/${supplier.id}/status`, {
            status: newStatus
          });
          
          if (response.data && response.data.data) {
            // Update the supplier in the list
            const index = this.suppliers.findIndex(s => s.id === supplier.id);
            if (index > -1) {
              this.suppliers[index].status = newStatus;
            }
            alert(`Supplier ${actionText}d successfully!`);
          }
        } catch (error) {
          console.error(`Error ${actionText}ing supplier:`, error);
          alert(`Failed to ${actionText} supplier`);
        }
      }
    },
    async deleteSupplier(supplier) {
      if (confirm('Are you sure you want to delete this supplier? This action cannot be undone.')) {
        try {
          await axios.delete(`/suppliers/${supplier.id}`);
          
          // Remove from local list
          const index = this.suppliers.findIndex(s => s.id === supplier.id);
          if (index > -1) {
            this.suppliers.splice(index, 1);
          }
          alert('Supplier deleted successfully!');
        } catch (error) {
          console.error('Error deleting supplier:', error);
          alert('Failed to delete supplier. This supplier might be associated with existing purchase orders.');
        }
      }
    },
    async submitSupplier() {
      try {
        if (this.editingSupplier) {
          // Update existing supplier
          const response = await axios.put(`/suppliers/${this.editingSupplier.id}`, this.form);
          
          if (response.data && response.data.data) {
            const index = this.suppliers.findIndex(s => s.id === this.editingSupplier.id);
            if (index > -1) {
              this.suppliers[index] = response.data.data;
            }
            
            this.formMessage = 'Supplier updated successfully!';
            this.formMessageType = 'success';
            
            // Close modal after a short delay to show message
            setTimeout(() => {
              this.closeModal();
            }, 1500);
          }
        } else {
          // Create new supplier
          const response = await axios.post('/suppliers', this.form);
          
          if (response.data && response.data.data) {
            this.suppliers.unshift(response.data.data);
            
            this.formMessage = 'Supplier created successfully!';
            this.formMessageType = 'success';
            
            // Reset form but don't close modal yet to show success message
            this.resetForm();
            
            // Close modal after a short delay
            setTimeout(() => {
              this.closeModal();
            }, 1500);
          }
        }
      } catch (error) {
        console.error('Error submitting supplier:', error);
        const errorMessage = error.response?.data?.message || 'Failed to save supplier';
        this.formMessage = errorMessage;
        this.formMessageType = 'error';
      }
    },
    closeModal() {
      this.showModal = false;
      this.editingSupplier = null;
      this.resetForm();
      this.formMessage = '';
    },
    closeViewModal() {
      this.showViewModal = false;
      this.selectedSupplier = null;
    },
    resetForm() {
      this.form = {
        name: '',
        contact_person: '',
        email: '',
        phone: '',
        address: '',
        notes: '',
        status: 'active'
      };
      this.formMessage = '';
    },
    formatDate(dateString) {
      if (!dateString) return '-';
      return new Date(dateString).toLocaleDateString();
    }
  }
};
</script>

<style scoped>
@import "../../styles/suppliers.css";

/* Enhanced form styling for better contrast */
.supplier-form-modal {
  max-width: 550px;
}

.supplier-form-modal .modal-body {
  padding: 15px 25px;
  background-color: #f9fafb;
}

.supplier-form-modal .form-group {
  margin-bottom: 16px;
}

.supplier-form-modal label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  color: #111827;
  font-size: 0.9rem;
}

/* Enhanced inputs for all supplier-related elements */
.supplier-input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.95rem;
  color: #111827;
  background-color: #fff;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

/* Enhanced filter styles */
.filters {
  background-color: #f9fafb;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 25px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
}

.filters .filter-group {
  margin-right: 15px;
}

.filters .search-input.supplier-input {
  min-width: 300px;
  height: 42px;
  font-weight: 500;
  padding-left: 40px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236B7280' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'%3E%3C/path%3E%3C/svg%3E");
  background-position: 10px center;
  background-repeat: no-repeat;
  background-size: 20px;
}

.filters .filter-select.supplier-input {
  min-width: 160px;
  height: 42px;
  font-weight: 500;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

.supplier-input:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
  outline: none;
}

.supplier-input:hover {
  border-color: #9ca3af;
}

/* Specific filter hover/focus effects */
.filters .search-input.supplier-input:hover,
.filters .filter-select.supplier-input:hover {
  border-color: #9ca3af;
}

.filters .search-input.supplier-input:focus,
.filters .filter-select.supplier-input:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
}

.supplier-modal-tip {
  font-size: 0.85rem;
  color: #6b7280;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px dashed #e5e7eb;
}

.supplier-form-message {
  padding: 10px 12px;
  border-radius: 4px;
  font-size: 14px;
  margin: 12px 0;
}

.supplier-form-message.success {
  background-color: #d1fae5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.supplier-form-message.error {
  background-color: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.modal-footer {
  border-top: 1px solid #e5e7eb;
  padding-top: 20px;
  margin-top: 16px;
}
</style>