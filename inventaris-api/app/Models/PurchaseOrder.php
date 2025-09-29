<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'supplier_name',
        'order_date',
        'expected_delivery',
        'notes',
        'total_amount',
        'status'
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}