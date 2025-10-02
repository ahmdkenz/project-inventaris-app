# Integrasi Firebase dengan Aplikasi Inventaris

Dokumen ini menjelaskan cara menggunakan Firebase dalam aplikasi Inventaris yang sudah ada.

## Pendahuluan

Aplikasi Inventaris menggunakan Firebase sebagai layanan tambahan untuk menyimpan data yang dapat diakses dari frontend (yang di-deploy di Vercel) tanpa perlu migrasi penuh dari database SQL.

## 1. Konfigurasi Backend (Laravel)

### Sinkronisasi Data

Backend akan secara otomatis menyinkronkan data ke Firebase saat:

- Data produk diubah
- Data supplier diubah
- Purchase order dibuat/diubah
- Sales order dibuat/diubah
- Stok diubah

### API Sinkronisasi Manual

Tersedia API endpoint untuk sinkronisasi manual:

```http
POST /api/firebase/sync
```

Contoh body request:

```json
{
  "table": "products" // atau "all", "suppliers", "transactions", dll
}
```

## 2. Penggunaan di Frontend

### Akses Data Firebase

Import service Firebase Database:

```javascript
import firebaseDatabase from "@/services/firebaseDatabase";
```

#### Contoh penggunaan:

```javascript
// Mendapatkan semua produk
const products = await firebaseDatabase.getProducts();

// Mendapatkan semua supplier
const suppliers = await firebaseDatabase.getSuppliers();

// Mendapatkan transaksi terbaru
const transactions = await firebaseDatabase.getRecentTransactions(10);
```

### Component FirebaseDataViewer

Tersedia component `FirebaseDataViewer.vue` untuk melihat dan menyinkronkan data:

```vue
<template>
  <FirebaseDataViewer />
</template>

<script>
import FirebaseDataViewer from "@/components/FirebaseDataViewer.vue";

export default {
  components: {
    FirebaseDataViewer,
  },
};
</script>
```

## 3. Sinkronisasi Menggunakan Command Line

Anda dapat menjalankan command berikut untuk sinkronisasi manual:

```bash
# Sinkronisasi semua tabel
php artisan app:sync-to-firebase

# Sinkronisasi tabel tertentu
php artisan app:sync-to-firebase --table=products
php artisan app:sync-to-firebase --table=suppliers
php artisan app:sync-to-firebase --table=transactions
```

## 4. Diagram Aliran Data

```
┌─────────────┐     ┌──────────────┐     ┌────────────────┐
│ MySQL/SQL   │────>│ Laravel API   │────>│ Firebase      │
│ Database    │<────│ (Backend)     │<────│ Realtime DB   │
└─────────────┘     └──────────────┘     └────────────────┘
                           ▲                     ▲
                           │                     │
                           ▼                     │
                    ┌──────────────┐             │
                    │ Vue Frontend │<────────────┘
                    │ (Vercel)     │
                    └──────────────┘
```

## 5. Keuntungan Integrasi Ini

1. **Akses Real-time**: Data tersedia secara real-time di frontend
2. **Tidak Perlu Migrasi Penuh**: Database SQL tetap sebagai database utama
3. **Sinkronisasi Otomatis**: Data tetap konsisten di kedua database
4. **Frontend yang Terpisah**: Frontend dapat mengakses data langsung dari Firebase
