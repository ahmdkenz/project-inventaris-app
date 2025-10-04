<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Validator;

class EnhancedReportController extends Controller
{
    /**
     * Get dashboard status
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboardStatus()
    {
        return response()->json([
            'available' => true,
            'version' => '1.0.0',
            'features' => [
                'sales_trend',
                'top_products',
                'stock_distribution',
                'recent_activities'
            ]
        ]);
    }
    
    /**
     * Generate trend analysis report
     */
    public function trendAnalysis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'required|in:daily,weekly,monthly,quarterly',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'metrics' => 'required|array',
            'metrics.*' => 'required|in:sales,purchases,profit,stock_level,avg_order_value'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $period = $request->period;
            $dateFrom = Carbon::parse($request->date_from);
            $dateTo = Carbon::parse($request->date_to);
            $metrics = $request->metrics;

            // Generate date periods based on selected period
            $datePoints = $this->generateDatePoints($period, $dateFrom, $dateTo);
            
            $data = [
                'period' => $period,
                'date_range' => [
                    'from' => $dateFrom->format('Y-m-d'),
                    'to' => $dateTo->format('Y-m-d')
                ],
                'data_points' => []
            ];

            // Generate data for each date point
            foreach ($datePoints as $point) {
                $pointStart = $point['start'];
                $pointEnd = $point['end'];
                $pointLabel = $point['label'];
                
                $dataPoint = [
                    'label' => $pointLabel,
                    'metrics' => []
                ];
                
                // Gather requested metrics for this date point
                foreach ($metrics as $metric) {
                    switch ($metric) {
                        case 'sales':
                            $dataPoint['metrics'][$metric] = $this->getSalesData($pointStart, $pointEnd);
                            break;
                        case 'purchases':
                            $dataPoint['metrics'][$metric] = $this->getPurchaseData($pointStart, $pointEnd);
                            break;
                        case 'profit':
                            $salesData = $this->getSalesData($pointStart, $pointEnd);
                            $purchaseData = $this->getPurchaseData($pointStart, $pointEnd);
                            $dataPoint['metrics'][$metric] = [
                                'gross_profit' => $salesData['total_amount'] - $purchaseData['total_amount'],
                                'margin' => $salesData['total_amount'] > 0 ? 
                                    round((($salesData['total_amount'] - $purchaseData['total_amount']) / $salesData['total_amount']) * 100, 2) : 0
                            ];
                            break;
                        case 'stock_level':
                            $dataPoint['metrics'][$metric] = $this->getStockLevelData($pointEnd);
                            break;
                        case 'avg_order_value':
                            $dataPoint['metrics'][$metric] = $this->getAvgOrderValue($pointStart, $pointEnd);
                            break;
                    }
                }
                
                $data['data_points'][] = $dataPoint;
            }

