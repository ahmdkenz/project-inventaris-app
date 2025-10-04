<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Product extends Model
{
    use Auditable;
    
    // Konfigurasi untuk ID string
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'name',
        'sku',
        'description',
        'category',
        'category_id',
        'purchase_price',
        'selling_price',
        'stock',
        'min_stock',
        'status',
        'user_id',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    
    /**
     * Get the stock relationship attribute.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function stockRelation()
    {
        return $this->hasOne(Stock::class)->latest();
    }
    
    /**
     * Get the user that created the product.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}