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
        // Create a new table with string tokenable_id
        Schema::create('personal_access_tokens_new', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_id', 255); // Changed to string
            $table->string('tokenable_type');
            $table->index(['tokenable_id', 'tokenable_type']);
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        // Copy data from old table to new table
        if (Schema::hasTable('personal_access_tokens')) {
            $tokens = DB::table('personal_access_tokens')->get();
            foreach ($tokens as $token) {
                DB::table('personal_access_tokens_new')->insert([
                    'id' => $token->id,
                    'tokenable_id' => (string) $token->tokenable_id,
                    'tokenable_type' => $token->tokenable_type,
                    'name' => $token->name,
                    'token' => $token->token,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at,
                    'expires_at' => $token->expires_at,
                    'created_at' => $token->created_at,
                    'updated_at' => $token->updated_at
                ]);
            }

            // Drop old table
            Schema::drop('personal_access_tokens');

            // Rename new table to original name
            Schema::rename('personal_access_tokens_new', 'personal_access_tokens');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // If needed to rollback, recreate with bigint
        Schema::create('personal_access_tokens_old', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        if (Schema::hasTable('personal_access_tokens')) {
            // Attempt to convert string IDs back to integers for users
            $tokens = DB::table('personal_access_tokens')->get();
            foreach ($tokens as $token) {
                try {
                    if ($token->tokenable_type === 'App\\Models\\User') {
                        // Skip tokens that can't be converted back to int
                        continue;
                    }
                    
                    DB::table('personal_access_tokens_old')->insert([
                        'id' => $token->id,
                        'tokenable_id' => $token->tokenable_id,
                        'tokenable_type' => $token->tokenable_type,
                        'name' => $token->name,
                        'token' => $token->token,
                        'abilities' => $token->abilities,
                        'last_used_at' => $token->last_used_at,
                        'expires_at' => $token->expires_at,
                        'created_at' => $token->created_at,
                        'updated_at' => $token->updated_at
                    ]);
                } catch (\Exception $e) {
                    // Skip any tokens that can't be converted
                    continue;
                }
            }

            // Drop current table
            Schema::drop('personal_access_tokens');

            // Rename old table back to original name
            Schema::rename('personal_access_tokens_old', 'personal_access_tokens');
        }
    }
};