<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    use HasFactory;
    protected $table = 'clients_phones';

    protected $fillable = [
        "client_id",
        "phone_number"
    ];
    public function client (){
        return $this->belongsTo(Client::class);
    }
}
