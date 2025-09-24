<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\StockService;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_adjust_stock()
    {
        $service = new StockService();
        $service->adjustStock([
            'product_id' => 1,
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('stocks', ['product_id' => 1, 'quantity' => 10]);
    }
}