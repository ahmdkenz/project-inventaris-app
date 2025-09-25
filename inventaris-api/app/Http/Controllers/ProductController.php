<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Product::query();

            // Search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('category', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                });
            }

            // Category filter
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }

            // Status filter
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $products = $query->paginate($perPage);

            return response()->json([
                'data' => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'total_pages' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch products',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Generate SKU if not provided
            if (!$request->sku) {
                $request->merge(['sku' => $this->generateSku($request->name, $request->category)]);
            }

            $product = Product::create([
                'name' => $request->name,
                'sku' => $request->sku,
                'description' => $request->description,
                'category' => $request->category,
                'price' => $request->price,
                'stock' => $request->stock,
                'min_stock' => $request->min_stock ?? 10,
                'status' => $request->status ?? 'active',
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create product',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Product $product)
    {
        try {
            return response()->json([
                'product' => $product->load(['transactions' => function ($query) {
                    $query->with('user')->latest()->take(10);
                }])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch product',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'category' => 'sometimes|string|max:100',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'min_stock' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product->update($request->only([
                'name', 'sku', 'description', 'category', 
                'price', 'stock', 'min_stock', 'status'
            ]));

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update product',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            // Check if product has transactions
            if ($product->transactions()->exists()) {
                return response()->json([
                    'error' => 'Cannot delete product',
                    'message' => 'This product has transaction history and cannot be deleted.'
                ], 400);
            }

            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete product',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product categories
     */
    public function categories()
    {
        try {
            $categories = Product::distinct()
                ->pluck('category')
                ->filter()
                ->sort()
                ->values();

            return response()->json($categories);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch categories',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate SKU for product
     */
    private function generateSku($name, $category)
    {
        $prefix = strtoupper(substr($category, 0, 3));
        $namePrefix = strtoupper(substr(str_replace(' ', '', $name), 0, 3));
        $timestamp = date('ymd');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . $namePrefix . $timestamp . $random;
    }

    /**
     * Bulk update products
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'action' => 'required|in:activate,deactivate,delete,update_category',
            'category' => 'required_if:action,update_category|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $productIds = collect($request->products)->pluck('id');
            $updated = 0;

            switch ($request->action) {
                case 'activate':
                    $updated = Product::whereIn('id', $productIds)->update(['status' => 'active']);
                    break;
                case 'deactivate':
                    $updated = Product::whereIn('id', $productIds)->update(['status' => 'inactive']);
                    break;
                case 'update_category':
                    $updated = Product::whereIn('id', $productIds)->update(['category' => $request->category]);
                    break;
                case 'delete':
                    $updated = Product::whereIn('id', $productIds)
                        ->whereDoesntHave('transactions')
                        ->delete();
                    break;
            }

            return response()->json([
                'message' => "Bulk {$request->action} completed successfully",
                'updated_count' => $updated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to perform bulk operation',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
