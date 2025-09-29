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
        // Drop and recreate the table approach to handle the tokenable_id column type change
        $tokens = DB::table('personal_access_tokens')->get();
        
        Schema::dropIfExists('personal_access_tokens');
        
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_id', 255); // Change to string
            $table->string('tokenable_type');
            $table->index(['tokenable_id', 'tokenable_type']);
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
        
        // Restore data if needed
        foreach ($tokens as $token) {
            // Convert numeric ids to string if they're for users
            if ($token->tokenable_type === 'App\\Models\\User') {
                DB::table('personal_access_tokens')->insert([
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
            } else {
                DB::table('personal_access_tokens')->insert([
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
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // If needed to rollback, we'd recreate with bigint
        Schema::dropIfExists('personal_access_tokens');
        
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }
};
    }
};
