<?php

namespace App\Services;

use App\Models\Transaction;

class ReportService
{
    public function generateSalesReport()
    {
        return Transaction::where('type', 'sale')->get();
    }

    public function generateStockReport()
    {
        return Transaction::where('type', 'stock_adjustment')->get();
    }
}