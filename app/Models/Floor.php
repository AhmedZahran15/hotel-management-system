<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Floor extends Model
{

    /** @use HasFactory<\Database\Factories\FloorFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable= [
        "name",
        "creator_user_id"
    ];
    public function creatorUser(){
        return $this->belongsTo(User::class,"creator_user_id");
    }
    public function rooms(){
        return $this->hasMany(Room::class,"floor_number","number");
    }
}
