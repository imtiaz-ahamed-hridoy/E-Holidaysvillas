<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    protected $table = 'property_types';
    protected $primaryKey = 'property_type_id';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'marker_file',
        'marker'
    ];

    protected static function booted()
    {
        static::deleting(function ($data) {
            if ($data->marker_file && \Illuminate\Support\Facades\Storage::disk('public')->exists($data->marker_file)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($data->marker_file);
            }
        });
    }
}
