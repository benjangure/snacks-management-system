<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mandazi_id')->constrained('mandazi')->onDelete('cascade');
            $table->string('transaction_id')->nullable();
            $table->string('checkout_request_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('phone_number', 15);
            $table->enum('status', ['Pending', 'Success', 'Failed'])->default('Pending');
            $table->text('mpesa_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};