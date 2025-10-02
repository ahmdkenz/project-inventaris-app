<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Cek struktur tabel purchase_order_items
echo "Memeriksa struktur tabel purchase_order_items...\n";
try {
    $columns = DB::select("SHOW COLUMNS FROM purchase_order_items");
    echo "Struktur saat ini:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
    
    // Cek jika product_id adalah varchar
    $productIdIsVarchar = false;
    
    foreach ($columns as $column) {
        if ($column->Field === 'product_id' && strpos($column->Type, 'varchar') !== false) {
            $productIdIsVarchar = true;
        }
    }
    
    if (!$productIdIsVarchar) {
        echo "Mengubah tipe kolom 'product_id' menjadi varchar...\n";
        // Lepaskan foreign key dulu jika ada
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'purchase_order_items'
              AND COLUMN_NAME = 'product_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($foreignKeys as $key) {
            echo "Menghapus foreign key: {$key->CONSTRAINT_NAME}\n";
            DB::statement("ALTER TABLE purchase_order_items DROP FOREIGN KEY {$key->CONSTRAINT_NAME}");
        }
        
        // Ubah tipe kolom
        DB::statement("ALTER TABLE purchase_order_items MODIFY product_id VARCHAR(20) NOT NULL");
        
        echo "Tipe kolom 'product_id' berhasil diubah.\n";
    } else {
        echo "Kolom 'product_id' sudah bertipe varchar.\n";
    }
    
    // Cek struktur tabel products untuk referensi
    echo "\nMemeriksa struktur tabel products...\n";
    $columns = DB::select("SHOW COLUMNS FROM products");
    echo "Struktur products:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
    
    echo "Perbaikan selesai.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}