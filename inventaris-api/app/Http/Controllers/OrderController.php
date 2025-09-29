<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Get orders statistics for dashboard
     * 
     * @return \Illuminate\Http\Response
     */
    public function stats()
    {
        $stats = $this->orderService->getOrdersStats();
        return response()->json(['data' => $stats]);
    }

    /**
     * Get recent orders
     * 
     * @return \Illuminate\Http\Response
     */
    public function recent()
    {
        $limit = request('limit', 5);
        $recentOrders = $this->orderService->getRecentOrders($limit);
        return response()->json(['data' => $recentOrders]);
    }
}