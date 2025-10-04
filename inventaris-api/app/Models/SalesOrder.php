<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class SalesOrder extends Model
{
    use Auditable;
    protected $fillable = [
        'so_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'order_date',
        'expected_delivery',
        'shipping_address',
        'notes',
        'total_amount',
        'status'
    ];

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }
}