<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected $stockService;
    protected $supplierService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }
    
    /**
     * Set the supplier service.
     *
     * @param \App\Services\SupplierService $supplierService
     * @return void
     */
    public function setSupplierService(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * Create a new purchase order
     * 
     * @param array $data Order data
     * @return PurchaseOrder
     */
    public function createPurchaseOrder(array $data)
    {
        try {
            DB::beginTransaction();

            $total = 0;
            foreach ($data['items'] as $item) {
                $total += $item['quantity'] * $item['unit_price'];
            }

            // Create purchase order
            $purchaseOrderData = [
                'po_number' => $data['po_number'],
                'supplier_name' => $data['supplier_name'] ?? null,
                'order_date' => $data['order_date'],
                'expected_delivery' => $data['expected_delivery'] ?? null,
                'notes' => $data['notes'] ?? null,
                'total_amount' => $total,
                'status' => 'pending'
            ];
            
            // Add supplier_id if provided
            if (isset($data['supplier_id'])) {
                $purchaseOrderData['supplier_id'] = $data['supplier_id'];
                
                // If we have supplier_id but no supplier_name, get it from the supplier model
                if (empty($purchaseOrderData['supplier_name']) && $this->supplierService) {
                    try {
                        $supplier = $this->supplierService->getSupplierById($data['supplier_id']);
                        $purchaseOrderData['supplier_name'] = $supplier->name;
                    } catch (\Exception $e) {
                        Log::warning('Could not find supplier for ID: ' . $data['supplier_id']);
                    }
                }
            }
            
            $purchaseOrder = PurchaseOrder::create($purchaseOrderData);

            // Create order items
            foreach ($data['items'] as $item) {
                // Get the product name from the database
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    Log::error('Produk tidak ditemukan', [
                        'product_id' => $item['product_id'],
                        'product_id_type' => gettype($item['product_id'])
                    ]);
                    throw new \Exception("Produk dengan ID '{$item['product_id']}' tidak ditemukan");
                }
                
                $productName = $product->name;
                
                // Pastikan product_id adalah string
                $productId = (string) $item['product_id'];
                
                // Log untuk debugging
                Log::info('Membuat purchase order item', [
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $productId,
                    'product_id_type' => gettype($productId),
                    'product_name' => $item['product_name'] ?? $productName,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price']
                ]);
                
                try {
                    // Create the purchase order item with product name
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $productId,
                        'product_name' => $item['product_name'] ?? $productName,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price']
                    ]);
                } catch (\Exception $e) {
                    Log::error('Gagal membuat purchase order item: ' . $e->getMessage(), [
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $productId,
                        'exception' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            DB::commit();
            return $purchaseOrder->load('items.product');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating purchase order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new sales order
     * 
     * @param array $data Order data
     * @return SalesOrder
     */
    public function createSalesOrder(array $data)
    {
        try {
            DB::beginTransaction();

            // Check stock availability
            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                if (!$product || $product->stock < $item['quantity']) {
                    throw new \Exception('Insufficient stock for product ID: ' . $item['product_id']);
                }
            }

            $total = 0;
            foreach ($data['items'] as $item) {
                $total += $item['quantity'] * $item['unit_price'];
            }

            // Create sales order
            $salesOrder = SalesOrder::create([
                'so_number' => $data['so_number'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'order_date' => $data['order_date'],
                'expected_delivery' => $data['expected_delivery'] ?? null,
                'shipping_address' => $data['shipping_address'],
                'notes' => $data['notes'] ?? null,
                'total_amount' => $total,
                'status' => 'pending'
            ]);

            // Create order items
            foreach ($data['items'] as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price']
                ]);
            }

            DB::commit();
            return $salesOrder->load('items.product');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating sales order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a purchase order
     * 
     * @param PurchaseOrder $order The order to update
     * @param array $data The new order data
     * @return PurchaseOrder
     */
    public function updatePurchaseOrder(PurchaseOrder $order, array $data)
    {
        if ($order->status !== 'pending') {
            throw new \Exception('Cannot update a purchase order that is not pending');
        }

        try {
            DB::beginTransaction();

            // Update order data
            $updateData = [
                'order_date' => $data['order_date'] ?? $order->order_date,
                'expected_delivery' => $data['expected_delivery'] ?? $order->expected_delivery,
                'notes' => $data['notes'] ?? $order->notes,
            ];
            
            // Update supplier information
            if (isset($data['supplier_id'])) {
                $updateData['supplier_id'] = $data['supplier_id'];
                
                // If supplier_id changes, update supplier_name as well
                if ($this->supplierService && $data['supplier_id'] != $order->supplier_id) {
                    try {
                        $supplier = $this->supplierService->getSupplierById($data['supplier_id']);
                        $updateData['supplier_name'] = $supplier->name;
                    } catch (\Exception $e) {
                        $updateData['supplier_name'] = $data['supplier_name'] ?? $order->supplier_name;
                    }
                }
            } else if (isset($data['supplier_name'])) {
                $updateData['supplier_name'] = $data['supplier_name'];
            }
            
            $order->update($updateData);

            // Update items if provided
            if (isset($data['items'])) {
                // Delete existing items
                $order->items()->delete();

                // Create new items
                $total = 0;
                foreach ($data['items'] as $item) {
                    // Get the product name from the database
                    $product = Product::find($item['product_id']);
                    
                    if (!$product) {
                        Log::error('Produk tidak ditemukan saat memperbarui', [
                            'product_id' => $item['product_id'],
                            'product_id_type' => gettype($item['product_id'])
                        ]);
                        throw new \Exception("Produk dengan ID '{$item['product_id']}' tidak ditemukan");
                    }
                    
                    $productName = $product->name;
                    
                    // Pastikan product_id adalah string
                    $productId = (string) $item['product_id'];
                    
                    // Log untuk debugging
                    Log::info('Memperbarui purchase order item', [
                        'purchase_order_id' => $order->id,
                        'product_id' => $productId,
                        'product_id_type' => gettype($productId),
                        'product_name' => $item['product_name'] ?? $productName,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price']
                    ]);
                    
                    try {
                        PurchaseOrderItem::create([
                            'purchase_order_id' => $order->id,
                            'product_id' => $productId,
                            'product_name' => $item['product_name'] ?? $productName,
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price']
                        ]);
                        $total += $item['quantity'] * $item['unit_price'];
                    } catch (\Exception $e) {
                        Log::error('Gagal memperbarui purchase order item: ' . $e->getMessage(), [
                            'purchase_order_id' => $order->id,
                            'product_id' => $productId,
                            'exception' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        throw $e;
                    }
                }

                // Update total
                $order->update(['total_amount' => $total]);
            }

            DB::commit();
            return $order->load('items.product');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating purchase order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a sales order
     * 
     * @param SalesOrder $order The order to update
     * @param array $data The new order data
     * @return SalesOrder
     */
    public function updateSalesOrder(SalesOrder $order, array $data)
    {
        if ($order->status !== 'pending') {
            throw new \Exception('Cannot update a sales order that is not pending');
        }

        try {
            DB::beginTransaction();

            // Update order data
            $order->update([
                'customer_name' => $data['customer_name'] ?? $order->customer_name,
                'customer_email' => $data['customer_email'] ?? $order->customer_email,
                'customer_phone' => $data['customer_phone'] ?? $order->customer_phone,
                'order_date' => $data['order_date'] ?? $order->order_date,
                'expected_delivery' => $data['expected_delivery'] ?? $order->expected_delivery,
                'shipping_address' => $data['shipping_address'] ?? $order->shipping_address,
                'notes' => $data['notes'] ?? $order->notes,
            ]);

            // Update items if provided
            if (isset($data['items'])) {
                // Check stock availability
                foreach ($data['items'] as $item) {
                    $product = Product::find($item['product_id']);
                    if (!$product || $product->stock < $item['quantity']) {
                        throw new \Exception('Insufficient stock for product ID: ' . $item['product_id']);
                    }
                }

                // Delete existing items
                $order->items()->delete();

                // Create new items
                $total = 0;
                foreach ($data['items'] as $item) {
                    SalesOrderItem::create([
                        'sales_order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price']
                    ]);
                    $total += $item['quantity'] * $item['unit_price'];
                }

                // Update total
                $order->update(['total_amount' => $total]);
            }

            DB::commit();
            return $order->load('items.product');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating sales order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Receive a purchase order and update inventory
     * 
     * @param PurchaseOrder $order
     * @param int $userId
     * @return PurchaseOrder
     */
    public function receivePurchaseOrder(PurchaseOrder $order, $userId)
    {
        if ($order->status !== 'approved') {
            throw new \Exception('Only approved purchase orders can be received');
        }

        try {
            DB::beginTransaction();

            // Update purchase order status
            $order->update(['status' => 'received']);

            // Load items with products
            $order->load('items.product');

            // Adjust stock for each item
            foreach ($order->items as $item) {
                $product = $item->product;
                $oldStock = $product->stock;
                $newStock = $oldStock + $item->quantity;
                
                // Update product stock
                $product->update(['stock' => $newStock]);

                // Record transaction
                Transaction::create([
                    'product_id' => $item->product_id,
                    'type' => 'in',
                    'quantity' => $item->quantity,
                    'description' => 'Stock received from PO: ' . $order->po_number,
                    'user_id' => $userId,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'price' => $item->unit_price
                ]);
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error receiving purchase order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ship a sales order and update inventory
     * 
     * @param SalesOrder $order
     * @param int $userId
     * @return SalesOrder
     */
    public function shipSalesOrder(SalesOrder $order, $userId)
    {
        if ($order->status !== 'confirmed') {
            throw new \Exception('Only confirmed sales orders can be shipped');
        }

        try {
            DB::beginTransaction();

            // Load items with products
            $order->load('items.product');

            // Check stock availability again
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product->stock < $item->quantity) {
                    throw new \Exception('Insufficient stock for product: ' . $product->name);
                }
            }

            // Update sales order status
            $order->update(['status' => 'shipped']);

            // Adjust stock for each item
            foreach ($order->items as $item) {
                $product = $item->product;
                $oldStock = $product->stock;
                $newStock = $oldStock - $item->quantity;
                
                // Update product stock
                $product->update(['stock' => $newStock]);

                // Record transaction
                Transaction::create([
                    'product_id' => $item->product_id,
                    'type' => 'out',
                    'quantity' => $item->quantity,
                    'description' => 'Stock shipped for SO: ' . $order->so_number,
                    'user_id' => $userId,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'price' => $item->unit_price
                ]);
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error shipping sales order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get orders statistics
     * 
     * @return array
     */
    public function getOrdersStats()
    {
        $currentMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // Sales Orders
        $currentMonthSales = SalesOrder::where('created_at', '>=', $currentMonthStart)->count();
        $lastMonthSales = SalesOrder::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        // Purchase Orders
        $currentMonthPurchases = PurchaseOrder::where('created_at', '>=', $currentMonthStart)->count();
        $lastMonthPurchases = PurchaseOrder::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        // Pending Orders
        $pendingSales = SalesOrder::where('status', 'pending')->count();
        $pendingPurchases = PurchaseOrder::where('status', 'pending')->count();
        $pendingOrders = $pendingSales + $pendingPurchases;

        // Completed Orders
        $completedSales = SalesOrder::whereIn('status', ['delivered'])->count();
        $completedPurchases = PurchaseOrder::whereIn('status', ['received'])->count();
        $completedOrders = $completedSales + $completedPurchases;

        // Calculate trends
        $salesOrdersTrend = $lastMonthSales > 0 ? round(($currentMonthSales - $lastMonthSales) / $lastMonthSales * 100) : 0;
        $purchaseOrdersTrend = $lastMonthPurchases > 0 ? round(($currentMonthPurchases - $lastMonthPurchases) / $lastMonthPurchases * 100) : 0;

        return [
            'totalSalesOrders' => SalesOrder::count(),
            'salesOrdersTrend' => $salesOrdersTrend,
            'totalPurchaseOrders' => PurchaseOrder::count(),
            'purchaseOrdersTrend' => $purchaseOrdersTrend,
            'pendingOrders' => $pendingOrders,
            'completedOrders' => $completedOrders,
        ];
    }

    /**
     * Get recent orders
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentOrders($limit = 5)
    {
        $recentOrders = [];

        $salesOrders = SalesOrder::orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'type' => 'sales',
                    'number' => $order->so_number,
                    'customer' => $order->customer_name,
                    'amount' => $order->total_amount,
                    'status' => $order->status,
                    'date' => $order->created_at->format('Y-m-d')
                ];
            });

        $purchaseOrders = PurchaseOrder::orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'type' => 'purchase',
                    'number' => $order->po_number,
                    'supplier' => $order->supplier_name,
                    'amount' => $order->total_amount,
                    'status' => $order->status,
                    'date' => $order->created_at->format('Y-m-d')
                ];
            });

        $recentOrders = $salesOrders->concat($purchaseOrders)
            ->sortByDesc('date')
            ->take($limit)
            ->values()
            ->all();

        return $recentOrders;
    }
}