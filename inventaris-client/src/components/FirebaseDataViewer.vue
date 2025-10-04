<template>
  <div class="firebase-data-viewer">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Data Firebase ({{ dataSource }})</h5>
        <div>
          <button 
            class="btn btn-sm btn-outline-primary me-2" 
            @click="syncData" 
            :disabled="isSyncing">
            <span v-if="isSyncing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            Sinkronkan Data
          </button>
          <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary" @click="changeSource('products')">Produk</button>
            <button class="btn btn-sm btn-outline-secondary" @click="changeSource('suppliers')">Supplier</button>
            <button class="btn btn-sm btn-outline-secondary" @click="changeSource('transactions')">Transaksi</button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div v-if="isLoading" class="d-flex justify-content-center">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-else-if="error" class="alert alert-danger">
          {{ error }}
        </div>
        <div v-else-if="!data || Object.keys(data).length === 0" class="alert alert-info">
          Tidak ada data {{ dataSource }} di Firebase. Klik tombol "Sinkronkan Data" untuk memulai sinkronisasi.
        </div>
        <div v-else>
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th v-for="header in tableHeaders" :key="header">{{ header }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, key) in processedData" :key="key">
                  <td v-for="header in tableHeaders" :key="`${key}-${header}`">
                    {{ formatCellValue(item[header.toLowerCase()]) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="card-footer text-muted">
        <small>Data terakhir diperbarui: {{ lastUpdated }}</small>
      </div>
    </div>
  </div>
</template>

<script>
import firebaseDatabase from '../services/firebaseDatabase';
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  name: 'FirebaseDataViewer',
  setup() {
    const data = ref(null);
    const isLoading = ref(true);
    const error = ref(null);
    const dataSource = ref('products');
    const isSyncing = ref(false);
    const lastUpdated = ref('Belum pernah');
    const tableHeaders = ref([]);
    const processedData = ref([]);
    
    // API base URL
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';
    
    // Load data from Firebase
    const loadData = async (source) => {
      isLoading.value = true;
      error.value = null;
      try {
        let result;
        switch (source) {
          case 'products':
            result = await firebaseDatabase.getProducts();
            break;
          case 'suppliers':
            result = await firebaseDatabase.getSuppliers();
            break;
          case 'transactions':
            result = await firebaseDatabase.getRecentTransactions(20);
            break;
          default:
            result = await firebaseDatabase.getProducts();
        }
        
        data.value = result;
        
        // Process data for display
        processData(result);
        
        lastUpdated.value = new Date().toLocaleString();
      } catch (err) {
        console.error('Error loading data from Firebase:', err);
        error.value = `Gagal memuat data: ${err.message}`;
      } finally {
        isLoading.value = false;
      }
    };
    
    // Process data for display in table
    const processData = (rawData) => {
      if (!rawData) {
        processedData.value = [];
        tableHeaders.value = [];
        return;
      }
      
      // Convert object to array if needed
      const dataArray = Array.isArray(rawData) ? rawData : Object.entries(rawData).map(([key, value]) => {
        return { key, ...value };
      });
      
      // Extract headers from first item
      if (dataArray.length > 0) {
        // Get headers (capitalize first letter)
        const firstItem = dataArray[0];
        const headers = Object.keys(firstItem).map(key => {
          return key.charAt(0).toUpperCase() + key.slice(1);
        });
        
        // Limit to first 6 columns for cleaner display
        tableHeaders.value = headers.slice(0, 6);
      } else {
        tableHeaders.value = [];
      }
      
      processedData.value = dataArray;
    };
    
    // Format cell value for display
    const formatCellValue = (value) => {
      if (value === null || value === undefined) {
        return '-';
      }
      if (typeof value === 'object') {
        return JSON.stringify(value).substring(0, 30) + '...';
      }
      if (typeof value === 'boolean') {
        return value ? 'Ya' : 'Tidak';
      }
      if (value.toString().length > 30) {
        return value.toString().substring(0, 30) + '...';
      }
      return value;
    };
    
    // Change data source
    const changeSource = (source) => {
      dataSource.value = source;
      loadData(source);
    };
    
    // Sync data from backend
    const syncData = async () => {
      isSyncing.value = true;
      try {
        // Call API to sync data
        const response = await axios.post(`${apiBaseUrl}/firebase/sync`, { 
          table: dataSource.value 
        }, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token') || localStorage.getItem('authToken')}`
          }
        });
        
        if (response.data.status === 'success') {
          // Reload data after sync
          setTimeout(() => loadData(dataSource.value), 1000);
        } else {
          error.value = 'Gagal sinkronisasi data';
        }
      } catch (err) {
        console.error('Error syncing data:', err);
        error.value = `Gagal sinkronisasi: ${err.response?.data?.message || err.message}`;
      } finally {
        isSyncing.value = false;
      }
    };
    
    // Initial load
    onMounted(() => {
      loadData(dataSource.value);
    });
    
    return {
      data,
      isLoading,
      error,
      dataSource,
      isSyncing,
      lastUpdated,
      tableHeaders,
      processedData,
      changeSource,
      syncData,
      formatCellValue
    };
  }
};
</script>

<style scoped>
.firebase-data-viewer {
  margin-top: 20px;
}

.card-header {
  background-color: #f8f9fa;
}

.btn-group .btn {
  font-size: 0.8rem;
}
</style>