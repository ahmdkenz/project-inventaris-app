<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\PurchaseOrderController;
use App\Services\StockService;
use App\Services\OrderService;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

DB::enableQueryLog();

// Buat instance dari controller dan service yang dibutuhkan
$stockService = new StockService();
$orderService = new OrderService($stockService);
$supplierService = new SupplierService();
$controller = new PurchaseOrderController($stockService, $orderService, $supplierService);

// Buat request dengan data
$requestData = [
    'po_number' => 'PO202510TEST',
    'supplier_id' => 2,
    'supplier_name' => 'PT.DUTA OTO RAYA',
    'order_date' => '2025-10-02',
    'expected_delivery' => '2025-10-05',
    'notes' => 'Test Order',
    'items' => [
        [
            'product_id' => 'PRD-147371-1057',
            'quantity' => 5,
            'unit_price' => 100000
        ]
    ]
];

$request = new Request($requestData);

// Eksekusi store method
try {
    $response = $controller->store($request);
    
    // Tampilkan respons
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";
    
    // Tampilkan query yang dijalankan
    echo "\nQueries executed:\n";
    foreach (DB::getQueryLog() as $index => $query) {
        echo ($index + 1) . ". {$query['query']}\n";
        echo "   Bindings: " . json_encode($query['bindings']) . "\n";
        echo "   Time: {$query['time']} ms\n\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
    
    // Tampilkan query yang dijalankan meski error
    echo "\nQueries executed before error:\n";
    foreach (DB::getQueryLog() as $index => $query) {
        echo ($index + 1) . ". {$query['query']}\n";
        echo "   Bindings: " . json_encode($query['bindings']) . "\n";
        echo "   Time: {$query['time']} ms\n\n";
    }
}