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
        Schema::table('reservations', function (Blueprint $table) {
            // Add new columns
            $table->string('payment_status')->default('pending');
            $table->string('payment_id')->nullable();
            $table->unsignedInteger('accompany_number')->default(1);
            
            // Add indexes for better query performance
            $table->index('payment_status');
            $table->index('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['payment_id']);
            
            // Drop columns
            $table->dropColumn(['payment_status', 'payment_id', 'accompany_number']);
        });
    }
};