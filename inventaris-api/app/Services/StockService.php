<?php

namespace App\Services;

use App\Models\Stock;

class StockService
{
    public function adjustStock(array $data)
    {
        $stock = Stock::where('product_id', $data['product_id'])->first();
        if ($stock) {
            $stock->quantity += $data['quantity'];
            $stock->save();
        }
    }
}