<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Transaction;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
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
        $orders = PurchaseOrder::with('items.product')->orderBy('created_at', 'desc')->get();
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
            'po_number' => 'required|string|max:50|unique:purchase_orders',
            'supplier_name' => 'required|string|max:100',
            'order_date' => 'required|date',
            'expected_delivery' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate total amount
            $total_amount = 0;
            foreach ($request->items as $item) {
                $total_amount += $item['quantity'] * $item['unit_price'];
            }

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $request->po_number,
                'supplier_name' => $request->supplier_name,
                'order_date' => $request->order_date,
                'expected_delivery' => $request->expected_delivery,
                'notes' => $request->notes,
                'total_amount' => $total_amount,
                'status' => 'pending'
            ]);

            // Add items
            foreach ($request->items as $item) {
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price']
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Purchase order created successfully',
                'data' => $purchaseOrder->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating purchase order: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create purchase order', 'error' => $e->getMessage()], 500);
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
        $purchaseOrder = PurchaseOrder::with('items.product')->find($id);
        
        if (!$purchaseOrder) {
            return response()->json(['message' => 'Purchase order not found'], 404);
        }

        return response()->json(['data' => $purchaseOrder]);
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
        $purchaseOrder = PurchaseOrder::find($id);
        
        if (!$purchaseOrder) {
            return response()->json(['message' => 'Purchase order not found'], 404);
        }

        // Only allow editing for pending purchase orders
        if ($purchaseOrder->status !== 'pending') {
            return response()->json(['message' => 'Cannot edit a purchase order that is not pending'], 422);
        }

        $validator = Validator::make($request->all(), [
            'supplier_name' => 'sometimes|required|string|max:100',
            'order_date' => 'sometimes|required|date',
            'expected_delivery' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Update purchase order
            $purchaseOrder->update([
                'supplier_name' => $request->supplier_name ?? $purchaseOrder->supplier_name,
                'order_date' => $request->order_date ?? $purchaseOrder->order_date,
                'expected_delivery' => $request->expected_delivery,
                'notes' => $request->notes
            ]);

            // Update items if provided
            if ($request->has('items')) {
                // Remove existing items
                $purchaseOrder->items()->delete();
                
                // Add new items
                $total_amount = 0;
                foreach ($request->items as $item) {
                    $purchaseOrder->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price']
                    ]);
                    $total_amount += $item['quantity'] * $item['unit_price'];
                }

                // Update total amount
                $purchaseOrder->update(['total_amount' => $total_amount]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Purchase order updated successfully',
                'data' => $purchaseOrder->load('items.product')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating purchase order: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update purchase order', 'error' => $e->getMessage()], 500);
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
        $purchaseOrder = PurchaseOrder::find($id);
        
        if (!$purchaseOrder) {
            return response()->json(['message' => 'Purchase order not found'], 404);
        }

        // Only allow deletion for pending purchase orders
        if ($purchaseOrder->status !== 'pending') {
            return response()->json(['message' => 'Cannot delete a purchase order that is not pending'], 422);
        }

        try {
            DB::beginTransaction();

            // Delete items first
            $purchaseOrder->items()->delete();
            
            // Delete the purchase order
            $purchaseOrder->delete();

            DB::commit();

            return response()->json(['message' => 'Purchase order deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting purchase order: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete purchase order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update purchase order status to approved.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    {
        $purchaseOrder = PurchaseOrder::find($id);
        
        if (!$purchaseOrder) {
            return response()->json(['message' => 'Purchase order not found'], 404);
        }

        if ($purchaseOrder->status !== 'pending') {
            return response()->json(['message' => 'Only pending purchase orders can be approved'], 422);
        }

        $purchaseOrder->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Purchase order approved successfully',
            'data' => $purchaseOrder
        ]);
    }

    /**
     * Update purchase order status to received and adjust stock.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function receive(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::with('items.product')->find($id);
        
        if (!$purchaseOrder) {
            return response()->json(['message' => 'Purchase order not found'], 404);
        }

        if ($purchaseOrder->status !== 'approved') {
            return response()->json(['message' => 'Only approved purchase orders can be received'], 422);
        }

        try {
            DB::beginTransaction();

            // Update purchase order status
            $purchaseOrder->update(['status' => 'received']);

            // Adjust stock for each item
            foreach ($purchaseOrder->items as $item) {
                $product = Product::find($item->product_id);
                $oldStock = $product->stock;
                $newStock = $oldStock + $item->quantity;
                
                // Update product stock
                $product->update(['stock' => $newStock]);

                // Create transaction record
                Transaction::create([
                    'product_id' => $item->product_id,
                    'type' => 'in',
                    'quantity' => $item->quantity,
                    'description' => 'Stock received from PO: ' . $purchaseOrder->po_number,
                    'user_id' => auth()->id(),
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'price' => $item->unit_price
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Purchase order received successfully and stock updated',
                'data' => $purchaseOrder
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error receiving purchase order: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to receive purchase order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel a purchase order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancel($id)
    {
        $purchaseOrder = PurchaseOrder::find($id);
        
        if (!$purchaseOrder) {
            return response()->json(['message' => 'Purchase order not found'], 404);
        }

        if ($purchaseOrder->status === 'received') {
            return response()->json(['message' => 'Cannot cancel a received purchase order'], 422);
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Purchase order cancelled successfully',
            'data' => $purchaseOrder
        ]);
    }
}