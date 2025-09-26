<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    /**
     * Display a listing of stocks
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with(['stocks' => function ($query) {
                $query->latest();
            }]);

            // Search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('category', 'LIKE', "%{$search}%");
                });
            }

            // Status filter
            if ($request->has('status') && $request->status) {
                $status = $request->status;
                if ($status === 'low_stock') {
                    $query->whereRaw('CAST(stock AS UNSIGNED) <= CAST(min_stock AS UNSIGNED)')
                          ->where('stock', '>', 0);
                } elseif ($status === 'out_of_stock') {
                    $query->where('stock', 0);
                } elseif ($status === 'in_stock') {
                    $query->where('stock', '>', 0)
                          ->whereColumn('stock', '>', 'min_stock');
                }
            }

            $perPage = $request->get('per_page', 15);
            $products = $query->paginate($perPage);

            // Transform data for stock view
            $stockData = $products->getCollection()->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'category' => $product->category,
                    'quantity' => $product->stock,
                    'min_stock' => $product->min_stock ?? 10,
                    'purchase_price' => $product->purchase_price,
                    'selling_price' => $product->selling_price,
                    'updated_at' => $product->updated_at,
                    'status' => $this->getStockStatus($product->stock, $product->min_stock ?? 10)
                ];
            });

            return response()->json([
                'data' => $stockData,
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'total_pages' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch stock data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Adjust stock for a product
     */
    public function adjust(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer',
            'type' => 'required|in:in,out,adjustment',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($request->product_id);
            $oldStock = $product->stock;

            // Calculate new stock based on transaction type
            switch ($request->type) {
                case 'in':
                    $newStock = $oldStock + $request->quantity;
                    break;
                case 'out':
                    if ($oldStock < $request->quantity) {
                        return response()->json([
                            'error' => 'Insufficient stock',
                            'message' => "Available stock: {$oldStock}, Requested: {$request->quantity}"
                        ], 400);
                    }
                    $newStock = $oldStock - $request->quantity;
                    break;
                case 'adjustment':
                    $newStock = $request->quantity;
                    break;
                default:
                    return response()->json(['error' => 'Invalid transaction type'], 400);
            }

            // Update product stock
            $product->update(['stock' => $newStock]);

            // Create transaction record
            $transaction = Transaction::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => $request->type,
                'quantity' => $request->quantity,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'price' => $product->selling_price,
                'reason' => $request->reason ?? 'No reason provided',
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Stock adjusted successfully',
                'transaction' => $transaction,
                'product' => $product->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to adjust stock',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock history for a product
     */
    public function history($productId, Request $request)
    {
        try {
            $product = Product::findOrFail($productId);
            
            $perPage = $request->get('per_page', 20);
            $transactions = Transaction::with('user')
                ->where('product_id', $productId)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'product' => $product,
                'transactions' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch stock history',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock alerts
     */
    public function lowStockAlerts(Request $request)
    {
        try {
            $lowStockProducts = Product::where(function($query) {
                    $query->whereRaw('CAST(stock AS UNSIGNED) <= CAST(min_stock AS UNSIGNED)')
                          ->where('stock', '>', 0); // Produk dengan stok rendah tapi tidak nol
                })
                ->orWhere('stock', 0) // Produk dengan stok kosong
                ->orderBy('stock', 'asc')
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'category' => $product->category,
                        'current_stock' => $product->stock,
                        'min_stock' => $product->min_stock ?? 10,
                        'status' => $this->getStockStatus($product->stock, $product->min_stock ?? 10),
                        'urgency' => $product->stock === 0 ? 'critical' : 'warning'
                    ];
                });

            return response()->json([
                'alerts' => $lowStockProducts,
                'total_alerts' => $lowStockProducts->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch low stock alerts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk stock adjustment
     */
    public function bulkAdjust(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'adjustments' => 'required|array',
            'adjustments.*.product_id' => 'required|exists:products,id',
            'adjustments.*.quantity' => 'required|integer|min:0',
            'adjustments.*.type' => 'required|in:in,out,adjustment',
            'adjustments.*.reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $results = [];
            
            foreach ($request->adjustments as $adjustment) {
                $product = Product::findOrFail($adjustment['product_id']);
                $oldStock = $product->stock;

                // Calculate new stock
                switch ($adjustment['type']) {
                    case 'in':
                        $newStock = $oldStock + $adjustment['quantity'];
                        break;
                    case 'out':
                        if ($oldStock < $adjustment['quantity']) {
                            throw new \Exception("Insufficient stock for product {$product->name}");
                        }
                        $newStock = $oldStock - $adjustment['quantity'];
                        break;
                    case 'adjustment':
                        $newStock = $adjustment['quantity'];
                        break;
                }

                // Update product
                $product->update(['stock' => $newStock]);

                // Create transaction
                $transaction = Transaction::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => $adjustment['type'],
                    'quantity' => $adjustment['quantity'],
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'price' => $product->selling_price,
                    'reason' => $adjustment['reason'] ?? 'Bulk adjustment',
                ]);

                $results[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'transaction_id' => $transaction->id
                ];
            }

            DB::commit();

            return response()->json([
                'message' => 'Bulk stock adjustment completed successfully',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to perform bulk adjustment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine stock status
     */
    private function getStockStatus($currentStock, $minStock)
    {
        // Convert to integers for proper comparison
        $currentStock = (int)$currentStock;
        $minStock = (int)$minStock;
        
        if ($currentStock === 0) {
            return 'out_of_stock';
        } elseif ($currentStock <= $minStock) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }
}