<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;
use App\Models\Product;
use App\Models\User;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

class SyncToFirebase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-to-firebase {--table=all : Pilih tabel tertentu atau semua}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan data dari database SQL ke Firebase Realtime Database';

    /**
     * Execute the console command.
     */
    public function handle(FirebaseService $firebaseService)
    {
        $tableName = $this->option('table');
        
        if ($tableName === 'all' || $tableName === 'products') {
            $this->syncProducts($firebaseService);
        }
        
        if ($tableName === 'all' || $tableName === 'users') {
            $this->syncUsers($firebaseService);
        }
        
        if ($tableName === 'all' || $tableName === 'suppliers') {
            $this->syncSuppliers($firebaseService);
        }
        
        if ($tableName === 'all' || $tableName === 'transactions') {
            $this->syncTransactions($firebaseService);
        }
        
        if ($tableName === 'all' || $tableName === 'purchase_orders') {
            $this->syncPurchaseOrders($firebaseService);
        }
        
        if ($tableName === 'all' || $tableName === 'sales_orders') {
            $this->syncSalesOrders($firebaseService);
        }
        
        $this->info('Sinkronisasi data ke Firebase selesai!');
    }
    
    private function syncProducts(FirebaseService $firebaseService)
    {
        $this->info('Sinkronisasi tabel products...');
        $products = Product::all()->toArray();
        $result = $firebaseService->syncFromSQL('products', 'products', $products);
        
        if ($result) {
            $this->info('✓ ' . count($products) . ' produk berhasil disinkronkan');
        } else {
            $this->error('✗ Gagal menyinkronkan produk');
        }
    }
    
    private function syncUsers(FirebaseService $firebaseService)
    {
        $this->info('Sinkronisasi tabel users...');
        $users = User::select('id', 'name', 'email', 'role', 'status', 'last_login', 'created_at', 'updated_at')
                    ->get()
                    ->toArray();
        $result = $firebaseService->syncFromSQL('users', 'users', $users);
        
        if ($result) {
            $this->info('✓ ' . count($users) . ' pengguna berhasil disinkronkan');
        } else {
            $this->error('✗ Gagal menyinkronkan pengguna');
        }
    }
    
    private function syncSuppliers(FirebaseService $firebaseService)
    {
        $this->info('Sinkronisasi tabel suppliers...');
        $suppliers = Supplier::all()->toArray();
        $result = $firebaseService->syncFromSQL('suppliers', 'suppliers', $suppliers);
        
        if ($result) {
            $this->info('✓ ' . count($suppliers) . ' supplier berhasil disinkronkan');
        } else {
            $this->error('✗ Gagal menyinkronkan supplier');
        }
    }
    
    private function syncTransactions(FirebaseService $firebaseService)
    {
        $this->info('Sinkronisasi tabel transactions...');
        
        // Ambil transaksi dengan nama produk
        $transactions = DB::table('transactions')
            ->join('products', 'transactions.product_id', '=', 'products.id')
            ->select(
                'transactions.*',
                'products.name as product_name',
                'products.sku as product_sku'
            )
            ->get()
            ->toArray();
        
        // Convert to array of associative arrays
        $transactions = json_decode(json_encode($transactions), true);
        
        $result = $firebaseService->syncFromSQL('transactions', 'transactions', $transactions);
        
        if ($result) {
            $this->info('✓ ' . count($transactions) . ' transaksi berhasil disinkronkan');
        } else {
            $this->error('✗ Gagal menyinkronkan transaksi');
        }
    }
    
    private function syncPurchaseOrders(FirebaseService $firebaseService)
    {
        $this->info('Sinkronisasi tabel purchase_orders...');
        
        // Get purchase orders with items
        $purchaseOrders = PurchaseOrder::with('items')->get()->toArray();
        $result = $firebaseService->syncFromSQL('purchase_orders', 'purchase_orders', $purchaseOrders);
        
        if ($result) {
            $this->info('✓ ' . count($purchaseOrders) . ' purchase orders berhasil disinkronkan');
        } else {
            $this->error('✗ Gagal menyinkronkan purchase orders');
        }
    }
    
    private function syncSalesOrders(FirebaseService $firebaseService)
    {
        $this->info('Sinkronisasi tabel sales_orders...');
        
        // Get sales orders with items
        $salesOrders = SalesOrder::with('items')->get()->toArray();
        $result = $firebaseService->syncFromSQL('sales_orders', 'sales_orders', $salesOrders);
        
        if ($result) {
            $this->info('✓ ' . count($salesOrders) . ' sales orders berhasil disinkronkan');
        } else {
            $this->error('✗ Gagal menyinkronkan sales orders');
        }
    }
}