<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //loop throw all clients and update the country name to the correct name not its equevalent numiric value
        Client::whereRaw("country REGEXP '^[0-9]+$'")->each(function ($client) {
            $country = DB::table('countries')->where('id', $client->country)->first();
            if ($country && isset($country->name)) {
                $client->update(['country' => $country->name]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
