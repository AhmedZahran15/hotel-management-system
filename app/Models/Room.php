<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "number",
        "capacity",
        "status",
        "floor_number",
        "creator_user_id",

    ];

    public function creatorUser(){
        return $this->belongsTo(User::class,"creator_user_id");
    }
    public function floor(){
        return $this->belongsTo(Floor::class,"floor_number");
    }
    public function reservations(){
        return $this->hasMany(Reservation::class,"room_number");
    }
}
