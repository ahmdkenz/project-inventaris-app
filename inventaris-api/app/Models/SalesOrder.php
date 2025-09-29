<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
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