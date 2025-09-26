# Struktur Folder Final Aplikasi Inventory

## Backend: inventaris-api/ (Laravel)

```
inventaris-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── ProductController.php
│   │   │   ├── StockController.php
│   │   │   ├── PurchaseOrderController.php
│   │   │   ├── SalesOrderController.php
│   │   │   └── ReportController.php
│   │   ├── Middleware/
│   │   │   ├── AuthMiddleware.php
│   │   │   └── RoleMiddleware.php
│   │   ├── Requests/
│   │   │   ├── ProductRequest.php
│   │   │   ├── StockRequest.php
│   │   │   └── OrderRequest.php
│   │   └── Resources/
│   │       ├── ProductResource.php
│   │       ├── StockResource.php
│   │       └── OrderResource.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Stock.php
│   │   ├── PurchaseOrder.php
│   │   ├── SalesOrder.php
│   │   └── Transaction.php
│   ├── Services/
│   │   ├── ProductService.php
│   │   ├── StockService.php
│   │   ├── OrderService.php
│   │   └── ReportService.php
│   └── Exceptions/
│       └── Handler.php
├── database/
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── create_products_table.php
│   │   ├── create_stocks_table.php
│   │   ├── create_purchase_orders_table.php
│   │   ├── create_sales_orders_table.php
│   │   └── create_transactions_table.php
│   ├── seeders/
│   │   ├── UserSeeder.php
│   │   ├── ProductSeeder.php
│   │   └── StockSeeder.php
│   └── factories/
│       ├── UserFactory.php
│       ├── ProductFactory.php
│       └── StockFactory.php
├── routes/
│   ├── api.php
│   └── web.php
├── config/
│   ├── cors.php
│   └── sanctum.php
├── tests/
│   ├── Feature/
│   │   ├── ProductTest.php
│   │   ├── StockTest.php
│   │   └── OrderTest.php
│   └── Unit/
│       ├── ProductServiceTest.php
│       ├── StockServiceTest.php
│       └── OrderServiceTest.php
└── .env
```

## Frontend: inventaris-client/ (Vue.js)

