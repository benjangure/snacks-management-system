<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if username column exists
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable()->after('email');
            });
        }
        
        // Update existing users with default usernames
        $users = \App\Models\User::whereNull('username')->orWhere('username', '')->get();
        foreach ($users as $user) {
            $baseUsername = strtolower(str_replace(' ', '_', $user->name));
            $username = $baseUsername;
            $counter = 1;
            
            // Ensure username is unique
            while (\App\Models\User::where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $baseUsername . '_' . $counter;
                $counter++;
            }
            
            $user->update(['username' => $username]);
        }
        
        // Now make username required and unique if not already
        if (!Schema::hasIndex('users', 'users_username_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable(false)->unique()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
