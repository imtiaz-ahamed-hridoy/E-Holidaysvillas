<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyVideo extends Model
{
    protected $table = 'property_videos';
    protected $primaryKey = 'property_video_id';

    protected $fillable = [
        'property_id',
        'video_type',
        'video_file',
        'poster_file',
        'embed',
        'status',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
