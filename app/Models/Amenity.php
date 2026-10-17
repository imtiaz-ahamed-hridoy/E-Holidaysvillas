<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    protected $table = 'amenities';
    protected $primaryKey = 'amenity_id';

    protected $fillable = [
        'property_id',
        'title',
        'icon',
        'icon_type',
        'status',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
