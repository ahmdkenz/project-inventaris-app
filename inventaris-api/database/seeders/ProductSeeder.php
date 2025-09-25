<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'Laptop Dell XPS 13',
                'description' => 'Laptop premium dengan performa tinggi.',
                'sku' => 'LAPTOP-DELL-XPS13',
                'category_id' => 1,
                'purchase_price' => 1200.00,
                'selling_price' => 1500.00,
            ],
            [
                'name' => 'Smartphone Samsung Galaxy S21',
                'description' => 'Smartphone flagship dengan kamera canggih.',
                'sku' => 'PHONE-SAMSUNG-S21',
                'category_id' => 2,
                'purchase_price' => 800.00,
                'selling_price' => 1000.00,
            ],
            [
                'name' => 'Headphone Sony WH-1000XM4',
                'description' => 'Headphone noise-cancelling terbaik.',
                'sku' => 'HEADPHONE-SONY-XM4',
                'category_id' => 3,
                'purchase_price' => 250.00,
                'selling_price' => 350.00,
            ],
            [
                'name' => 'Monitor LG UltraFine 4K',
                'description' => 'Monitor 4K dengan kualitas warna tinggi.',
                'sku' => 'MONITOR-LG-4K',
                'category_id' => 4,
                'purchase_price' => 500.00,
                'selling_price' => 700.00,
            ],
            [
                'name' => 'Keyboard Mechanical Keychron K6',
                'description' => 'Keyboard mekanikal dengan desain compact.',
                'sku' => 'KEYBOARD-KEYCHRON-K6',
                'category_id' => 5,
                'purchase_price' => 80.00,
                'selling_price' => 120.00,
            ],
            [
                'name' => 'Mouse Logitech MX Master 3',
                'description' => 'Mouse ergonomis dengan fitur canggih.',
                'sku' => 'MOUSE-LOGITECH-MX3',
                'category_id' => 6,
                'purchase_price' => 100.00,
                'selling_price' => 150.00,
            ],
            [
                'name' => 'Printer Canon PIXMA G2020',
                'description' => 'Printer multifungsi dengan tinta hemat.',
                'sku' => 'PRINTER-CANON-G2020',
                'category_id' => 7,
                'purchase_price' => 150.00,
                'selling_price' => 200.00,
            ],
            [
                'name' => 'Tablet Apple iPad Air',
                'description' => 'Tablet ringan dengan performa tinggi.',
                'sku' => 'TABLET-APPLE-IPAD-AIR',
                'category_id' => 8,
                'purchase_price' => 600.00,
                'selling_price' => 750.00,
            ],
            [
                'name' => 'Camera Nikon D3500',
                'description' => 'Kamera DSLR untuk pemula.',
                'sku' => 'CAMERA-NIKON-D3500',
                'category_id' => 9,
                'purchase_price' => 400.00,
                'selling_price' => 550.00,
            ],
            [
                'name' => 'Smartwatch Garmin Forerunner 245',
                'description' => 'Jam tangan pintar untuk olahraga.',
                'sku' => 'SMARTWATCH-GARMIN-245',
                'category_id' => 10,
                'purchase_price' => 300.00,
                'selling_price' => 400.00,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}