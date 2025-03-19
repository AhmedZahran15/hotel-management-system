<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    /** @use HasFactory<\Database\Factories\RoomFactory> */
    use HasFactory,SoftDeletes;

    protected $fillable = [
        "number",
        "capacity",
        "state",
        "floor_number",
        "creator_user_id",
    ];

    public function creatorUser(){
        return $this->belongsTo(User::class,"creator_user_id");
    }
    public function floor(){
        return $this->belongsTo(Floor::class,"floor_number","number");
    }
    public function reservations(){
        return $this->hasMany(Reservation::class,"room_number","number");
    }
}
