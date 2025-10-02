<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class FirebaseSyncController extends Controller
{
    /**
     * Sinkronisasi data dari SQL ke Firebase
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(Request $request)
    {
        try {
            $table = $request->input('table', 'all');
            
            // Jalankan command sinkronisasi
            Artisan::call('app:sync-to-firebase', [
                '--table' => $table
            ]);
            
            // Ambil output dari command
            $output = Artisan::output();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Sinkronisasi ke Firebase berhasil',
                'details' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal sinkronisasi ke Firebase',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get Firebase sync status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        try {
            // Cek koneksi ke Firebase
            $firebaseService = app(\App\Services\FirebaseService::class);
            $testData = $firebaseService->getData('test');
            
            return response()->json([
                'status' => 'success',
                'connected' => true,
                'message' => 'Firebase connection is active'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'connected' => false,
                'message' => 'Firebase connection error: ' . $e->getMessage()
            ], 500);
        }
    }
}