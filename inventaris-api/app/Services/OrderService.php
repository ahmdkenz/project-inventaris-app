<?php

namespace App\Services;

use App\Models\SalesOrder;

class OrderService
{
    public function createOrder(array $data)
    {
        return SalesOrder::create($data);
    }

    public function updateOrder(SalesOrder $order, array $data)
    {
        $order->update($data);
        return $order;
    }
}