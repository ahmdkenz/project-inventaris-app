<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if not exists
        $adminEmail = 'admin@inventaris.com';
        
        if (!User::where('email', $adminEmail)->exists()) {
            User::create([
                'name' => 'Administrator',
                'email' => $adminEmail,
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]);
        }

        // Create staff user if not exists
        $staffEmail = 'staff@inventaris.com';
        
        if (!User::where('email', $staffEmail)->exists()) {
            User::create([
                'name' => 'Staff User',
                'email' => $staffEmail,
                'password' => Hash::make('staff123'),
                'role' => 'staff',
            ]);
        }
    }
}
