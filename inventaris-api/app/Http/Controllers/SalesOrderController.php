<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Transaction;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SalesOrderController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = SalesOrder::with('items.product')->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $orders]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'so_number' => 'required|string|max:50|unique:sales_orders',
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string|max:20',
            'order_date' => 'required|date',
            'expected_delivery' => 'nullable|date',
            'shipping_address' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check stock availability
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || $product->stock < $item['quantity']) {
                return response()->json([
                    'message' => 'Insufficient stock for ' . ($product ? $product->name : 'product'),
                    'product_id' => $item['product_id'],
                    'available' => $product ? $product->stock : 0,
                    'requested' => $item['quantity']
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            // Calculate total amount
            $total_amount = 0;
            foreach ($request->items as $item) {
                $total_amount += $item['quantity'] * $item['unit_price'];
            }

            // Create sales order
            $salesOrder = SalesOrder::create([
                'so_number' => $request->so_number,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'order_date' => $request->order_date,
                'expected_delivery' => $request->expected_delivery,
                'shipping_address' => $request->shipping_address,
                'notes' => $request->notes,
                'total_amount' => $total_amount,
                'status' => 'pending'
            ]);

            // Add items and reduce stock immediately
            foreach ($request->items as $item) {
                $salesOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price']
                ]);
                
                // Update product stock immediately
                $product = Product::find($item['product_id']);
                $oldStock = $product->stock;
                $newStock = $oldStock - $item['quantity'];
                
                // Update product stock
                $product->update(['stock' => $newStock]);
                
                // Log stock update
                Log::info('Stock reduced for product ' . $product->name . ' (ID: ' . $item['product_id'] . ') from ' . $oldStock . ' to ' . $newStock . ' for SO: ' . $salesOrder->so_number);
            }

            DB::commit();

            return response()->json([
                'message' => 'Sales order created successfully',
                'data' => $salesOrder->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating sales order: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create sales order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $salesOrder = SalesOrder::with('items.product')->find($id);
        
        if (!$salesOrder) {
            return response()->json(['message' => 'Sales order not found'], 404);
        }

        return response()->json(['data' => $salesOrder]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $salesOrder = SalesOrder::find($id);
        
        if (!$salesOrder) {
            return response()->json(['message' => 'Sales order not found'], 404);
        }

        // Only allow editing for pending sales orders
        if ($salesOrder->status !== 'pending') {
            return response()->json(['message' => 'Cannot edit a sales order that is not pending'], 422);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'sometimes|required|string|max:100',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string|max:20',
            'order_date' => 'sometimes|required|date',
            'expected_delivery' => 'nullable|date',
            'shipping_address' => 'sometimes|required|string',
            'notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check stock availability if items are being updated
        if ($request->has('items')) {
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product || $product->stock < $item['quantity']) {
                    return response()->json([
                        'message' => 'Insufficient stock for ' . ($product ? $product->name : 'product'),
                        'product_id' => $item['product_id'],
                        'available' => $product ? $product->stock : 0,
                        'requested' => $item['quantity']
                    ], 422);
                }
            }
        }

        try {
            DB::beginTransaction();

            // Update sales order
            $salesOrder->update([
                'customer_name' => $request->customer_name ?? $salesOrder->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'order_date' => $request->order_date ?? $salesOrder->order_date,
                'expected_delivery' => $request->expected_delivery,
                'shipping_address' => $request->shipping_address ?? $salesOrder->shipping_address,
                'notes' => $request->notes
            ]);

            // Update items if provided
            if ($request->has('items')) {
                // Get current items to restore stock
                $currentItems = $salesOrder->items()->get();
                
                // Return stock for items that will be removed or updated
                foreach ($currentItems as $currentItem) {
                    $product = Product::find($currentItem->product_id);
                    $oldStock = $product->stock;
                    $newStock = $oldStock + $currentItem->quantity;
                    
                    // Update product stock (return the items)
                    $product->update(['stock' => $newStock]);
                    
                    // Log stock return
                    Log::info('Stock returned for product ' . $product->name . ' (ID: ' . $currentItem->product_id . ') from ' . $oldStock . ' to ' . $newStock . ' for SO update: ' . $salesOrder->so_number);
                }
                
                // Remove existing items
                $salesOrder->items()->delete();
                
                // Add new items and adjust stock
                $total_amount = 0;
                foreach ($request->items as $item) {
                    $salesOrder->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price']
                    ]);
                    $total_amount += $item['quantity'] * $item['unit_price'];
                    
                    // Reduce stock for new items
                    $product = Product::find($item['product_id']);
                    $oldStock = $product->stock;
                    $newStock = $oldStock - $item['quantity'];
                    
                    // Update product stock
                    $product->update(['stock' => $newStock]);
                    
                    // Log stock reduction
                    Log::info('Stock reduced for product ' . $product->name . ' (ID: ' . $item['product_id'] . ') from ' . $oldStock . ' to ' . $newStock . ' for SO update: ' . $salesOrder->so_number);
                }

                // Update total amount
                $salesOrder->update(['total_amount' => $total_amount]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Sales order updated successfully',
                'data' => $salesOrder->load('items.product')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating sales order: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update sales order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $salesOrder = SalesOrder::find($id);
        
        if (!$salesOrder) {
            return response()->json(['message' => 'Sales order not found'], 404);
        }

        // Only allow deletion for pending sales orders
        if ($salesOrder->status !== 'pending') {
            return response()->json(['message' => 'Cannot delete a sales order that is not pending'], 422);
        }

        try {
            DB::beginTransaction();
            
            // Get items before deletion to restore stock
            $items = $salesOrder->items()->get();
            
            // Return stock for all items
            foreach ($items as $item) {
                $product = Product::find($item->product_id);
                $oldStock = $product->stock;
                $newStock = $oldStock + $item->quantity;
                
                // Update product stock (return the items)
                $product->update(['stock' => $newStock]);
                
                // Log stock return
                Log::info('Stock returned for product ' . $product->name . ' (ID: ' . $item->product_id . ') from ' . $oldStock . ' to ' . $newStock . ' for deleted SO: ' . $salesOrder->so_number);
            }

            // Delete items first
            $salesOrder->items()->delete();
            
            // Delete the sales order
            $salesOrder->delete();

            DB::commit();

            return response()->json(['message' => 'Sales order deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting sales order: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete sales order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update sales order status to confirmed.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function confirm($id)
    {
        $salesOrder = SalesOrder::with('items.product')->find($id);
        
        if (!$salesOrder) {
            return response()->json(['message' => 'Sales order not found'], 404);
        }

        if ($salesOrder->status !== 'pending') {
            return response()->json(['message' => 'Only pending sales orders can be confirmed'], 422);
        }

        // Check stock availability again before confirming
        foreach ($salesOrder->items as $item) {
            $product = Product::find($item->product_id);
            if (!$product || $product->stock < $item->quantity) {
                return response()->json([
                    'message' => 'Insufficient stock for ' . ($product ? $product->name : 'product'),
                    'product_id' => $item->product_id,
                    'available' => $product ? $product->stock : 0,
                    'requested' => $item->quantity
                ], 422);
            }
        }

        $salesOrder->update(['status' => 'confirmed']);

        return response()->json([
            'message' => 'Sales order confirmed successfully',
            'data' => $salesOrder
        ]);
    }

    /**
     * Update sales order status to shipped and adjust stock.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function ship($id)
    {
        $salesOrder = SalesOrder::with('items.product')->find($id);
        
        if (!$salesOrder) {
            return response()->json(['message' => 'Sales order not found'], 404);
        }

        if ($salesOrder->status !== 'confirmed') {
            return response()->json(['message' => 'Only confirmed sales orders can be shipped'], 422);
        }

        try {
            DB::beginTransaction();

            // Update sales order status
            $salesOrder->update(['status' => 'shipped']);

            // Adjust stock for each item
            foreach ($salesOrder->items as $item) {
                $product = Product::find($item->product_id);
                $oldStock = $product->stock;
                $newStock = $oldStock - $item->quantity;
                
                if ($newStock < 0) {
                    throw new \Exception('Insufficient stock for product: ' . $product->name);
                }
                
                // Update product stock
                $product->update(['stock' => $newStock]);

                // Log stock shipment
                Log::info('Stock shipped for product ' . $product->name . ' (ID: ' . $item->product_id . ') from ' . $oldStock . ' to ' . $newStock . ' for SO: ' . $salesOrder->so_number);
            }

            DB::commit();

            return response()->json([
                'message' => 'Sales order shipped successfully and stock updated',
                'data' => $salesOrder
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error shipping sales order: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to ship sales order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update sales order status to delivered.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deliver($id)
    {
        $salesOrder = SalesOrder::find($id);
        
        if (!$salesOrder) {
            return response()->json(['message' => 'Sales order not found'], 404);
        }

        if ($salesOrder->status !== 'shipped') {
            return response()->json(['message' => 'Only shipped sales orders can be marked as delivered'], 422);
        }

        $salesOrder->update(['status' => 'delivered']);

        return response()->json([
            'message' => 'Sales order marked as delivered successfully',
            'data' => $salesOrder
        ]);
    }

    /**
     * Cancel a sales order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancel($id)
    {
        $salesOrder = SalesOrder::find($id);
        
        if (!$salesOrder) {
            return response()->json(['message' => 'Sales order not found'], 404);
        }

        if ($salesOrder->status === 'shipped' || $salesOrder->status === 'delivered') {
            return response()->json(['message' => 'Cannot cancel a sales order that has been shipped or delivered'], 422);
        }

        $salesOrder->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Sales order cancelled successfully',
            'data' => $salesOrder
        ]);
    }
}