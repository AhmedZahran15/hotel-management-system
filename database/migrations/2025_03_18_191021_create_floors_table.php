<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->bigIncrements("number");
            $table->string("name");
            $table->unsignedBigInteger("creator_user_id");
            $table->softDeletes();
            $table->timestamps();

            $table->foreign("creator_user_id")->references("id")->on("users");


        });
        if (DB::getDriverName() !== 'sqlite')
        {
            DB::statement("ALTER TABLE floors AUTO_INCREMENT = 1000;");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};
