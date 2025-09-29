<template>
  <AppLayout>
    <template #header>
    </template>
    <FormWrapper
      title="Tambah Produk"
      backLink="/products"
      backText="Kembali ke Daftar Produk"
      :error="error"
    >
      <form @submit.prevent="createProduct" class="product-form">
      <div class="grid grid-2">
        <div class="form-group">
          <label for="name">Nama Produk *</label>
          <input 
            id="name" 
            v-model="product.name" 
            type="text" 
            class="form-control"
            required 
            placeholder="Masukkan nama produk"
            @input="generateProductSku"
          />
        </div>
        
        <div class="form-group">
          <label for="category">Kategori *</label>
          <select 
            id="category" 
            v-model="product.category" 
            class="form-control" 
            required
            @change="generateProductSku"
          >
            <option value="">Pilih Kategori</option>
            <option v-for="category in categories" :key="category" :value="category">
              {{ category }}
            </option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="sku">SKU (ID Produk)</label>
          <input 
            id="sku" 
            v-model="product.sku" 
            type="text" 
            class="form-control"
            readonly
            placeholder="Auto-generate setelah input nama dan kategori"
          />
        </div>
        
        <div class="form-group">
          <label for="purchase_price">Harga Beli (Rp) *</label>
          <input 
            id="purchase_price" 
            :value="formattedPurchasePrice"
            @input="handlePurchasePriceInput"
            type="text"
            class="form-control idr-input"
            required 
            placeholder="0"
          />
        </div>
        
        <div class="form-group">
          <label for="selling_price">Harga Jual (Rp) *</label>
          <input 
            id="selling_price" 
            :value="formattedSellingPrice"
            @input="handleSellingPriceInput"
            type="text"
            class="form-control idr-input"
            required 
            placeholder="0"
          />
        </div>
        
        <div class="form-group">
          <label for="stock">Stok Awal *</label>
          <input 
            id="stock" 
            v-model="product.stock" 
            type="number" 
            class="form-control"
            required 
            placeholder="0"
          />
        </div>
        
        <div class="form-group">
          <label for="min_stock">Stok Minimum</label>
          <input 
            id="min_stock" 
            v-model="product.min_stock" 
            type="number" 
            class="form-control"
            placeholder="10"
          />
        </div>
      </div>
      
      <div class="form-group">
        <label for="description">Deskripsi</label>
        <textarea 
          id="description" 
          v-model="product.description" 
          rows="4"
          class="form-control"
          placeholder="Masukkan deskripsi produk"
        ></textarea>
      </div>
      
      <div class="flex-center">
        <button type="submit" class="btn btn-primary btn-lg" :disabled="isSubmitting">
          {{ isSubmitting ? 'Sedang Membuat...' : 'Buat Produk' }}
        </button>
      </div>
    </form>
  </FormWrapper>
  </AppLayout>
</template>

<script>
import FormWrapper from '@/components/common/FormWrapper.vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import axios from '@/services/axios';
import { formatIDR, parseIDR } from '@/utils/formatters';

export default {
  name: 'ProductCreateView',
  components: {
    FormWrapper,
    AppLayout
  },
  data() {
    return {
      product: {
        name: '',
        description: '',
        purchase_price: '',
        selling_price: '',
        stock: '',
        category: '',
        min_stock: 10,
        sku: ''
      },
      categories: [
        'Elektronik',
        'Pakaian', 
        'Buku',
        'Rumah & Taman',
        'Olahraga',
        'Mainan',
        'Otomotif',
        'Kesehatan & Kecantikan'
      ],
      isSubmitting: false,
      error: null
    };
  },
  computed: {
    formattedPurchasePrice() {
      return formatIDR(this.product.purchase_price);
    },
    formattedSellingPrice() {
      return formatIDR(this.product.selling_price);
    }
  },
  methods: {
    handlePurchasePriceInput(event) {
      // Hanya izinkan angka dan titik
      const rawValue = event.target.value.replace(/[^\d]/g, '');
      // Simpan nilai numerik (tanpa format)
      this.product.purchase_price = rawValue ? parseInt(rawValue) : '';
    },
    handleSellingPriceInput(event) {
      // Hanya izinkan angka dan titik
      const rawValue = event.target.value.replace(/[^\d]/g, '');
      // Simpan nilai numerik (tanpa format)
      this.product.selling_price = rawValue ? parseInt(rawValue) : '';
    },
    generateProductSku() {
      // Hanya generate jika nama dan kategori sudah diisi
      if (!this.product.name || !this.product.category) {
        this.product.sku = '';
        return;
      }
      
      // Generate SKU dengan format: [CAT][NAME][TANGGAL][RANDOM]
      const prefix = this.product.category.substring(0, 3).toUpperCase();
      const namePrefix = this.product.name.replace(/\s+/g, '').substring(0, 3).toUpperCase();
      
      // Format tanggal: yymmdd
      const now = new Date();
      const year = now.getFullYear().toString().slice(-2);
      const month = String(now.getMonth() + 1).padStart(2, '0');
      const day = String(now.getDate()).padStart(2, '0');
      const timestamp = year + month + day;
      
      // 3 digit random
      const random = String(Math.floor(Math.random() * 1000)).padStart(3, '0');
      
      this.product.sku = prefix + namePrefix + timestamp + random;
    },
    async createProduct() {
      this.isSubmitting = true;
      this.error = null;

      try {
        console.log('Mengirim data produk:', this.product);
        const response = await axios.post('/products', {
          name: this.product.name,
          description: this.product.description || null,
          category: this.product.category,
          purchase_price: parseFloat(this.product.purchase_price),
          selling_price: parseFloat(this.product.selling_price),
          stock: Number(this.product.stock),
          min_stock: Number(this.product.min_stock || 10),
          sku: this.product.sku || null, // Mengirim SKU yang sudah di-generate
        });

        console.log('Respon API:', response.data);

        if (response.data) {
          this.$router.push('/products');
        }
      } catch (error) {
        console.error('Kesalahan saat membuat produk:', error.response || error.message);
        if (error.response) {
          console.error('Detail kesalahan:', error.response.data);
        }
        // Tampilkan pesan validasi jika ada
        if (error.response?.status === 422) {
          const errs = error.response.data.errors || {};
          const firstKey = Object.keys(errs)[0];
          this.error = errs[firstKey]?.[0] || 'Validasi formulir gagal. Silakan periksa input Anda.';
        } else {
          this.error = error.response?.data?.message || 'Gagal membuat produk. Silakan coba lagi.';
        }
      } finally {
        this.isSubmitting = false;
      }
    }
  }
};
</script>

<style>
@import '@/styles/product-form.css';
@import '@/styles/minimal-product.css';
</style>
