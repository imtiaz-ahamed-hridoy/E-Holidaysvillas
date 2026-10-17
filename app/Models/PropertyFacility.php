<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyFacility extends Model
{
    use HasFactory;

    protected $table = 'property_facilities';
    protected $primaryKey = 'property_facility_id';

    protected $fillable = [
        'property_id',
        'icon',
        'icon_type',
        'title',
        'description',
        'status',
    ];

    /**
     * Get the property that owns the facility.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