```
inventaris-client/
├── public/                     // Aset statis yang tidak diproses oleh Vite, bisa diakses langsung.
│ 	├── favicon.ico             // Ikon yang muncul di tab browser.
│ 	└── images/                 // Gambar-gambar umum untuk aplikasi.
├── src/                        // Folder utama berisi semua kode sumber aplikasi Anda.
│ 	├── assets/                 // Aset statis yang akan diproses oleh Vite (CSS, gambar, font).
│ 	│ 	├── css/                  // File styling global.
│ 	│ 	├── images/               // Gambar yang digunakan di dalam komponen Vue.
│ 	│ 	└── fonts/                // File font kustom.
│ 	├── components/             // Komponen Vue yang bisa digunakan kembali di berbagai halaman.
│ 	│ 	├── layout/               // Komponen untuk tata letak utama halaman.
│ 	│ 	│ 	├── AppLayout.vue       // [ADMIN & STAFF] - Kerangka utama yang berisi Sidebar, Header, dll.
│ 	│ 	│ 	├── Sidebar.vue         // [ADMIN & STAFF] - Menu navigasi. Isinya akan dinamis (menyembunyikan menu Admin untuk Staff).
│ 	│ 	│ 	├── Header.vue          // [ADMIN & STAFF] - Bagian atas halaman, biasanya berisi nama user dan tombol logout.
│ 	│ 	│ 	└── Footer.vue          // [ADMIN & STAFF] - Bagian bawah halaman.
│ 	│ 	├── ui/                   // [ADMIN & STAFF] - Komponen UI dasar dan atomik, "batu bata" aplikasi.
│ 	│ 	│ 	├── PrimaryButton.vue   // Tombol utama.
│ 	│ 	│ 	├── DataTable.vue       // Tabel untuk menampilkan data.
│ 	│ 	│ 	├── Modal.vue           // Jendela pop-up.
│ 	│ 	│ 	├── FormInput.vue       // Komponen input teks.
│ 	│ 	│ 	├── LoadingSpinner.vue  // Indikator saat memuat data.
│ 	│ 	│ 	└── AlertMessage.vue    // Pesan notifikasi (sukses, error).
│ 	│ 	├── charts/               // [ADMIN ONLY] - Komponen visualisasi data untuk laporan dan dashboard Admin.
│ 	│ 	│ 	├── BarChart.vue
│ 	│ 	│ 	└── LineChart.vue
│ 	│ 	└── forms/                // [ADMIN & STAFF] - Komponen form yang lebih kompleks, gabungan dari komponen 'ui'.
│ 	│ 	 	├── ProductForm.vue     // Form untuk membuat/mengedit data produk.
│ 	│ 	 	├── StockForm.vue       // Form untuk penyesuaian stok.
│ 	│ 	 	└── OrderForm.vue       // Form untuk membuat pesanan.
│ 	├── composables/              // [ADMIN & STAFF] - Kumpulan fungsi logika (Composition API) yang bisa dipakai ulang.
│ 	│ 	├── useApi.js             // Mengelola state pemanggilan API (loading, error, data).
│ 	│ 	├── useAuth.js            // Menyediakan info user dan status login.
│ 	│ 	└── useNotification.js    // Mengontrol notifikasi/alert.
│ 	├── middleware/               // [ADMIN & STAFF] - "Penjaga Gerbang" untuk setiap rute/halaman.
│ 	│ 	├── auth.js               // Memastikan hanya user yang sudah login (dan punya role sesuai) yang bisa masuk.
│ 	│ 	└── guest.js              // Memastikan hanya user yang BELUM login yang bisa akses (misal: halaman login).
│ 	├── router/                   // [ADMIN & STAFF] - Mengatur semua rute dan URL halaman.
│ 	│ 	└── index.js              // [SANGAT PENTING] Tempat mendefinisikan rute dan menerapkan middleware untuk melindunginya.
│ 	├── services/                 // [ADMIN & STAFF] - Jembatan antara aplikasi Vue dan API Backend.
│ 	│ 	├── axios.js              // Konfigurasi utama Axios (misal: base URL, interceptor).
│ 	│ 	├── api.js                // Kumpulan semua fungsi pemanggilan API (getProducts, createProduct, dll).
│ 	│ 	└── helpers/              // Fungsi-fungsi pembantu.
│ 	│ 	 	├── formatters.js     // Untuk memformat data (misal: tanggal, mata uang).
│ 	│ 	 	└── validators.js     // Untuk validasi input di sisi frontend.
│ 	├── stores/                   // [ADMIN & STAFF] - Pusat manajemen state global (data terpusat) dengan Pinia.
│ 	│ 	├── authStore.js          // [SANGAT PENTING] Menyimpan data user yang login, termasuk rolenya.
│ 	│ 	├── productStore.js       // Mengelola state data produk.
│ 	│ 	├── stockStore.js         // Mengelola state data stok.
│ 	│ 	├── orderStore.js         // Mengelola state data pesanan.
│ 	│ 	└── notificationStore.js  // Mengelola state untuk notifikasi global.
│ 	├── views/                    // Folder berisi file komponen untuk setiap halaman/fitur utama.
│ 	│ 	├── auth/                 // [ADMIN & STAFF] - Halaman yang berhubungan dengan otentikasi.
│ 	│ 	│ 	├── LoginView.vue
│ 	│ 	│ 	└── RegisterView.vue
│ 	│ 	├── dashboard/            // [ADMIN & STAFF] - Halaman utama setelah login, kontennya bisa berbeda tergantung role.
│ 	│ 	│ 	├── DashboardView.vue
│ 	│ 	│ 	└── components/         // Komponen-komponen spesifik hanya untuk halaman Dashboard.
│ 	│ 	│ 	 	├── StatsCard.vue     // Kartu statistik (misal: total produk, total penjualan).
│ 	│ 	│ 	 	└── RecentActivity.vue// Daftar aktivitas terbaru.
│ 	│ 	├── products/             // [STAFF UTAMA & ADMIN] - Fitur inti untuk manajemen produk.
│ 	│ 	│ 	├── ProductListView.vue   // Halaman untuk menampilkan semua produk.
│ 	│ 	│ 	├── ProductCreateView.vue // Halaman untuk menambah produk baru.
│ 	│ 	│ 	├── ProductEditView.vue   // Halaman untuk mengedit produk.
│ 	│ 	│ 	└── ProductDetailView.vue // Halaman untuk melihat detail satu produk.
│ 	│ 	├── stocks/               // [STAFF UTAMA & ADMIN] - Fitur inti untuk manajemen stok.
│ 	│ 	│ 	├── StockListView.vue     // Halaman untuk melihat semua stok.
│ 	│ 	│ 	├── StockAdjustmentView.vue // Halaman untuk melakukan stok opname/penyesuaian.
│ 	│ 	│ 	└── StockTransactionView.vue// Halaman untuk melihat riwayat pergerakan stok.
│ 	│ 	├── orders/               // [STAFF UTAMA & ADMIN] - Fitur inti untuk manajemen pesanan.
│ 	│ 	│ 	├── PurchaseOrderView.vue // Halaman untuk pesanan pembelian ke supplier.
│ 	│ 	│ 	├── SalesOrderView.vue    // Halaman untuk pesanan penjualan dari customer.
│ 	│ 	│ 	└── OrderDetailView.vue   // Halaman untuk melihat detail satu pesanan.
│ 	│ 	├── reports/              // [ADMIN ONLY] - Halaman laporan strategis untuk manajemen.
│ 	│ 	│ 	├── ReportView.vue
│ 	│ 	│ 	└── components/
│ 	│ 	│ 	 	├── StockReport.vue   // Komponen untuk menampilkan laporan stok.
│ 	│ 	│ 	 	└── SalesReport.vue   // Komponen untuk menampilkan laporan penjualan.
│ 	│ 	└── settings/             // [ADMIN ONLY] - Halaman pengaturan yang hanya bisa diakses Admin.
│ 	│ 	 	├── UserManagementView.vue  // Halaman untuk mengelola (CRUD) pengguna lain.
│ 	│ 	 	└── SystemSettingsView.vue  // Halaman untuk pengaturan umum aplikasi.
│ 	├── styles/                 // [ADMIN & STAFF] - File styling tambahan jika diperlukan.
│ 	├── utils/                  // [ADMIN & STAFF] - Fungsi-fungsi bantuan (utilities) umum.
│ 	│ 	├── constants.js          // Menyimpan nilai konstan (misal: URL API).
│ 	│ 	└── storage.js            // Helper untuk berinteraksi dengan LocalStorage/SessionStorage.
│ 	├── App.vue                   // [ADMIN & STAFF] - Komponen root/induk dari seluruh aplikasi Vue.
│ 	└── main.js                   // [ADMIN & STAFF] - Titik masuk aplikasi, tempat Vue, Pinia, dan Router diinisialisasi.
├── tests/                      // Folder untuk pengujian otomatis (unit test, e2e test).
└── vite.config.js              // File konfigurasi untuk Vite, build tool yang digunakan.
```

