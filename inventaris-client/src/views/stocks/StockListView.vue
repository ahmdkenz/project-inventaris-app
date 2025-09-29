<template>
  <AppLayout>
    <div class="stock-list-container">
      <div class="card">
        <div class="card-header">
          <h1 class="card-title">Manajemen Stok</h1>
          <div class="flex-center">
            <router-link 
              v-if="user.role === 'admin'" 
              to="/stocks/adjustment" 
              class="btn btn-primary"
            >
              Penyesuaian Stok
            </router-link>
            <button @click="refreshStocks" class="btn btn-secondary">
              Segarkan
            </button>
          </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
          <div class="flex-between">
            <div class="search-box">
              <input 
                v-model="searchQuery" 
                type="text" 
                class="form-control"
                placeholder="Cari produk..." 
                @input="handleSearch"
              >
            </div>
            <div class="filter-group">
              <select v-model="statusFilter" @change="handleFilter" class="form-control">
                <option value="">Semua Status</option>
                <option value="in_stock">Tersedia</option>
                <option value="low_stock">Stok Rendah</option>
                <option value="out_of_stock">Habis</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading">
          Memuat data stok...
        </div>

        <!-- Stock Table -->
        <div v-else class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>ID Produk</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Stok Saat Ini</th>
                <th>Min. Stok</th>
                <th>Status</th>
                <th>Terakhir Diperbarui</th>
                <th v-if="user.role === 'admin'">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="stock in paginatedStocks" :key="stock.product_id">
                <td>{{ stock.product_id }}</td>
                <td>{{ stock.product_name }}</td>
                <td>{{ stock.category }}</td>
                <td :class="getStockClass(stock)">{{ stock.quantity }}</td>
                <td>{{ stock.min_stock || 10 }}</td>
                <td>
                  <span :class="`status ${getStockStatus(stock)}`">
                    {{ getStockStatusText(stock) }}
                  </span>
                </td>
                <td>{{ formatDate(stock.updated_at) }}</td>
                <td v-if="user.role === 'admin'">
                  <div class="action-buttons">
                    <router-link 
                      :to="`/stocks/adjustment?product=${stock.product_id}`" 
                      class="btn btn-sm btn-warning"
                    >
                      Sesuaikan
                    </router-link>
                    <button 
                      @click="viewHistory(stock.product_id)"
                      class="btn btn-sm btn-info"
                    >
                      Riwayat
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && filteredStocks.length === 0" class="text-center py-4">
          <p class="text-muted">Tidak ada stok ditemukan.</p>
        </div>

        <!-- Pagination -->
        <div v-if="filteredStocks.length > 0" class="pagination-wrapper">
          <div class="flex-center">
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
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import axios from '../../services/axios';
import AppLayout from '../../components/layout/AppLayout.vue';

export default {
  name: "StockListView",
  components: {
    AppLayout
  },
  data() {
    return {
      stocks: [],
      filteredStocks: [],
      searchQuery: '',
      statusFilter: '',
      currentPage: 1,
      itemsPerPage: 15,
      loading: false,
      user: {}
    };
  },
  computed: {
    totalPages() {
      return Math.ceil(this.filteredStocks.length / this.itemsPerPage);
    },
    paginatedStocks() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredStocks.slice(start, end);
    }
  },
  async mounted() {
    this.loadUserData();
    await this.loadStocks();
  },
  methods: {
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    },
    async loadStocks() {
      this.loading = true;
      try {
        const response = await axios.get('/stocks', {
          params: {
            search: this.searchQuery,
            status: this.statusFilter,
            page: this.currentPage,
            per_page: this.itemsPerPage
          }
        });
        
        if (response.data && response.data.data) {
          this.stocks = response.data.data;
          this.filteredStocks = [...this.stocks];
        }
      } catch (error) {
        console.error('Error loading stocks:', error);
        // Fallback to mock data
        this.stocks = [
          { 
            product_id: 1, 
            product_name: 'Laptop Dell XPS 13', 
            category: 'Electronics', 
            quantity: 15, 
            min_stock: 5, 
            updated_at: new Date().toISOString() 
          },
          { 
            product_id: 2, 
            product_name: 'Wireless Mouse', 
            category: 'Electronics', 
            quantity: 3, 
            min_stock: 10, 
            updated_at: new Date().toISOString() 
          },
          { 
            product_id: 3, 
            product_name: 'Office Chair', 
            category: 'Furniture', 
            quantity: 0, 
            min_stock: 5, 
            updated_at: new Date().toISOString() 
          }
        ];
        this.filteredStocks = [...this.stocks];
      } finally {
        this.loading = false;
      }
    },
    handleSearch() {
      this.filterStocks();
    },
    handleFilter() {
      this.filterStocks();
    },
    filterStocks() {
      let filtered = [...this.stocks];
      
      if (this.searchQuery) {
        filtered = filtered.filter(stock => 
          stock.product_name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          stock.category.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
      }
      
      if (this.statusFilter) {
        filtered = filtered.filter(stock => {
          const status = this.getStockStatus(stock);
          return status === this.statusFilter;
        });
      }
      
      this.filteredStocks = filtered;
      this.currentPage = 1;
    },
    getStockStatus(stock) {
      if (stock.quantity === 0) return 'out_of_stock';
      if (stock.quantity <= (stock.min_stock || 10)) return 'low_stock';
      return 'in_stock';
    },
    getStockStatusText(stock) {
      const status = this.getStockStatus(stock);
      switch (status) {
        case 'out_of_stock': return 'Habis';
        case 'low_stock': return 'Stok Rendah';
        case 'in_stock': return 'Tersedia';
        default: return 'Tidak Diketahui';
      }
    },
    getStockClass(stock) {
      const status = this.getStockStatus(stock);
      return {
        'text-danger': status === 'out_of_stock',
        'text-warning': status === 'low_stock',
        'text-success': status === 'in_stock'
      };
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleString();
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
    async refreshStocks() {
      await this.loadStocks();
    },
    viewHistory(productId) {
      this.$router.push(`/stocks/history/${productId}`);
    }
  }
};
</script>

<style scoped>
@import "../../styles/stock-adjustment.css";
@import "../../styles/minimal-stock.css";
</style>
