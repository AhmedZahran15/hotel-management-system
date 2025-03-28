<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Room extends Model implements HasMedia
{
    protected $primaryKey = 'number'; // Specify the primary key

    /** @use HasFactory<\Database\Factories\RoomFactory> */
    use HasFactory, SoftDeletes, InteractsWithMedia;

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        "number",
        "capacity",
        "room_price",
        "state",
        "floor_number",
        "creator_user_id",
        "title",
        "description",
    ];

    /**
     * Register media collections for the room
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('room_images')
            ->singleFile() // Only keep one image per room
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png']);
    }

    /**
     * Get the room image URL
     */
    public function getImageUrl()
    {
        $media = $this->getFirstMedia('room_images');
        return $media ? $media->getUrl() : null;
    }

    public function creatorUser()
    {
        return $this->belongsTo(User::class, "creator_user_id", "id");
    }
    public function floor()
    {
        return $this->belongsTo(Floor::class, "floor_number", "number");
    }
    public function reservations()
    {
        return $this->hasMany(Reservation::class, "room_number", "number");
    }
    public function scopeAvailable($query)
    {
        return $query->where('state', 'available');
    }
    public function getRouteKeyName()
    {
        return 'number';
    }
}
