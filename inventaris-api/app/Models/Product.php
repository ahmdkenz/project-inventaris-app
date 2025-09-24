<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'description',
        'purchase_price',
        'selling_price',
        'stock'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock' => 'integer'
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}