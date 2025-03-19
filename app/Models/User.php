<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        "creator_user_id",
        "user_type  "
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function profile()
    {
        return $this->user_type === 'client'
            ? $this->hasOne(Client::class, 'user_id')
            : $this->hasOne(Employee::class, 'user_id');
    }
    public function createdEmployees(){
        return $this->hasMany(Employee::class,"creator_user_id");
    }
    public function floors(){
        return $this->hasMany(Floor::class,"creator_user_id");
    }
    public function rooms(){
        return $this->hasMany(Room::class,"creator_user_id");
    }
    public function createdUsers(){
        return $this->hasMany(User::class,"creator_user_id");
    }
    public function approvedClients(){
        return $this->hasMany(Client::class,"approved_by");
    }


}
