<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'minimum_stock',
        'location'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'minimum_stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}