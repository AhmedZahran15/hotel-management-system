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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('national_id')->unique();
            $table->string('img_name')->default("default.jpg");
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('creator_user_id');
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users'); // is a
            $table->foreign('creator_user_id')->references('id')->on('users'); //created by
            $table->foreign('manager_id')->references('id')->on('employees');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
