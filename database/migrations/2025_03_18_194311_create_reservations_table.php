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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->dateTime('reservation_date');
            $table->unsignedInteger('reservation_price');// will be in cents any way so no need to be double
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedInteger('room_number');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign("client_id")->references("id")->on("clients")->onDelete("set null");
            $table->foreign("room_number")->references("number")->on("rooms");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
