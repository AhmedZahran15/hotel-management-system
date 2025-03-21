<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Floor extends Model
{
    protected $primaryKey = 'number'; // Specify the primary key

    /** @use HasFactory<\Database\Factories\UserFactory> */
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
