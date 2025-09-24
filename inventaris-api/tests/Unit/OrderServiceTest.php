<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\OrderService;
use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order()
    {
        $service = new OrderService();
        $order = $service->createOrder([
            'customer_name' => 'John Doe',
            'total_amount' => 500,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('sales_orders', ['customer_name' => 'John Doe']);
    }
}