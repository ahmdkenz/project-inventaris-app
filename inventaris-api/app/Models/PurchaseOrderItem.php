<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id', 'product_id', 'product_name', 'quantity', 'unit_price'];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        // Hubungkan dengan model Product dan tentukan kunci asing dan kunci lokal
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}