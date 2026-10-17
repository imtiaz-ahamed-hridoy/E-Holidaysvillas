<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    protected $primaryKey = 'location_id';

    protected $fillable = [
        'property_id',
        'country',
        'region',
        'city',
        'area',
        'landmark',
        'address',
        'zip_code',
        'zoom',
        'lat',
        'lng',
        'country_code'
    ];

    /**
     * Get all properties assigned to this location.
     */
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
