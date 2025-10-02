<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;

// Lihat struktur tabel products
echo "Struktur tabel products:\n";
$columns = DB::select("SHOW COLUMNS FROM products");
foreach ($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}

// Ambil beberapa produk
echo "\nBeberapa produk dari database:\n";
$products = Product::take(5)->get();
foreach ($products as $index => $product) {
    echo ($index + 1) . ". ID: {$product->id}, Name: {$product->name}\n";
}