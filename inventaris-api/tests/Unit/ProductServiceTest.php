<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ProductService;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_product()
    {
        $service = new ProductService();
        $product = $service->createProduct([
            'name' => 'Test Product',
            'sku' => 'TEST123',
            'purchase_price' => 100,
            'selling_price' => 150,
        ]);

        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }
}