<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use App\Services\FirebaseService;

class MigrateToFirebase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-to-firebase {--model=all : Model yang akan dimigrasikan (all,products,stocks,suppliers,users,sales,purchases)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi data dari database SQL ke Firebase';

    /**
     * @var FirebaseService
     */
    protected $firebase;

    /**
     * Execute the console command.
     */
    public function handle(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
        
        $model = $this->option('model');
        
        $this->info('Mulai migrasi data ke Firebase...');
        
        if ($model === 'all' || $model === 'products') {
            $this->migrateProducts();
        }
        
        if ($model === 'all' || $model === 'stocks') {
            $this->migrateStocks();
        }
        
        if ($model === 'all' || $model === 'suppliers') {
            $this->migrateSuppliers();
        }
        
        if ($model === 'all' || $model === 'users') {
            $this->migrateUsers();
        }
        
        if ($model === 'all' || $model === 'sales') {
            $this->migrateSalesOrders();
        }
        
        if ($model === 'all' || $model === 'purchases') {
            $this->migratePurchaseOrders();
        }
        
        $this->info('Migrasi data ke Firebase selesai!');
    }
    
    /**
     * Migrasi data produk ke Firebase
     */
    protected function migrateProducts()
    {
        $this->info('Migrasi data produk...');
        $products = Product::all();
        $this->withProgressBar($products, function ($product) {
            $data = $product->toArray();
            $this->firebase->saveData('products', $data, (string) $product->id);
        });
        $this->newLine();
        $this->info('Migrasi data produk selesai!');
    }
    
    /**
     * Migrasi data stok ke Firebase
     */
    protected function migrateStocks()
    {
        $this->info('Migrasi data stok...');
        $stocks = Stock::all();
        $this->withProgressBar($stocks, function ($stock) {
            $data = $stock->toArray();
            $this->firebase->saveData('stocks', $data, (string) $stock->id);
        });
        $this->newLine();
        $this->info('Migrasi data stok selesai!');
    }
    
    /**
     * Migrasi data supplier ke Firebase
     */
    protected function migrateSuppliers()
    {
        $this->info('Migrasi data supplier...');
        $suppliers = Supplier::all();
        $this->withProgressBar($suppliers, function ($supplier) {
            $data = $supplier->toArray();
            $this->firebase->saveData('suppliers', $data, (string) $supplier->id);
        });
        $this->newLine();
        $this->info('Migrasi data supplier selesai!');
    }
    
    /**
     * Migrasi data pengguna ke Firebase
     */
    protected function migrateUsers()
    {
        $this->info('Migrasi data pengguna...');
        $users = User::all();
        $this->withProgressBar($users, function ($user) {
            // Hilangkan informasi sensitif
            $data = $user->makeHidden(['password', 'remember_token'])->toArray();
            $this->firebase->saveData('users', $data, (string) $user->id);
        });
        $this->newLine();
        $this->info('Migrasi data pengguna selesai!');
    }
    
    /**
     * Migrasi data pesanan penjualan ke Firebase
     */
    protected function migrateSalesOrders()
    {
        $this->info('Migrasi data pesanan penjualan...');
        $salesOrders = SalesOrder::with('items')->get();
        $this->withProgressBar($salesOrders, function ($salesOrder) {
            $data = $salesOrder->toArray();
            $this->firebase->saveData('sales_orders', $data, (string) $salesOrder->id);
            
            // Simpan item pesanan penjualan
            foreach ($salesOrder->items as $item) {
                $itemData = $item->toArray();
                $itemData['sales_order_id'] = (string) $salesOrder->id;
                $this->firebase->saveData('sales_order_items', $itemData, (string) $item->id);
            }
        });
        $this->newLine();
        $this->info('Migrasi data pesanan penjualan selesai!');
    }
    
    /**
     * Migrasi data pesanan pembelian ke Firebase
     */
    protected function migratePurchaseOrders()
    {
        $this->info('Migrasi data pesanan pembelian...');
        $purchaseOrders = PurchaseOrder::with('items')->get();
        $this->withProgressBar($purchaseOrders, function ($purchaseOrder) {
            $data = $purchaseOrder->toArray();
            $this->firebase->saveData('purchase_orders', $data, (string) $purchaseOrder->id);
            
            // Simpan item pesanan pembelian
            foreach ($purchaseOrder->items as $item) {
                $itemData = $item->toArray();
                $itemData['purchase_order_id'] = (string) $purchaseOrder->id;
                $this->firebase->saveData('purchase_order_items', $itemData, (string) $item->id);
            }
        });
        $this->newLine();
        $this->info('Migrasi data pesanan pembelian selesai!');
    }
}