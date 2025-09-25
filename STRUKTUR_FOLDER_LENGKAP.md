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
├── public/
│   ├── favicon.ico
│   └── images/
├── src/
│   ├── assets/
│   │   ├── css/
│   │   │   ├── main.css
│   │   │   └── components.css
│   │   ├── images/
│   │   └── fonts/
│   ├── components/
│   │   ├── layout/
│   │   │   ├── AppLayout.vue
│   │   │   ├── Sidebar.vue
│   │   │   ├── Header.vue
│   │   │   └── Footer.vue
│   │   ├── ui/
│   │   │   ├── PrimaryButton.vue
│   │   │   ├── SecondaryButton.vue
│   │   │   ├── DataTable.vue
│   │   │   ├── Modal.vue
│   │   │   ├── FormInput.vue
│   │   │   ├── FormSelect.vue
│   │   │   ├── LoadingSpinner.vue
│   │   │   └── AlertMessage.vue
│   │   ├── charts/
│   │   │   ├── BarChart.vue
│   │   │   └── LineChart.vue
│   │   └── forms/
│   │       ├── ProductForm.vue
│   │       ├── StockForm.vue
│   │       └── OrderForm.vue
│   ├── composables/
│   │   ├── useApi.js
│   │   ├── useAuth.js
│   │   └── useNotification.js
│   ├── middleware/
│   │   ├── auth.js
│   │   └── guest.js
│   ├── router/
│   │   └── index.js
│   ├── services/
│   │   ├── axios.js
│   │   ├── api.js
│   │   └── helpers/
│   │       ├── formatters.js
│   │       └── validators.js
│   ├── stores/
│   │   ├── authStore.js
│   │   ├── productStore.js
│   │   ├── stockStore.js
│   │   ├── orderStore.js
│   │   └── notificationStore.js
│   ├── views/
│   │   ├── auth/
│   │   │   ├── LoginView.vue
│   │   │   └── RegisterView.vue
│   │   ├── dashboard/
│   │   │   ├── DashboardView.vue
│   │   │   └── components/
│   │   │       ├── StatsCard.vue
│   │   │       └── RecentActivity.vue
│   │   ├── products/
│   │   │   ├── ProductListView.vue
│   │   │   ├── ProductCreateView.vue
│   │   │   ├── ProductEditView.vue
│   │   │   └── ProductDetailView.vue
│   │   ├── stocks/
│   │   │   ├── StockListView.vue
│   │   │   ├── StockAdjustmentView.vue
│   │   │   └── StockTransactionView.vue
│   │   ├── orders/
│   │   │   ├── PurchaseOrderView.vue
│   │   │   ├── SalesOrderView.vue
│   │   │   └── OrderDetailView.vue
│   │   ├── reports/
│   │   │   ├── ReportView.vue
│   │   │   └── components/
│   │   │       ├── StockReport.vue
│   │   │       └── SalesReport.vue
│   │   └── settings/
│   │       ├── UserManagementView.vue
│   │       └── SystemSettingsView.vue
│   ├── styles/
│   │   ├── variables.css
│   │   └── utilities.css
│   ├── utils/
│   │   ├── constants.js
│   │   └── storage.js
│   ├── App.vue
│   └── main.js
├── tests/
│   ├── unit/
│   └── e2e/
└── vite.config.js
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
