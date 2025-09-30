<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'contact_person',
        'notes',
        'status'
    ];
    
    /**
     * Get the purchase orders for the supplier.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
