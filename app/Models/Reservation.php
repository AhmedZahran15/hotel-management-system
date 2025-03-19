<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;
    protected $fillable = [
        "reservation_date",
        "reservation_price",
        "client_id",
        "room_number"
    ];

    public function room(){
        return $this->belongsTo(Room::class,"room_number","number");
    }
    public function client(){
        return $this->belongsTo(Client::class,"client_id","id");
    }
}
