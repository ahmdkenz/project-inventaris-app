<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Stock;

class StockTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_adjustment()
    {
        $response = $this->postJson('/api/stocks', [
            'product_id' => 1,
            'quantity' => 10,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('stocks', ['product_id' => 1, 'quantity' => 10]);
    }
}