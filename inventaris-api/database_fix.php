<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Cek struktur tabel purchase_orders
echo "Memeriksa struktur tabel purchase_orders...\n";
try {
    $columns = DB::select("SHOW COLUMNS FROM purchase_orders");
    echo "Struktur saat ini:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
    
    // Cek apakah kolom yang dibutuhkan ada
    $hasOrderDate = false;
    $hasNotes = false;
    
    foreach ($columns as $column) {
        if ($column->Field === 'order_date') {
            $hasOrderDate = true;
        }
        if ($column->Field === 'notes') {
            $hasNotes = true;
        }
    }
    
    // Tambahkan kolom yang hilang
    if (!$hasOrderDate) {
        echo "Menambahkan kolom 'order_date'...\n";
        DB::statement("ALTER TABLE purchase_orders ADD COLUMN order_date date NOT NULL AFTER supplier_id");
    }
    
    if (!$hasNotes) {
        echo "Menambahkan kolom 'notes'...\n";
        DB::statement("ALTER TABLE purchase_orders ADD COLUMN notes text NULL AFTER status");
    }
    
    echo "Perbaikan selesai.\n";
    
    // Periksa struktur terbaru
    $columns = DB::select("SHOW COLUMNS FROM purchase_orders");
    echo "Struktur terbaru:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}