<template>
  <AppLayout>
    <!-- User Stats -->
    <div class="user-stats">
      <div class="stat-card">
        <h3>Total Pengguna</h3>
        <span class="stat-number">{{ users.length }}</span>
      </div>
      <div class="stat-card">
        <h3>Pengguna Aktif</h3>
        <span class="stat-number">{{ activeUsers }}</span>
      </div>
      <div class="stat-card">
        <h3>Admin</h3>
        <span class="stat-number">{{ adminUsers }}</span>
      </div>
      <div class="stat-card">
        <h3>Staff</h3>
        <span class="stat-number">{{ staffUsers }}</span>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters">
      <div class="search-box">
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Cari pengguna..." 
          @input="filterUsers"
        >
      </div>
      <div class="filter-group">
        <select v-model="roleFilter" @change="filterUsers">
          <option value="">Semua Role</option>
          <option value="admin">Admin</option>
          <option value="staff">Staff</option>
        </select>
        <select v-model="statusFilter" @change="filterUsers">
          <option value="">Semua Status</option>
          <option value="active">Aktif</option>
          <option value="inactive">Nonaktif</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <p>Memuat data pengguna...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="error-state">
      <p>{{ error }}</p>
      <button @click="loadUsers" class="btn btn-primary">Coba Lagi</button>
    </div>

    <!-- Users Table -->
    <div v-if="!loading && !error" class="table-container">
      <table class="users-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Dibuat Pada</th>
            <th>Login Terakhir</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in paginatedUsers" :key="user.id">
            <td>{{ user.id }}</td>
            <td>
              <div class="user-info">
                <div class="avatar">{{ (user.name || '?').charAt(0).toUpperCase() }}</div>
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
            <td>{{ user.last_login ? formatDate(user.last_login) : 'Belum Pernah' }}</td>
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
                  {{ user.status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
                <button 
                  v-if="user.id !== currentUser.id"
                  @click="deleteUser(user.id)" 
                  class="btn btn-sm btn-danger"
                >
                  Hapus
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="paginatedUsers.length === 0">
            <td colspan="8" style="text-align: center; padding: 20px;">
              Tidak ada data pengguna ditemukan
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Buttons moved below the table -->
      <div class="actions">
        <button @click="showAddUserModal = true" class="btn btn-primary">
          Tambah Pengguna Baru
        </button>
        <button @click="refreshUsers" class="btn btn-secondary">
          Refresh
        </button>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="!loading && !error && totalPages > 1" class="pagination">
      <button 
        @click="previousPage" 
        :disabled="currentPage <= 1"
        class="btn btn-secondary"
      >
        Sebelumnya
      </button>
      <span class="page-info">
        Halaman {{ currentPage }} dari {{ totalPages }}
      </span>
      <button 
        @click="nextPage" 
        :disabled="currentPage >= totalPages"
        class="btn btn-secondary"
      >
        Selanjutnya
      </button>
    </div>

    <!-- Add/Edit User Modal -->
    <div v-if="showAddUserModal || showEditUserModal" class="modal-overlay" @click="closeModals">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>{{ showAddUserModal ? 'Tambah Pengguna Baru' : 'Edit Pengguna' }}</h3>
          <button @click="closeModals" class="close-btn">&times;</button>
        </div>
        <form @submit.prevent="saveUser">
          <div class="form-group">
            <label for="userName">Nama:</label>
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
          <div v-if="showEditUserModal" class="form-group">
            <label for="userPassword">Password (Kosongkan jika tidak ingin mengubah):</label>
            <input 
              id="userPassword" 
              v-model="userForm.password" 
              type="password" 
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
              <option value="active">Aktif</option>
              <option value="inactive">Nonaktif</option>
            </select>
          </div>
          <div class="modal-actions">
            <button type="button" @click="closeModals" class="btn btn-secondary">
              Batal
            </button>
            <button type="submit" class="btn btn-primary">
              {{ showAddUserModal ? 'Buat Pengguna' : 'Perbarui Pengguna' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import axios from '@/services/axios';
import AppLayout from '@/components/layout/AppLayout.vue';

export default {
  name: 'UserManagementView',
  components: {
    AppLayout
  },
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
      loading: false,
      error: null,
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
    // Polling untuk realtime refresh
    this._usersInterval = setInterval(() => {
      this.loadUsers();
    }, 10000);
  },
  beforeUnmount() {
    if (this._usersInterval) clearInterval(this._usersInterval);
  },
  methods: {
    loadCurrentUser() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.currentUser = JSON.parse(userData);
      }
    },
    async loadUsers() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/users');
        console.log('Data pengguna:', response.data);
        this.users = response.data;
        // Terapkan filter yang sedang aktif setelah refresh
        this.filteredUsers = [...this.users];
        this.filterUsers();
      } catch (error) {
        console.error('Error loading users:', error);
        this.error = 'Gagal memuat data pengguna. Silakan coba lagi.';
      } finally {
        this.loading = false;
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
        const confirmMessage = `Apakah Anda yakin ingin ${newStatus === 'active' ? 'mengaktifkan' : 'menonaktifkan'} pengguna ${user.name}?`;
        
        if (confirm(confirmMessage)) {
          await axios.patch(`/users/${user.id}/status`, { status: newStatus });
          user.status = newStatus;
          await this.loadUsers();
        }
      } catch (error) {
        console.error('Error updating user status:', error);
        alert('Gagal mengubah status pengguna. Silakan coba lagi.');
      }
    },
    async deleteUser(userId) {
      const user = this.users.find(u => u.id === userId);
      if (confirm(`Apakah Anda yakin ingin menghapus pengguna ${user?.name}? Tindakan ini tidak dapat dibatalkan.`)) {
        try {
          await axios.delete(`/users/${userId}`);
          this.users = this.users.filter(user => user.id !== userId);
          this.filterUsers();
          alert('Pengguna berhasil dihapus.');
        } catch (error) {
          console.error('Error deleting user:', error);
          alert('Gagal menghapus pengguna. Silakan coba lagi.');
        }
      }
    },
    async saveUser() {
      try {
        if (this.showAddUserModal) {
          // Add new user logic
          const response = await axios.post('/users', this.userForm);
          this.users.push(response.data);
          alert('Pengguna baru berhasil dibuat.');
        } else {
          // Update existing user logic - exclude password if empty
          const updateData = { ...this.userForm };
          if (!updateData.password) {
            delete updateData.password;
          }
          
          await axios.put(`/users/${this.userForm.id}`, updateData);
          const index = this.users.findIndex(user => user.id === this.userForm.id);
          if (index !== -1) {
            this.users[index] = { ...this.users[index], ...updateData };
          }
          alert('Data pengguna berhasil diperbarui.');
        }
        
        this.closeModals();
        this.filterUsers();
        await this.loadUsers(); // Refresh data from server
      } catch (error) {
        console.error('Error saving user:', error);
        if (error.response && error.response.data && error.response.data.message) {
          alert(`Gagal menyimpan pengguna: ${error.response.data.message}`);
        } else {
          alert('Gagal menyimpan pengguna. Silakan coba lagi.');
        }
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
@import "@/styles/users.css";
@import "@/styles/responsive-fixes.css";

.user-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.filters {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1rem;
}

.actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.table-container {
  overflow-x: auto;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}

.users-table th, .users-table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #ddd;
}
</style>
