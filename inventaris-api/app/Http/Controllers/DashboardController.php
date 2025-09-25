<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        try {
            $stats = [
                'totalProducts' => Product::count(),
                'lowStock' => Product::whereColumn('stock', '<=', 'min_stock')->count(),
                'recentTransactions' => Transaction::where('created_at', '>=', now()->subDays(7))->count(),
                'totalUsers' => User::count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch dashboard statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activities
     */
    public function recentActivities(Request $request)
    {
        try {
            $activities = collect();

            // Recent product additions
            $recentProducts = Product::with('user')
                ->where('created_at', '>=', now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => 'product_' . $product->id,
                        'description' => "New product added: {$product->name}",
                        'user_name' => $product->user->name ?? 'System',
                        'created_at' => $product->created_at->toISOString(),
                        'type' => 'product_added'
                    ];
                });

            // Recent stock transactions
            $recentTransactions = Transaction::with(['product', 'user'])
                ->where('created_at', '>=', now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => 'transaction_' . $transaction->id,
                        'description' => "Stock {$transaction->type}: {$transaction->product->name} (Qty: {$transaction->quantity})",
                        'user_name' => $transaction->user->name ?? 'System',
                        'created_at' => $transaction->created_at->toISOString(),
                        'type' => 'stock_transaction'
                    ];
                });

            // Recent user registrations
            $recentUsers = User::where('created_at', '>=', now()->subDays(7))
                ->where('id', '!=', auth()->id()) // Exclude current user
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => 'user_' . $user->id,
                        'description' => "New user registered: {$user->name} ({$user->role})",
                        'user_name' => 'System',
                        'created_at' => $user->created_at->toISOString(),
                        'type' => 'user_registered'
                    ];
                });

            // Merge and sort activities
            $activities = $recentProducts
                ->merge($recentTransactions)
                ->merge($recentUsers)
                ->sortByDesc('created_at')
                ->take(10)
                ->values();

            return response()->json($activities);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch recent activities',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get admin dashboard overview
     */
    public function overview(Request $request)
    {
        try {
            $data = [
                'stats' => [
                    'totalProducts' => Product::count(),
                    'lowStock' => Product::whereColumn('stock', '<=', 'min_stock')->count(),
                    'totalUsers' => User::count(),
                    'recentTransactions' => Transaction::where('created_at', '>=', now()->subDays(7))->count(),
                    'totalRevenue' => Transaction::join('products', 'transactions.product_id', '=', 'products.id')
                        ->where('transactions.type', 'out')
                        ->where('transactions.created_at', '>=', now()->subMonth())
                        ->sum(DB::raw('transactions.quantity * products.selling_price')),
                ],
                'lowStockProducts' => Product::whereColumn('stock', '<=', 'min_stock')
                    ->select(['id', 'name', 'stock', 'min_stock'])
                    ->limit(5)
                    ->get(),
                'recentTransactions' => Transaction::with(['product', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch admin overview',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}