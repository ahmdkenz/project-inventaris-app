<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Generate sales report
     */
    public function salesReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'format' => 'nullable|in:json,csv,xlsx'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth();
            $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now();

            // Get sales data
            $salesQuery = Transaction::where('type', 'sale')
                ->whereBetween('created_at', [$dateFrom, $dateTo]);

            $salesData = [
                'period' => [
                    'from' => $dateFrom->format('Y-m-d'),
                    'to' => $dateTo->format('Y-m-d')
                ],
                'summary' => [
                    'total_revenue' => $salesQuery->sum('total_amount'),
                    'total_transactions' => $salesQuery->count(),
                    'average_transaction' => $salesQuery->avg('total_amount') ?? 0,
                    'total_items_sold' => $salesQuery->sum('quantity')
                ],
                'daily_sales' => $this->getDailySales($dateFrom, $dateTo),
                'top_products' => $this->getTopSellingProducts($dateFrom, $dateTo),
                'sales_by_category' => $this->getSalesByCategory($dateFrom, $dateTo)
            ];

            // Previous period comparison
            $prevPeriodFrom = $dateFrom->copy()->subDays($dateFrom->diffInDays($dateTo) + 1);
            $prevPeriodTo = $dateFrom->copy()->subDay();
            
            $prevSalesQuery = Transaction::where('type', 'sale')
                ->whereBetween('created_at', [$prevPeriodFrom, $prevPeriodTo]);

            $prevRevenue = $prevSalesQuery->sum('total_amount');
            $salesData['growth'] = [
                'revenue_growth' => $prevRevenue > 0 ? 
                    round((($salesData['summary']['total_revenue'] - $prevRevenue) / $prevRevenue) * 100, 2) : 0,
                'transaction_growth' => 0 // Calculate based on your needs
            ];

            return response()->json(['report' => $salesData]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate sales report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate stock report
     */
    public function stockReport(Request $request)
    {
        try {
            $stockData = [
                'summary' => [
                    'total_products' => Product::count(),
                    'active_products' => Product::where('status', 'active')->count(),
                    'low_stock_items' => Product::whereRaw('stock <= min_stock')->count(),
                    'out_of_stock_items' => Product::where('stock', 0)->count(),
                    'total_stock_value' => Product::selectRaw('SUM(stock * price)')->value('SUM(stock * price)') ?? 0
                ],
                'categories' => $this->getStockByCategory(),
                'low_stock_products' => Product::whereRaw('stock <= min_stock')
                    ->select('id', 'name', 'sku', 'stock', 'min_stock', 'category', 'price')
                    ->orderBy('stock', 'asc')
                    ->limit(20)
                    ->get(),
                'stock_movements' => $this->getRecentStockMovements(),
                'inventory_turnover' => $this->calculateInventoryTurnover()
            ];

            return response()->json(['report' => $stockData]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate stock report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate financial report (Admin only)
     */
    public function financialReport(Request $request)
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Admin access required'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->startOfQuarter();
            $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now();

            $salesRevenue = Transaction::where('type', 'sale')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('total_amount');

            $purchaseCosts = Transaction::where('type', 'purchase')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('total_amount');

            // Mock operational expenses - in real app, you'd have an expenses table
            $operationalExpenses = $purchaseCosts * 0.3; // 30% of purchase costs as operational expenses

            $financialData = [
                'period' => [
                    'from' => $dateFrom->format('Y-m-d'),
                    'to' => $dateTo->format('Y-m-d')
                ],
                'revenue' => [
                    'gross_revenue' => $salesRevenue,
                    'net_revenue' => $salesRevenue * 0.95, // After discounts/returns
                ],
                'costs' => [
                    'cost_of_goods_sold' => $purchaseCosts,
                    'operational_expenses' => $operationalExpenses,
                    'total_expenses' => $purchaseCosts + $operationalExpenses
                ],
                'profit' => [
                    'gross_profit' => $salesRevenue - $purchaseCosts,
                    'net_profit' => $salesRevenue - ($purchaseCosts + $operationalExpenses),
                    'profit_margin' => $salesRevenue > 0 ? round((($salesRevenue - $purchaseCosts) / $salesRevenue) * 100, 2) : 0
                ],
                'kpis' => [
                    'revenue_per_transaction' => $this->getAverageTransactionValue($dateFrom, $dateTo),
                    'customer_acquisition_cost' => 0, // Calculate based on your marketing spend
                    'customer_lifetime_value' => 0, // Calculate based on customer data
                ]
            ];

            return response()->json(['report' => $financialData]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate financial report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export reports in various formats
     */
    public function exportReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:sales,stock,financial',
            'format' => 'nullable|in:csv,xlsx,pdf',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $format = $request->format ?? 'xlsx';
            $type = $request->type;
            
            // Generate report data based on type
            switch ($type) {
                case 'sales':
                    $reportResponse = $this->salesReport($request);
                    break;
                case 'stock':
                    $reportResponse = $this->stockReport($request);
                    break;
                case 'financial':
                    $reportResponse = $this->financialReport($request);
                    break;
            }

            if ($reportResponse->getStatusCode() !== 200) {
                return $reportResponse;
            }

            $reportData = $reportResponse->getData(true)['report'];

            // For this example, we'll return a simple CSV
            // In a real application, you'd use libraries like PhpSpreadsheet or Laravel Excel
            $csv = $this->generateCSV($reportData, $type);

            $filename = "{$type}_report_" . date('Y-m-d') . ".csv";

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to export report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate custom report
     */
    public function generateCustomReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:sales,inventory,customer,financial',
            'range' => 'required|in:today,week,month,quarter,year,custom',
            'start_date' => 'nullable|required_if:range,custom|date',
            'end_date' => 'nullable|required_if:range,custom|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Calculate date range
            $dateRange = $this->calculateDateRange($request->range, $request->start_date, $request->end_date);
            
            // Generate report based on type and range
            $reportData = $this->generateReportByType($request->type, $dateRange['from'], $dateRange['to']);
            
            // In a real application, you might save the report and return a URL
            $reportId = uniqid('report_');
            
            return response()->json([
                'message' => 'Custom report generated successfully',
                'report_id' => $reportId,
                'report_url' => "/api/reports/download/{$reportId}",
                'report_data' => $reportData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate custom report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Helper methods
    private function getDailySales($dateFrom, $dateTo)
    {
        return Transaction::where('type', 'sale')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as transactions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getTopSellingProducts($dateFrom, $dateTo, $limit = 10)
    {
        return Transaction::where('type', 'sale')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->join('products', 'transactions.product_id', '=', 'products.id')
            ->selectRaw('products.name, products.sku, SUM(transactions.quantity) as total_sold, SUM(transactions.total_amount) as revenue')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    private function getSalesByCategory($dateFrom, $dateTo)
    {
        return Transaction::where('type', 'sale')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->join('products', 'transactions.product_id', '=', 'products.id')
            ->selectRaw('products.category, SUM(transactions.total_amount) as revenue, COUNT(*) as transactions')
            ->groupBy('products.category')
            ->orderByDesc('revenue')
            ->get();
    }

    private function getStockByCategory()
    {
        return Product::selectRaw('category, COUNT(*) as product_count, SUM(stock) as total_stock, SUM(stock * price) as stock_value')
            ->groupBy('category')
            ->orderBy('category')
            ->get();
    }

    private function getRecentStockMovements($limit = 20)
    {
        return Transaction::whereIn('type', ['purchase', 'adjustment', 'sale'])
            ->with(['product:id,name,sku', 'user:id,name'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function calculateInventoryTurnover()
    {
        // Simple inventory turnover calculation
        // In a real app, you'd want more sophisticated calculations
        $totalProducts = Product::count();
        $totalSales = Transaction::where('type', 'sale')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->sum('quantity');

        return $totalProducts > 0 ? round(($totalSales / $totalProducts) * 100, 2) : 0;
    }

    private function getAverageTransactionValue($dateFrom, $dateTo)
    {
        return Transaction::where('type', 'sale')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->avg('total_amount') ?? 0;
    }

    private function calculateDateRange($range, $startDate, $endDate)
    {
        $now = Carbon::now();
        
        switch ($range) {
            case 'today':
                return ['from' => $now->startOfDay(), 'to' => $now->endOfDay()];
            case 'week':
                return ['from' => $now->startOfWeek(), 'to' => $now->endOfWeek()];
            case 'month':
                return ['from' => $now->startOfMonth(), 'to' => $now->endOfMonth()];
            case 'quarter':
                return ['from' => $now->startOfQuarter(), 'to' => $now->endOfQuarter()];
            case 'year':
                return ['from' => $now->startOfYear(), 'to' => $now->endOfYear()];
            case 'custom':
                return ['from' => Carbon::parse($startDate), 'to' => Carbon::parse($endDate)];
            default:
                return ['from' => $now->startOfMonth(), 'to' => $now->endOfMonth()];
        }
    }

    private function generateReportByType($type, $dateFrom, $dateTo)
    {
        switch ($type) {
            case 'sales':
                return $this->salesReport(new Request(['date_from' => $dateFrom, 'date_to' => $dateTo]))->getData(true);
            case 'inventory':
                return $this->stockReport(new Request())->getData(true);
            case 'financial':
                return $this->financialReport(new Request(['date_from' => $dateFrom, 'date_to' => $dateTo]))->getData(true);
            default:
                return ['message' => 'Report type not implemented yet'];
        }
    }

    private function generateCSV($data, $type)
    {
        $csv = '';
        
        // Add headers based on report type
        switch ($type) {
            case 'sales':
                $csv .= "Sales Report\n";
                $csv .= "Period,Total Revenue,Total Transactions,Average Transaction\n";
                $summary = $data['summary'];
                $csv .= "{$data['period']['from']} to {$data['period']['to']},{$summary['total_revenue']},{$summary['total_transactions']},{$summary['average_transaction']}\n";
                break;
            case 'stock':
                $csv .= "Stock Report\n";
                $csv .= "Metric,Value\n";
                $summary = $data['summary'];
                foreach ($summary as $key => $value) {
                    $csv .= ucwords(str_replace('_', ' ', $key)) . "," . $value . "\n";
                }
                break;
            case 'financial':
                $csv .= "Financial Report\n";
                $csv .= "Metric,Value\n";
                $csv .= "Gross Revenue,{$data['revenue']['gross_revenue']}\n";
                $csv .= "Total Expenses,{$data['costs']['total_expenses']}\n";
                $csv .= "Net Profit,{$data['profit']['net_profit']}\n";
                break;
        }
        
        return $csv;
    }
}