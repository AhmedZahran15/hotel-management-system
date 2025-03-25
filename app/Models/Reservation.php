<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes, HasFactory;
    protected $fillable = [
        "reservation_date",
        "reservation_price",
        "client_id",
        "room_number",
        "payment_status",
        "payment_id",
        "accompany_number"
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, "room_number", "number");
    }
    public function client()
    {
        return $this->belongsTo(Client::class, "client_id", "id");
    }
}
