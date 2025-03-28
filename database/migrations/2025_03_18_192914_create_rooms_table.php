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
        Schema::create('rooms', function (Blueprint $table) {
            $table->unsignedInteger("number")->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger("capacity");
            $table->unsignedInteger('room_price');// will be in cents any way so no need to be double
            $table->enum("state", ["available","occupied","being_reserved","maintenance"]);
            $table->unsignedBigInteger('floor_number');
            $table->unsignedBigInteger("creator_user_id")->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->primary(["number"]);
            $table->foreign('floor_number')->references('number')->on('floors')->onDelete('cascade');
            $table->foreign("creator_user_id")->references("id")->on("users")->onDelete("set null");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
