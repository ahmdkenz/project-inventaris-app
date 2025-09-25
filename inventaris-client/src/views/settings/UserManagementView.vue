<template>
  <div class="user-management">
    <!-- Header -->
    <div class="page-header">
      <h1>User Management</h1>
      <div class="actions">
        <button @click="showAddUserModal = true" class="btn btn-primary">
          Add New User
        </button>
        <button @click="refreshUsers" class="btn btn-secondary">
          Refresh
        </button>
      </div>
    </div>

    <!-- User Stats -->
    <div class="user-stats">
      <div class="stat-card">
        <h3>Total Users</h3>
        <span class="stat-number">{{ users.length }}</span>
      </div>
      <div class="stat-card">
        <h3>Active Users</h3>
        <span class="stat-number">{{ activeUsers }}</span>
      </div>
      <div class="stat-card">
        <h3>Admins</h3>
        <span class="stat-number">{{ adminUsers }}</span>
      </div>
      <div class="stat-card">
        <h3>Staff Members</h3>
        <span class="stat-number">{{ staffUsers }}</span>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters">
      <div class="search-box">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search users..." 
          @input="filterUsers"
        >
      </div>
      <div class="filter-group">
        <select v-model="roleFilter" @change="filterUsers">
          <option value="">All Roles</option>
          <option value="admin">Admin</option>
          <option value="staff">Staff</option>
        </select>
        <select v-model="statusFilter" @change="filterUsers">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
    </div>

    <!-- Users Table -->
    <div class="table-container">
      <table class="users-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Last Login</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in paginatedUsers" :key="user.id">
            <td>{{ user.id }}</td>
            <td>
              <div class="user-info">
                <div class="avatar">{{ user.name.charAt(0).toUpperCase() }}</div>
                <span>{{ user.name }}</span>
              </div>
            </td>
            <td>{{ user.email }}</td>
            <td>
              <span :class="`role ${user.role}`">{{ user.role }}</span>
            </td>
            <td>
              <span :class="`status ${user.status}`">{{ user.status }}</span>
            </td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td>{{ user.last_login ? formatDate(user.last_login) : 'Never' }}</td>
            <td>
              <div class="action-buttons">
                <button 
                  @click="editUser(user)" 
                  class="btn btn-sm btn-warning"
                >
                  Edit
                </button>
                <button 
                  @click="toggleUserStatus(user)" 
                  :class="`btn btn-sm ${user.status === 'active' ? 'btn-danger' : 'btn-success'}`"
                >
                  {{ user.status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
                <button 
                  v-if="user.id !== currentUser.id"
                  @click="deleteUser(user.id)" 
                  class="btn btn-sm btn-danger"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination">
      <button 
        @click="previousPage" 
        :disabled="currentPage <= 1"
        class="btn btn-secondary"
      >
        Previous
      </button>
      <span class="page-info">
        Page {{ currentPage }} of {{ totalPages }}
      </span>
      <button 
        @click="nextPage" 
        :disabled="currentPage >= totalPages"
        class="btn btn-secondary"
      >
        Next
      </button>
    </div>

    <!-- Add/Edit User Modal -->
    <div v-if="showAddUserModal || showEditUserModal" class="modal-overlay" @click="closeModals">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>{{ showAddUserModal ? 'Add New User' : 'Edit User' }}</h3>
          <button @click="closeModals" class="close-btn">&times;</button>
        </div>
        <form @submit.prevent="saveUser">
          <div class="form-group">
            <label for="userName">Name:</label>
            <input 
              id="userName" 
              v-model="userForm.name" 
              type="text" 
              required 
            />
          </div>
          <div class="form-group">
            <label for="userEmail">Email:</label>
            <input 
              id="userEmail" 
              v-model="userForm.email" 
              type="email" 
              required 
            />
          </div>
          <div v-if="showAddUserModal" class="form-group">
            <label for="userPassword">Password:</label>
            <input 
              id="userPassword" 
              v-model="userForm.password" 
              type="password" 
              required 
            />
          </div>
          <div class="form-group">
            <label for="userRole">Role:</label>
            <select id="userRole" v-model="userForm.role" required>
              <option value="staff">Staff</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="form-group">
            <label for="userStatus">Status:</label>
            <select id="userStatus" v-model="userForm.status" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="modal-actions">
            <button type="button" @click="closeModals" class="btn btn-secondary">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary">
              {{ showAddUserModal ? 'Create User' : 'Update User' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../services/axios';

export default {
  name: 'UserManagementView',
  data() {
    return {
      users: [],
      filteredUsers: [],
      currentUser: {},
      searchQuery: '',
      roleFilter: '',
      statusFilter: '',
      currentPage: 1,
      itemsPerPage: 10,
      showAddUserModal: false,
      showEditUserModal: false,
      userForm: {
        id: null,
        name: '',
        email: '',
        password: '',
        role: 'staff',
        status: 'active'
      }
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.filteredUsers.length / this.itemsPerPage);
    },
    paginatedUsers() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredUsers.slice(start, end);
    },
    activeUsers() {
      return this.users.filter(user => user.status === 'active').length;
    },
    adminUsers() {
      return this.users.filter(user => user.role === 'admin').length;
    },
    staffUsers() {
      return this.users.filter(user => user.role === 'staff').length;
    }
  },
  async mounted() {
    this.loadCurrentUser();
    await this.loadUsers();
  },
  methods: {
    loadCurrentUser() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.currentUser = JSON.parse(userData);
      }
    },
    async loadUsers() {
      try {
        // Mock data - replace with actual API call
        this.users = [
          {
            id: 1,
            name: 'Admin User',
            email: 'admin@example.com',
            role: 'admin',
            status: 'active',
            created_at: '2024-01-15T10:30:00Z',
            last_login: '2024-09-25T08:15:00Z'
          },
          {
            id: 2,
            name: 'John Doe',
            email: 'john@example.com',
            role: 'staff',
            status: 'active',
            created_at: '2024-02-20T14:20:00Z',
            last_login: '2024-09-24T16:45:00Z'
          },
          {
            id: 3,
            name: 'Jane Smith',
            email: 'jane@example.com',
            role: 'staff',
            status: 'inactive',
            created_at: '2024-03-10T09:00:00Z',
            last_login: null
          }
        ];
        this.filteredUsers = [...this.users];
      } catch (error) {
        console.error('Error loading users:', error);
      }
    },
    filterUsers() {
      let filtered = [...this.users];
      
      if (this.searchQuery) {
        filtered = filtered.filter(user => 
          user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          user.email.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
      }
      
      if (this.roleFilter) {
        filtered = filtered.filter(user => user.role === this.roleFilter);
      }
      
      if (this.statusFilter) {
        filtered = filtered.filter(user => user.status === this.statusFilter);
      }
      
      this.filteredUsers = filtered;
      this.currentPage = 1;
    },
    editUser(user) {
      this.userForm = { ...user };
      this.showEditUserModal = true;
    },
    async toggleUserStatus(user) {
      try {
        const newStatus = user.status === 'active' ? 'inactive' : 'active';
        user.status = newStatus;
        console.log(`User ${user.id} status changed to ${newStatus}`);
        await this.loadUsers();
      } catch (error) {
        console.error('Error updating user status:', error);
      }
    },
    async deleteUser(userId) {
      if (confirm('Are you sure you want to delete this user?')) {
        try {
          this.users = this.users.filter(user => user.id !== userId);
          this.filterUsers();
          console.log(`User ${userId} deleted`);
        } catch (error) {
          console.error('Error deleting user:', error);
        }
      }
    },
    async saveUser() {
      try {
        if (this.showAddUserModal) {
          // Add new user logic
          const newUser = {
            ...this.userForm,
            id: Date.now(),
            created_at: new Date().toISOString(),
            last_login: null
          };
          this.users.push(newUser);
        } else {
          // Update existing user logic
          const index = this.users.findIndex(user => user.id === this.userForm.id);
          if (index !== -1) {
            this.users[index] = { ...this.userForm };
          }
        }
        
        this.closeModals();
        this.filterUsers();
        console.log('User saved successfully');
      } catch (error) {
        console.error('Error saving user:', error);
      }
    },
    closeModals() {
      this.showAddUserModal = false;
      this.showEditUserModal = false;
      this.userForm = {
        id: null,
        name: '',
        email: '',
        password: '',
        role: 'staff',
        status: 'active'
      };
    },
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
    async refreshUsers() {
      await this.loadUsers();
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleString();
    }
  }
};
</script>

<style scoped>
@import "../../styles/users.css";
</style>
