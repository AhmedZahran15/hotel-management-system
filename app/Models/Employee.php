<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\BelongsToManyRelationship;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Model
{

    use SoftDeletes,HasFactory;

    protected $fillable = [
        "name",
        "national_id",
        "img_name",
        "user_id",
        "creator_user_id",
        "manager_id"
    ];

    public function user(){
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function subordinates(){
        return $this->hasMany(Employee::class,"manager_id");
    }
    public function manager(){
        return $this->belongsTo(Employee::class,"manager_id");
    }
    public function creatorUser(){
        return $this->belongsTo(User::class,"creator_user_id");
    }
}
