<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\SalesOrder;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation()
    {
        $response = $this->postJson('/api/orders', [
            'customer_name' => 'John Doe',
            'total_amount' => 500,
            'status' => 'pending',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('sales_orders', ['customer_name' => 'John Doe']);
    }
}