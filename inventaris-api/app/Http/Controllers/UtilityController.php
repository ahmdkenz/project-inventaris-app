<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UtilityController extends Controller
{
    /**
     * Generate a unique purchase order number
     * 
     * @return \Illuminate\Http\Response
     */
    public function generatePONumber()
    {
        $date = Carbon::now();
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');
        
        // Format: POyyyymmddXXXX where XXXX is a random number
        do {
            $random = Str::padLeft(mt_rand(1, 9999), 4, '0');
            $poNumber = "PO{$year}{$month}{$day}{$random}";
            $exists = PurchaseOrder::where('po_number', $poNumber)->exists();
        } while ($exists);
        
        return response()->json(['po_number' => $poNumber]);
    }
}
