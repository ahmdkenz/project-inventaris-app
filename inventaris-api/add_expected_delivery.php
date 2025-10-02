<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Periksa apakah kolom expected_delivery sudah ada
echo "Memeriksa kolom expected_delivery di tabel purchase_orders...\n";
$columns = DB::select("SHOW COLUMNS FROM purchase_orders");
$hasExpectedDelivery = false;

foreach ($columns as $column) {
    if ($column->Field === 'expected_delivery') {
        $hasExpectedDelivery = true;
        break;
    }
}

if (!$hasExpectedDelivery) {
    echo "Kolom expected_delivery belum ada, menambahkannya...\n";
    try {
        DB::statement("ALTER TABLE purchase_orders ADD COLUMN expected_delivery date NULL AFTER order_date");
        echo "Kolom expected_delivery berhasil ditambahkan.\n";
    } catch (\Exception $e) {
        echo "Error menambahkan kolom: " . $e->getMessage() . "\n";
    }
} else {
    echo "Kolom expected_delivery sudah ada di tabel.\n";
}

// Periksa struktur tabel lagi untuk konfirmasi
echo "\nStruktur tabel purchase_orders saat ini:\n";
$columns = DB::select("SHOW COLUMNS FROM purchase_orders");
foreach ($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}