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
        Schema::create('clients_phones', function (Blueprint $table) {
            $table->unsignedBigInteger("client_id");
            $table->string("phone_number");
            $table->softDeletes();
            $table->timestamps();
            $table->primary(["client_id","phone_number"]);
            $table->foreign("client_id")->references("id")->on("clients")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients_phones');
    }
};
