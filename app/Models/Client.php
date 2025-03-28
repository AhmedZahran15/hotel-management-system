<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes,HasFactory;
    protected $fillable = [
        "name",
        "img_name",
        "country",
        "gender",
        "approved_by",
        "user_id",
        "phone",

    ];
    protected $casts = [
        'gender' => 'string',
        'approved_by' => 'integer',
        'user_id' => 'integer',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function reservations(){
        return $this->hasMany(Reservation::class);
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function phones(){
        return $this->hasMany(Phone::class);
    }
    public function countryInfo(){
        return $this->belongsTo(Country::class, 'country');
    }
}
