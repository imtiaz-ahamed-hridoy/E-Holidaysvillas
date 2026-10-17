<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyPhoto extends Model
{
    protected $table = 'property_photos';
    protected $primaryKey = 'property_photo_id';

    protected $fillable = [
        'property_id',
        'property_photo_file',
        'status',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }

    /**
     * Accessor to automatically format photo URL.
     */
    public function getPropertyPhotoFileAttribute($value)
    {
        return self::formatUrl($value);
    }

    /**
     * Format photo URL safely ensuring 'storage/' is included regardless of input format.
     */
    public static function formatUrl(?string $url): ?string
    {
        return \App\Helpers\Helper::imageUrl($url);
    }
}

