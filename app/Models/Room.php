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
        $media = $this->getFirstMediaUrl('room_images');
        if ($media) {
            $path = parse_url($media, PHP_URL_PATH);
            return url((string)$path);
        }
        return null;
    }
    public function updateImage($newImage): void
    {
        // First clear the existing avatar image
        $this->clearMediaCollection('room_images');

        // Then add the new avatar image
        $extension = $newImage->getClientOriginalExtension();
        $uniqueFileName = time() . '_' . $this->id . '.' . $extension;
        $this->addMedia($newImage)->usingFileName($uniqueFileName)->toMediaCollection('room_images');
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
