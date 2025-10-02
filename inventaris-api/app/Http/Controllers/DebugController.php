<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    public function checkProduct(Request $request, $productId)
    {
        // Log the product ID and its type
        Log::info('Checking product existence', [
            'product_id' => $productId,
            'product_id_type' => gettype($productId)
        ]);

        // Try finding the product with the exact ID
        $product = Product::find($productId);
        
        // If not found, try with where
        if (!$product) {
            $product = Product::where('id', $productId)->first();
        }
        
        // Get all products
        $allProductIds = Product::all()->pluck('id')->toArray();

        return response()->json([
            'exists' => $product ? true : false,
            'product' => $product,
            'total_products' => count($allProductIds),
            'all_product_ids' => $allProductIds
        ]);
    }
}