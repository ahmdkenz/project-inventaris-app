<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jika kolom id_string ada, kita akan menggunakannya untuk mengubah id
        if (Schema::hasColumn('users', 'id_string')) {
            // Pastikan semua id_string terisi
            DB::table('users')->whereNull('id_string')->update([
                'id_string' => DB::raw("CONCAT('USR-', SUBSTRING(UNIX_TIMESTAMP(), -6), '-', LPAD(FLOOR(RAND() * 9999) + 1, 4, '0'))")
            ]);
            
            // Hapus foreign key yang mereferensikan users.id jika ada
            if (Schema::hasTable('products') && Schema::hasColumn('products', 'user_id')) {
                Schema::table('products', function (Blueprint $table) {
                    // Cek apakah constraint ada sebelum mencoba drop
                    $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                        WHERE TABLE_NAME = 'products' 
                        AND COLUMN_NAME = 'user_id' 
                        AND CONSTRAINT_NAME != 'PRIMARY' 
                        AND TABLE_SCHEMA = DATABASE()");
                        
                    if (!empty($constraints)) {
                        $table->dropForeign(['user_id']);
                    }
                });
            }
            
            if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'user_id')) {
                Schema::table('transactions', function (Blueprint $table) {
                    // Cek apakah constraint ada sebelum mencoba drop
                    $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                        WHERE TABLE_NAME = 'transactions' 
                        AND COLUMN_NAME = 'user_id' 
                        AND CONSTRAINT_NAME != 'PRIMARY' 
                        AND TABLE_SCHEMA = DATABASE()");
                        
                    if (!empty($constraints)) {
                        $table->dropForeign(['user_id']);
                    }
                });
            }

            // Buat tabel users baru dengan id sebagai string
            Schema::create('temp_users', function (Blueprint $table) {
                $table->string('id', 20)->primary(); // ID sebagai string
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->enum('role', ['admin', 'staff'])->default('staff');
                $table->string('status')->default('active');
                $table->timestamp('last_login')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });

            // Salin data dari tabel users ke tabel sementara
            DB::statement("INSERT INTO temp_users (
                id, name, email, email_verified_at, password, role, status, last_login, created_at, updated_at
            ) 
            SELECT 
                id_string, name, email, email_verified_at, password, role, 
                COALESCE(status, 'active'), last_login, created_at, updated_at 
            FROM users");

            // Hapus tabel users
            Schema::dropIfExists('users');
            
            // Rename tabel sementara ke users
            Schema::rename('temp_users', 'users');
            
            // Ubah tipe kolom user_id di tabel terkait
            if (Schema::hasTable('products') && Schema::hasColumn('products', 'user_id')) {
                // Ubah tipe kolom user_id dari bigint ke string
                Schema::table('products', function (Blueprint $table) {
                    $table->string('user_id', 20)->nullable()->change();
                });
                
                // Peta ID lama ke ID baru
                $userMap = [];
                $users = DB::select("SELECT id, CAST(id AS CHAR) as old_id FROM users");
                foreach ($users as $user) {
                    $userMap[$user->old_id] = $user->id;
                }
                
                // Update referensi di products
                foreach ($userMap as $oldId => $newId) {
                    DB::statement("UPDATE products SET user_id = ? WHERE user_id = ?", [$newId, $oldId]);
                }
                
                // Buat foreign key setelah update data
                Schema::table('products', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                });
            }
            
            if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'user_id')) {
                // Ubah tipe kolom user_id dari bigint ke string
                Schema::table('transactions', function (Blueprint $table) {
                    $table->string('user_id', 20)->nullable()->change();
                });
                
                // Peta ID lama ke ID baru
                $userMap = [];
                $users = DB::select("SELECT id, CAST(id AS CHAR) as old_id FROM users");
                foreach ($users as $user) {
                    $userMap[$user->old_id] = $user->id;
                }
                
                // Update referensi di transactions
                foreach ($userMap as $oldId => $newId) {
                    DB::statement("UPDATE transactions SET user_id = ? WHERE user_id = ?", [$newId, $oldId]);
                }
                
                // Buat foreign key setelah update data
                Schema::table('transactions', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembali ke ID numerik dan menambahkan kolom id_string
        Schema::create('temp_users', function (Blueprint $table) {
            $table->id(); // Kembali ke BigInteger auto-increment
            $table->string('id_string', 20)->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'staff'])->default('staff');
            $table->string('status')->default('active');
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        
        // Hapus foreign key yang mereferensikan users.id
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'user_id')) {
            Schema::table('products', function (Blueprint $table) {
                $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE TABLE_NAME = 'products' 
                    AND COLUMN_NAME = 'user_id' 
                    AND CONSTRAINT_NAME != 'PRIMARY' 
                    AND TABLE_SCHEMA = DATABASE()");
                    
                if (!empty($constraints)) {
                    $table->dropForeign(['user_id']);
                }
            });
        }
        
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'user_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE TABLE_NAME = 'transactions' 
                    AND COLUMN_NAME = 'user_id' 
                    AND CONSTRAINT_NAME != 'PRIMARY' 
                    AND TABLE_SCHEMA = DATABASE()");
                    
                if (!empty($constraints)) {
                    $table->dropForeign(['user_id']);
                }
            });
        }
        
        // Salin data dari tabel users ke tabel temp_users
        $counter = 1;
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            DB::table('temp_users')->insert([
                'id' => $counter++,
                'id_string' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'password' => $user->password,
                'role' => $user->role,
                'status' => $user->status ?? 'active',
                'last_login' => $user->last_login,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]);
        }
        
        // Drop tabel users
        Schema::dropIfExists('users');
        
        // Rename tabel temp_users ke users
        Schema::rename('temp_users', 'users');
        
        // Ubah tipe kolom user_id di tabel terkait kembali ke bigInteger
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'user_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
        
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'user_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }
};
