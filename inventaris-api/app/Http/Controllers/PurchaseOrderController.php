<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Transaction;
use App\Services\OrderService;
use App\Services\StockService;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    protected $stockService;
    protected $orderService;
    protected $supplierService;

    public function __construct(StockService $stockService, OrderService $orderService, SupplierService $supplierService)
    {
        $this->stockService = $stockService;
        $this->orderService = $orderService;
        $this->supplierService = $supplierService;
        $this->orderService->setSupplierService($this->supplierService);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = PurchaseOrder::with(['items.product', 'supplier'])->orderBy('created_at', 'desc')->get();
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
        // Log data yang diterima dari front-end untuk debugging
        Log::info('Menerima request pembuatan purchase order baru', [
            'request_data' => $request->all()
        ]);
        
        $validator = Validator::make($request->all(), [
            'po_number' => 'required|string|max:50|unique:purchase_orders',
            'supplier_id' => 'nullable',
            'supplier_name' => 'nullable|string|max:100',
            'order_date' => 'required|date',
            'expected_delivery' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string', // pastikan product_id adalah string
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);
        
        // Validate that either supplier_id or supplier_name is provided
        if (!$request->has('supplier_id') && !$request->has('supplier_name')) {
            Log::warning('Validasi gagal: supplier_id atau supplier_name tidak disediakan');
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => ['supplier' => ['Supplier ID atau nama supplier harus disediakan']]
            ], 422);
        }

        if ($validator->fails()) {
            Log::warning('Validasi gagal untuk purchase order', [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate total amount
            $total_amount = 0;
            foreach ($request->items as $item) {
                $total_amount += $item['quantity'] * $item['unit_price'];
            }
            
            Log::info('Total amount dihitung', [
                'total_amount' => $total_amount
            ]);

            // Verifikasi produk dan konversi data
            $items = $request->input('items');
            $processedItems = [];
            
            foreach ($items as $index => $item) {
                // Pastikan product_id adalah string
                $productId = (string) $item['product_id'];
                
                Log::info('Verifikasi produk', [
                    'product_id' => $productId,
                    'product_id_type' => gettype($productId)
                ]);
                
                $product = Product::find($productId);
                if (!$product) {
                    Log::warning('Produk tidak ditemukan', [
                        'product_id' => $productId,
                        'index' => $index
                    ]);
                    
                    // Coba dengan metode lain
                    $product = Product::where('id', $productId)->first();
                    
                    if (!$product) {
                        // Mencoba lihat semua produk untuk debug
                        $allProducts = Product::all()->pluck('id')->toArray();
                        Log::warning('Semua ID produk yang tersedia:', [
                            'products' => $allProducts,
                            'count' => count($allProducts)
                        ]);
                        
                        throw new \Exception('Produk dengan ID ' . $productId . ' tidak ditemukan dalam sistem');
                    }
                }
                
                // Buat item baru dengan data yang diproses
                $processedItem = $item;
                $processedItem['product_id'] = $productId;
                
                // Tambahkan product_name jika tidak ada
                if (!isset($processedItem['product_name'])) {
                    $processedItem['product_name'] = $product->name;
                }
                
                $processedItems[] = $processedItem;
            }

            // Create purchase order using OrderService
            Log::info('Mencoba membuat purchase order dengan data', [
                'po_number' => $request->po_number,
                'supplier_id' => $request->supplier_id,
                'supplier_name' => $request->supplier_name,
                'order_date' => $request->order_date,
                'total_items' => count($processedItems)
            ]);
            
            $purchaseOrder = $this->orderService->createPurchaseOrder([
                'po_number' => $request->po_number,
                'supplier_id' => $request->supplier_id,
                'supplier_name' => $request->supplier_name,
                'order_date' => $request->order_date,
                'expected_delivery' => $request->expected_delivery,
                'notes' => $request->notes,
                'items' => $processedItems
            ]);

            DB::commit();
            
            Log::info('Purchase order berhasil dibuat', [
                'id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number
            ]);

            return response()->json([
                'message' => 'Purchase order berhasil dibuat',
                'data' => $purchaseOrder->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error membuat purchase order: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Gagal membuat purchase order', 'error' => $e->getMessage()], 500);
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
        $purchaseOrder = PurchaseOrder::with(['items.product', 'supplier'])->find($id);
        
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
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_name' => 'nullable|string|max:100',
            'order_date' => 'sometimes|required|date',
            'expected_delivery' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|string', // pastikan product_id adalah string
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Update purchase order data
            $updateData = [
                'supplier_id' => $request->supplier_id,
                'supplier_name' => $request->supplier_name,
                'order_date' => $request->order_date,
                'expected_delivery' => $request->expected_delivery,
                'notes' => $request->notes
            ];
            
            // Proses items jika ada
            if ($request->has('items')) {
                $items = $request->input('items');
                $processedItems = [];
                
                foreach ($items as $index => $item) {
                    // Pastikan product_id adalah string
                    $productId = (string) $item['product_id'];
                    
                    $product = Product::find($productId);
                    if (!$product) {
                        // Coba dengan metode lain
                        $product = Product::where('id', $productId)->first();
                        
                        if (!$product) {
                            throw new \Exception('Produk dengan ID ' . $productId . ' tidak ditemukan');
                        }
                    }
                    
                    // Buat item baru dengan data yang diproses
                    $processedItem = $item;
                    $processedItem['product_id'] = $productId;
                    
                    // Tambahkan product_name jika tidak ada
                    if (!isset($processedItem['product_name'])) {
                        $processedItem['product_name'] = $product->name;
                    }
                    
                    $processedItems[] = $processedItem;
                }
                
                $updateData['items'] = $processedItems;
            }
            
            $purchaseOrder = $this->orderService->updatePurchaseOrder($purchaseOrder, $updateData);

            DB::commit();

            return response()->json([
                'message' => 'Purchase order updated successfully',
                'data' => $purchaseOrder->load('items.product')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating purchase order: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
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