            return response()->json(['trend_analysis' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate trend analysis report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate product performance report
     */
    public function productPerformance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'category' => 'nullable|string',
            'sort_by' => 'nullable|in:revenue,quantity,profit,margin',
            'limit' => 'nullable|integer|min:5|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $dateFrom = Carbon::parse($request->date_from);
            $dateTo = Carbon::parse($request->date_to);
            $category = $request->category;
            $sortBy = $request->sort_by ?? 'revenue';
            $limit = $request->limit ?? 20;

            // Base query for sales items
            $query = SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
                ->join('products', 'sales_order_items.product_id', '=', 'products.id')
                ->whereBetween('sales_orders.order_date', [$dateFrom, $dateTo])
                ->where('sales_orders.status', '!=', 'cancelled');

            // Apply category filter if provided
            if ($category) {
                $query->where('products.category', $category);
            }

            // Select and group data
            $productPerformance = $query->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.category',
                DB::raw('SUM(sales_order_items.quantity) as units_sold'),
                DB::raw('SUM(sales_order_items.price * sales_order_items.quantity) as revenue'),
                DB::raw('AVG(products.purchase_price) as avg_purchase_price')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.category')
            ->get();

            // Calculate additional metrics
            foreach ($productPerformance as &$product) {
                $costOfGoods = $product->units_sold * $product->avg_purchase_price;
                $product->profit = $product->revenue - $costOfGoods;
                $product->margin = $product->revenue > 0 ? ($product->profit / $product->revenue) * 100 : 0;
                
                // Get current stock
                $stockData = Stock::where('product_id', $product->id)->first();
                $product->current_stock = $stockData ? $stockData->quantity : 0;
                
                // Calculate turnover rate (units sold / avg stock during period)
                $product->turnover_rate = $product->current_stock > 0 ? 
                    round($product->units_sold / (($product->current_stock + $product->units_sold) / 2), 2) : 0;
            }

            // Sort the collection based on requested sort field
            switch ($sortBy) {
                case 'revenue':
                    $productPerformance = $productPerformance->sortByDesc('revenue')->values();
                    break;
                case 'quantity':
                    $productPerformance = $productPerformance->sortByDesc('units_sold')->values();
                    break;
                case 'profit':
                    $productPerformance = $productPerformance->sortByDesc('profit')->values();
                    break;
                case 'margin':
                    $productPerformance = $productPerformance->sortByDesc('margin')->values();
                    break;
            }

            // Limit the results
            $productPerformance = $productPerformance->take($limit);

            // Calculate summary data
            $totalRevenue = $productPerformance->sum('revenue');
            $totalProfit = $productPerformance->sum('profit');
            $avgMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

            return response()->json([
                'product_performance' => [
                    'date_range' => [
                        'from' => $dateFrom->format('Y-m-d'),
                        'to' => $dateTo->format('Y-m-d')
                    ],
                    'summary' => [
                        'total_revenue' => round($totalRevenue, 2),
                        'total_profit' => round($totalProfit, 2),
                        'average_margin' => round($avgMargin, 2),
                        'products_analyzed' => $productPerformance->count()
                    ],
                    'products' => $productPerformance
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate product performance report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate inventory efficiency report
     */
    public function inventoryEfficiency(Request $request)
    {
        try {
            // Get current stock levels
            $stocks = Stock::with('product')->get();
            
            $totalStockValue = 0;
            $lowStockCount = 0;
            $excessStockCount = 0;
            $outOfStockCount = 0;
            $optimalStockCount = 0;
            
            $stockItems = [];
            
            foreach ($stocks as $stock) {
                if (!$stock->product) {
                    continue;
                }
                
                $itemValue = $stock->quantity * $stock->product->purchase_price;
                $totalStockValue += $itemValue;
                
                // Calculate days of inventory based on sales velocity
                $salesVelocity = $this->calculateSalesVelocity($stock->product_id);
                $daysOfInventory = $salesVelocity > 0 ? round($stock->quantity / $salesVelocity) : 0;
                
                // Determine stock status
                $status = 'optimal';
                if ($stock->quantity <= 0) {
                    $status = 'out_of_stock';
                    $outOfStockCount++;
                } elseif ($stock->quantity <= $stock->minimum_stock) {
                    $status = 'low_stock';
                    $lowStockCount++;
                } elseif ($daysOfInventory > 90) { // If more than 90 days of inventory, consider excess
                    $status = 'excess';
                    $excessStockCount++;
                } else {
                    $optimalStockCount++;
                }
                
                $stockItems[] = [
                    'product_id' => $stock->product_id,
                    'product_name' => $stock->product->name,
                    'sku' => $stock->product->sku,
                    'category' => $stock->product->category,
                    'quantity' => $stock->quantity,
                    'min_stock' => $stock->minimum_stock,
                    'value' => round($itemValue, 2),
                    'days_of_inventory' => $daysOfInventory,
                    'sales_velocity' => round($salesVelocity, 2),
                    'status' => $status,
                    'reorder_suggestion' => $this->calculateReorderAmount($stock, $salesVelocity)
                ];
            }
            
            // Sort by efficiency (we'll use days_of_inventory as a proxy)
            usort($stockItems, function ($a, $b) {
                $aDaysOfInventory = $a['days_of_inventory'];
                $bDaysOfInventory = $b['days_of_inventory'];
                
                // Out of stock items first
                if ($a['status'] === 'out_of_stock' && $b['status'] !== 'out_of_stock') {
                    return -1;
                }
                if ($a['status'] !== 'out_of_stock' && $b['status'] === 'out_of_stock') {
                    return 1;
                }
                
                // Then low stock items
                if ($a['status'] === 'low_stock' && $b['status'] !== 'low_stock') {
                    return -1;
                }
                if ($a['status'] !== 'low_stock' && $b['status'] === 'low_stock') {
                    return 1;
                }
                
                // Then by days of inventory (ascending)
                return $aDaysOfInventory <=> $bDaysOfInventory;
            });
            
            // Calculate total inventory value by category
            $categoryValues = [];
            foreach ($stockItems as $item) {
                $category = $item['category'] ?? 'Uncategorized';
                if (!isset($categoryValues[$category])) {
                    $categoryValues[$category] = 0;
                }
                $categoryValues[$category] += $item['value'];
            }
            
            return response()->json([
                'inventory_efficiency' => [
                    'summary' => [
                        'total_stock_value' => round($totalStockValue, 2),
                        'total_products' => count($stockItems),
                        'out_of_stock_products' => $outOfStockCount,
                        'low_stock_products' => $lowStockCount,
                        'excess_stock_products' => $excessStockCount,
                        'optimal_stock_products' => $optimalStockCount,
                    ],
                    'category_breakdown' => $categoryValues,
                    'stock_items' => $stockItems
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate inventory efficiency report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate dashboard overview data
     */
    public function dashboardOverview()
    {
        try {
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();
            $thisWeekStart = Carbon::now()->startOfWeek();
            $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
            $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();
            $thisMonthStart = Carbon::now()->startOfMonth();
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

            $data = [
                'sales' => [
                    'today' => $this->getSalesData($today, $today),
                    'yesterday' => $this->getSalesData($yesterday, $yesterday),
                    'this_week' => $this->getSalesData($thisWeekStart, $today),
                    'last_week' => $this->getSalesData($lastWeekStart, $lastWeekEnd),
                    'this_month' => $this->getSalesData($thisMonthStart, $today),
                    'last_month' => $this->getSalesData($lastMonthStart, $lastMonthEnd)
                ],
                'purchases' => [
                    'today' => $this->getPurchaseData($today, $today),
                    'this_week' => $this->getPurchaseData($thisWeekStart, $today),
                    'this_month' => $this->getPurchaseData($thisMonthStart, $today)
                ],
                'stock' => $this->getStockLevelData(),
                'top_selling_products' => $this->getTopSellingProducts(),
                'latest_activity' => $this->getLatestActivity()
            ];

            // Calculate growth percentages
            $data['growth'] = [
                'daily_sales' => $this->calculateGrowthPercentage(
                    $data['sales']['today']['total_amount'],
                    $data['sales']['yesterday']['total_amount']
                ),
                'weekly_sales' => $this->calculateGrowthPercentage(
                    $data['sales']['this_week']['total_amount'],
                    $data['sales']['last_week']['total_amount']
                ),
                'monthly_sales' => $this->calculateGrowthPercentage(
                    $data['sales']['this_month']['total_amount'],
                    $data['sales']['last_month']['total_amount']
                )
            ];

            return response()->json(['dashboard' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate dashboard overview',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to generate date points based on the selected period
     */
    private function generateDatePoints($period, $dateFrom, $dateTo)
    {
        $points = [];
        
        switch ($period) {
            case 'daily':
                $days = CarbonPeriod::create($dateFrom, '1 day', $dateTo);
                foreach ($days as $day) {
                    $points[] = [
                        'start' => $day->copy()->startOfDay(),
                        'end' => $day->copy()->endOfDay(),
                        'label' => $day->format('Y-m-d')
                    ];
                }
                break;
                
            case 'weekly':
                $currentStart = $dateFrom->copy()->startOfWeek();
                
                while ($currentStart->lte($dateTo)) {
                    $currentEnd = min($currentStart->copy()->endOfWeek(), $dateTo);
                    
                    $points[] = [
                        'start' => $currentStart->copy(),
                        'end' => $currentEnd->copy(),
                        'label' => 'Week ' . $currentStart->weekOfYear . ' ' . $currentStart->year
                    ];
                    
                    $currentStart->addWeek();
                }
                break;
                
            case 'monthly':
                $currentStart = $dateFrom->copy()->startOfMonth();
                
                while ($currentStart->lte($dateTo)) {
                    $currentEnd = min($currentStart->copy()->endOfMonth(), $dateTo);
                    
                    $points[] = [
                        'start' => $currentStart->copy(),
                        'end' => $currentEnd->copy(),
                        'label' => $currentStart->format('M Y')
                    ];
                    
                    $currentStart->addMonth();
                }
                break;
                
            case 'quarterly':
                $currentStart = $dateFrom->copy()->startOfQuarter();
                
                while ($currentStart->lte($dateTo)) {
                    $currentEnd = min($currentStart->copy()->endOfQuarter(), $dateTo);
                    
                    $quarter = ceil($currentStart->month / 3);
                    $points[] = [
                        'start' => $currentStart->copy(),
                        'end' => $currentEnd->copy(),
                        'label' => 'Q' . $quarter . ' ' . $currentStart->year
                    ];
                    
                    $currentStart->addQuarter();
                }
                break;
        }
        
        return $points;
    }

    /**
     * Helper function to get sales data for a specific period
     */
    private function getSalesData($startDate, $endDate)
    {
        $salesData = SalesOrder::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('COUNT(*) as count, SUM(total_amount) as total_amount, AVG(total_amount) as average_amount')
            ->first();
            
        $itemCount = SalesOrderItem::whereHas('salesOrder', function($query) use ($startDate, $endDate) {
                $query->whereBetween('order_date', [$startDate, $endDate])
                      ->where('status', '!=', 'cancelled');
            })
            ->sum('quantity');
            
        return [
            'count' => (int)$salesData->count,
            'total_amount' => round($salesData->total_amount ?? 0, 2),
            'average_amount' => round($salesData->average_amount ?? 0, 2),
            'item_count' => (int)$itemCount
        ];
    }

    /**
     * Helper function to get purchase data for a specific period
     */
    private function getPurchaseData($startDate, $endDate)
    {
        $purchaseData = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('COUNT(*) as count, SUM(total_amount) as total_amount, AVG(total_amount) as average_amount')
            ->first();
            
        $itemCount = PurchaseOrderItem::whereHas('purchaseOrder', function($query) use ($startDate, $endDate) {
                $query->whereBetween('order_date', [$startDate, $endDate])
                      ->where('status', '!=', 'cancelled');
            })
            ->sum('quantity');
            
        return [
            'count' => (int)$purchaseData->count,
            'total_amount' => round($purchaseData->total_amount ?? 0, 2),
            'average_amount' => round($purchaseData->average_amount ?? 0, 2),
            'item_count' => (int)$itemCount
        ];
    }

    /**
     * Helper function to get stock level data
     */
    private function getStockLevelData($date = null)
    {
        $query = Stock::select(
                DB::raw('COUNT(*) as total_products'),
                DB::raw('SUM(quantity) as total_items'),
                DB::raw('SUM(CASE WHEN quantity <= minimum_stock THEN 1 ELSE 0 END) as low_stock_products'),
                DB::raw('SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock_products')
            )
            ->with('product');
            
        $stockData = $query->first();
        
        // Calculate total stock value
        $totalValue = Stock::join('products', 'stocks.product_id', '=', 'products.id')
            ->selectRaw('SUM(stocks.quantity * products.purchase_price) as value')
            ->value('value');
        
        return [
            'total_products' => (int)$stockData->total_products,
            'total_items' => (int)$stockData->total_items,
            'low_stock_products' => (int)$stockData->low_stock_products,
            'out_of_stock_products' => (int)$stockData->out_of_stock_products,
            'total_value' => round($totalValue ?? 0, 2)
        ];
    }

    /**
     * Helper function to get top selling products
     */
    private function getTopSellingProducts($limit = 5)
    {
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();
        
        return SalesOrderItem::join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->whereBetween('sales_orders.order_date', [$startDate, $endDate])
            ->where('sales_orders.status', '!=', 'cancelled')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(sales_order_items.quantity) as quantity_sold'),
                DB::raw('SUM(sales_order_items.price * sales_order_items.quantity) as revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('quantity_sold')
            ->limit($limit)
            ->get();
    }

    /**
     * Helper function to get the latest activity
     */
    private function getLatestActivity($limit = 10)
    {
        $salesOrders = SalesOrder::select('id', 'created_at', DB::raw("'sales_order' as type"), 'so_number as reference', 'total_amount', 'customer_name as entity_name')
            ->orderBy('created_at', 'desc')
            ->limit($limit);
            
        $purchaseOrders = PurchaseOrder::select('id', 'created_at', DB::raw("'purchase_order' as type"), 'po_number as reference', 'total_amount', 'supplier_name as entity_name')
            ->orderBy('created_at', 'desc')
            ->limit($limit);
            
        return $salesOrders->union($purchaseOrders)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Helper function to calculate average order value
     */
    private function getAvgOrderValue($startDate, $endDate)
    {
        $salesAvg = SalesOrder::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->avg('total_amount') ?? 0;
            
        return [
            'average' => round($salesAvg, 2)
        ];
    }

    /**
     * Helper function to calculate growth percentage
     */
    private function calculateGrowthPercentage($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Helper function to calculate sales velocity (units sold per day)
     */
    private function calculateSalesVelocity($productId)
    {
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();
        $daysInPeriod = $startDate->diffInDays($endDate) + 1;
        
        $totalSold = SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->where('sales_order_items.product_id', $productId)
            ->whereBetween('sales_orders.order_date', [$startDate, $endDate])
            ->where('sales_orders.status', '!=', 'cancelled')
            ->sum('sales_order_items.quantity');
            
        return $daysInPeriod > 0 ? $totalSold / $daysInPeriod : 0;
    }

    /**
     * Helper function to calculate recommended reorder amount
     */
    private function calculateReorderAmount($stock, $salesVelocity)
    {
        // If we have no sales velocity, return minimum stock level
        if ($salesVelocity <= 0) {
            return max(0, $stock->minimum_stock - $stock->quantity);
        }
        
        // Assume we want to cover 30 days of inventory
        $targetDaysOfInventory = 30;
        $targetQuantity = ceil($salesVelocity * $targetDaysOfInventory);
        
        // Ensure we're at least at minimum stock level
        $targetQuantity = max($targetQuantity, $stock->minimum_stock);
        
        // Calculate how much we need to order
        $reorderAmount = max(0, $targetQuantity - $stock->quantity);
        
        return $reorderAmount;
    }
}