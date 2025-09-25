<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Get recent transactions
     */
    public function recent(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            
            $transactions = Transaction::with(['product', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'transactions' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch recent transactions',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get transactions for a specific product
     */
    public function byProduct($productId, Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            
            $transactions = Transaction::with('user')
                ->where('product_id', $productId)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'product_id' => $productId,
                'transactions' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch transactions',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}