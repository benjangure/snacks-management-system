<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if seller_id column already exists before adding it
        if (!Schema::hasColumn('mandazi', 'seller_id')) {
            Schema::table('mandazi', function (Blueprint $table) {
                $table->foreignId('seller_id')->nullable()->constrained('users')->onDelete('cascade');
            });
        }
        
        // Only add transaction_id if it doesn't exist
        if (!Schema::hasColumn('payments', 'transaction_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('transaction_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Only drop if columns exist
        if (Schema::hasColumn('mandazi', 'seller_id')) {
            Schema::table('mandazi', function (Blueprint $table) {
                $table->dropForeign(['seller_id']);
                $table->dropColumn('seller_id');
            });
        }
        
        if (Schema::hasColumn('payments', 'transaction_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('transaction_id');
            });
        }
    }
};