## Struktur Database yang Disarankan

```sql
-- Users (Pengguna sistem)
users: id, name, email, password, role, created_at, updated_at

-- Products (Produk)
products: id, name, description, sku, category_id, purchase_price, selling_price, created_at, updated_at

-- Stocks (Stok produk)
stocks: id, product_id, quantity, minimum_stock, location, created_at, updated_at

-- Transactions (Riwayat transaksi)
transactions: id, product_id, type, quantity, description, user_id, created_at, updated_at

-- Purchase Orders (Pesanan pembelian)
purchase_orders: id, supplier_id, total_amount, status, created_at, updated_at

-- Sales Orders (Pesanan penjualan)
sales_orders: id, customer_name, total_amount, status, created_at, updated_at
```

## Progres Proyek

### Backend:

1. **Autentikasi**:
   - Login dan register menggunakan Laravel Sanctum.
   - Middleware untuk melindungi rute API.
2. **Dashboard**:
   - Statistik produk, stok, dan transaksi.
   - Aktivitas terbaru pengguna.
3. **Manajemen Produk**:
   - CRUD produk dengan validasi.
   - Filter dan pencarian produk.
4. **Laporan**:
   - Laporan penjualan, stok, dan keuangan.
   - Ekspor laporan dalam format CSV dan Excel.
   - Laporan khusus berdasarkan rentang tanggal.
5. **Manajemen Role**:
   - Role `admin` dan `staff` dengan akses berbeda.
   - Proteksi rute berdasarkan role.

### Frontend:

1. **Autentikasi**:
   - Halaman login dan register dengan validasi.
   - Penyimpanan token autentikasi di `localStorage`.
2. **Dashboard**:
   - **Admin Dashboard**:
     - Statistik pengguna, produk, dan transaksi.
     - Akses ke laporan keuangan.
   - **Staff Dashboard**:
     - Statistik produk dan stok.
     - Akses ke laporan stok dan penjualan.
3. **Laporan**:
   - Halaman laporan dengan metrik utama seperti total pendapatan, stok rendah, dll.
   - Ekspor laporan dalam format Excel dan CSV.
   - Laporan khusus berdasarkan rentang tanggal.
4. **Manajemen Produk**:
   - Pencarian dan filter produk berdasarkan kategori dan status.
   - Navigasi halaman untuk daftar produk.
5. **Manajemen Role**:
   - Proteksi rute frontend berdasarkan role pengguna.

### Teknologi Tambahan yang Disarankan

#### Backend:

- Laravel Sanctum: Untuk API authentication
- Laravel Excel: Untuk export/import data
- Spatie Permission: Untuk role & permission management

#### Frontend:

- Chart.js/ApexCharts: Untuk visualisasi data
- Vue-toastification: Untuk notifikasi
- VeeValidate: Untuk form validation
- Tailwind CSS: Untuk styling (opsional)
