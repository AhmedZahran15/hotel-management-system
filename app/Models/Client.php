<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;
    protected $fillable = [
        "name",
        "country",
        "gender",
        "approved_by",
        "user_id"

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
}
