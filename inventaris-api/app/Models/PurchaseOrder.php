<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class PurchaseOrder extends Model
{
    use Auditable;
    protected $fillable = [
        'po_number',
        'supplier_id',
        'supplier_name',
        'order_date',
        'expected_delivery',
        'notes',
        'total_amount',
        'status',
        'rejection_reason',
        'created_by',
        'updated_by'
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
    
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}