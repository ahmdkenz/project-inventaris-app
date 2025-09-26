<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Get all settings
     */
    public function index(Request $request)
    {
        try {
            // Ambil settings dari database, jika tidak ada, gunakan default
            $settings = DB::table('settings')->first();
            
            if (!$settings) {
                // Return default settings
                return response()->json([
                    'settings' => $this->getDefaultSettings()
                ]);
            }
            
            // Convert stored JSON to actual settings object
            $settingsData = json_decode($settings->settings_data, true);
            
            return response()->json([
                'settings' => $settingsData
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching settings: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch settings',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update settings
     */
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'settings' => 'required|array',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Invalid settings data',
                    'messages' => $validator->errors()
                ], 400);
            }
            
            // Update or create settings in database
            DB::table('settings')->updateOrInsert(
                ['id' => 1], // Identifier
                [
                    'settings_data' => json_encode($request->settings),
                    'updated_at' => now()
                ]
            );
            
            return response()->json([
                'message' => 'Settings updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating settings: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update settings',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reset settings to default
     */
    public function reset()
    {
        try {
            // Set settings to default
            DB::table('settings')->updateOrInsert(
                ['id' => 1],
                [
                    'settings_data' => json_encode($this->getDefaultSettings()),
                    'updated_at' => now()
                ]
            );
            
            return response()->json([
                'message' => 'Settings reset to defaults successfully',
                'settings' => $this->getDefaultSettings()
            ]);
        } catch (\Exception $e) {
            Log::error('Error resetting settings: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to reset settings',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get system information
     */
    public function systemInfo()
    {
        try {
            // Get latest backup info if exists
            $lastBackup = null;
            if (Storage::disk('backups')->exists('last_backup_time.txt')) {
                $lastBackup = Storage::disk('backups')->get('last_backup_time.txt');
            }
            
            $info = [
                'version' => config('app.version', '1.0.0'),
                'databaseVersion' => DB::select('SELECT version() as version')[0]->version ?? 'Unknown',
                'lastBackup' => $lastBackup,
                'serverStatus' => 'online',
                'totalUsers' => User::count(),
                'totalProducts' => Product::count(),
            ];
            
            return response()->json([
                'info' => $info
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching system info: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch system information',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create a backup
     */
    public function createBackup()
    {
        try {
            // Here we would implement the actual backup logic
            // For now, we'll just simulate a successful backup
            
            // Update the last backup time
            Storage::disk('backups')->put('last_backup_time.txt', now()->toISOString());
            
            return response()->json([
                'message' => 'Backup created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating backup: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to create backup',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Download the latest backup
     */
    public function downloadBackup()
    {
        try {
            // Here we would implement the actual backup download logic
            // For now, we'll just create a dummy SQL file
            
            $backupContent = "-- Dummy SQL backup file\n-- Generated on: " . now()->toDateTimeString() . "\n\n";
            $backupContent .= "-- This is just a placeholder for the actual backup functionality\n";
            $backupContent .= "-- In a real implementation, this would contain SQL statements to recreate the database\n";
            
            return response($backupContent, 200, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="inventory_backup_' . now()->format('Y-m-d') . '.sql"',
            ]);
        } catch (\Exception $e) {
            Log::error('Error downloading backup: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to download backup',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get default settings
     */
    private function getDefaultSettings()
    {
        return [
            'general' => [
                'companyName' => 'Inventaris App',
                'companyEmail' => 'admin@inventarisapp.com',
                'companyPhone' => '+62 812 3456 7890',
                'companyAddress' => 'Jl. Contoh No. 123, Jakarta, Indonesia',
                'timezone' => 'Asia/Jakarta',
                'dateFormat' => 'DD/MM/YYYY',
                'currency' => 'IDR'
            ],
            'inventory' => [
                'defaultMinStock' => 10,
                'lowStockThreshold' => 5,
                'autoGenerateSku' => true,
                'stockAlerts' => true,
                'skuPrefix' => 'PRD'
            ],
            'notifications' => [
                'emailNotifications' => true,
                'pushNotifications' => true,
                'smsNotifications' => false,
                'types' => [
                    'lowStock' => true,
                    'newOrders' => true,
                    'systemUpdates' => true,
                    'reports' => false
                ]
            ],
            'security' => [
                'sessionTimeout' => 60,
                'maxLoginAttempts' => 5,
                'passwordMinLength' => 8,
                'requireTwoFactor' => false
            ],
            'backup' => [
                'autoBackup' => true,
                'frequency' => 'weekly',
                'retainDays' => 30
            ]
        ];
    }
}