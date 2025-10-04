<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(): JsonResponse
    {
        try {
            $users = User::select('id', 'name', 'email', 'role', 'status', 'created_at', 'last_login')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role ?? 'staff',
                        'status' => $user->status ?? 'active',
                        'created_at' => optional($user->created_at)->toIso8601String(),
                        'last_login' => optional($user->last_login)->toIso8601String(),
                    ];
                });

            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data pengguna',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'required|in:admin,staff',
                'status' => 'sometimes|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Generate unique ID for user
            $userId = $this->generateUserId();
            
            \Log::info("Creating new user with ID: {$userId}, Name: {$request->name}, Email: {$request->email}");

            $user = User::create([
                'id' => $userId,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => $request->status ?? 'active',
            ]);

            // Pastikan user berhasil dibuat dan memiliki ID
            if (!$user || !$user->id) {
                \Log::error("Failed to create user or get user ID");
                throw new \Exception("Gagal membuat pengguna, data tidak tersimpan dengan benar");
            }

            \Log::info("User created successfully with ID: {$user->id}");

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'created_at' => $user->created_at->toISOString(),
                'last_login' => null,
            ], 201);
        } catch (\Exception $e) {
            \Log::error("Error creating user: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Gagal membuat pengguna baru',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'staff',
                'status' => $user->status ?? 'active',
                'created_at' => optional($user->created_at)->toIso8601String(),
                'last_login' => optional($user->last_login)->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Pengguna tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            \Log::info("Attempting to update user ID: {$id}", [
                'request_data' => $request->except('password')
            ]);
            
            $user = User::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'password' => 'sometimes|nullable|string|min:8',
                'role' => 'sometimes|required|in:admin,staff',
                'status' => 'sometimes|in:active,inactive'
            ]);

            if ($validator->fails()) {
                \Log::warning("Validation failed for user update ID: {$id}", [
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->only(['name', 'email', 'role', 'status']);
            
            // Hanya update password jika ada nilai yang diberikan
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            \Log::info("Updating user with data", [
                'user_id' => $id,
                'update_data' => array_keys($updateData)
            ]);

            $user->update($updateData);

            // Pastikan update berhasil dengan me-load ulang data pengguna
            $user->refresh();
            
            \Log::info("User updated successfully ID: {$id}");

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'created_at' => $user->created_at->toISOString(),
                'last_login' => $user->last_login ? $user->last_login->toISOString() : null,
            ]);
        } catch (\Exception $e) {
            \Log::error("Error updating user ID {$id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Gagal memperbarui pengguna',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user status.
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        try {
            \Log::info("Updating status for user ID: {$id}", [
                'requested_status' => $request->status
            ]);
            
            $user = User::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator->fails()) {
                \Log::warning("Invalid status requested for user ID: {$id}", [
                    'errors' => $validator->errors()->toArray(),
                    'requested_status' => $request->status
                ]);
                
                return response()->json([
                    'message' => 'Status tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Cek apakah user mencoba menonaktifkan akunnya sendiri
            if (auth()->id() === $user->id && $request->status === 'inactive') {
                \Log::warning("User attempted to deactivate own account, ID: {$id}");
                return response()->json([
                    'message' => 'Anda tidak dapat menonaktifkan akun sendiri'
                ], 403);
            }

            $oldStatus = $user->status;
            $user->update(['status' => $request->status]);
            
            \Log::info("User status updated for ID: {$id}", [
                'old_status' => $oldStatus,
                'new_status' => $user->status
            ]);

            return response()->json([
                'message' => 'Status pengguna berhasil diperbarui',
                'status' => $user->status
            ]);
        } catch (\Exception $e) {
            \Log::error("Error updating status for user ID {$id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Gagal memperbarui status pengguna',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            \Log::info("Attempting to delete user ID: {$id}");
            
            $user = User::findOrFail($id);
            
            // Prevent deleting the current authenticated user
            if (auth()->id() === $user->id) {
                \Log::warning("Attempted to delete own account, user ID: {$id}");
                return response()->json([
                    'message' => 'Tidak dapat menghapus akun sendiri'
                ], 403);
            }
            
            // Simpan nama user untuk pesan log
            $userName = $user->name;
            
            // Hapus token akses terlebih dahulu
            \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $id)->delete();
            
            $user->delete();
            
            \Log::info("User deleted successfully: {$userName} (ID: {$id})");

            return response()->json([
                'message' => 'Pengguna berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            \Log::error("Error deleting user ID {$id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Gagal menghapus pengguna',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate unique user ID
     * 
     * @return string
     */
    private function generateUserId(): string
    {
        $prefix = 'USR'; // Prefix untuk user
        $timestamp = substr(time(), -6); // 6 digit terakhir dari timestamp
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT); // 4 digit random
        
        // Format: USR-[timestamp]-[random]
        $userId = $prefix . '-' . $timestamp . '-' . $random;
        
        // Cek apakah ID sudah ada, jika ya, generate ulang
        $attempts = 0;
        $maxAttempts = 10; // Batas maksimal percobaan
        
        while (User::where('id', $userId)->exists()) {
            if (++$attempts >= $maxAttempts) {
                // Gunakan microtime untuk memastikan unik jika terlalu banyak percobaan
                $timestamp = substr(str_replace('.', '', microtime(true)), -6);
            }
            
            $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $userId = $prefix . '-' . $timestamp . '-' . $random;
        }
        
        \Log::info("Generated unique user ID: {$userId}");
        return $userId;
    }
}