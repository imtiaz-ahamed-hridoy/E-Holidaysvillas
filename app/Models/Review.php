<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'review_id';

    public $timestamps = true;

    protected $fillable = [
        'property_id',
        'avatar',
        'name',
        'details',
        'rating_stars',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
        'rating_stars' => 'integer',
        'property_id' => 'integer',
    ];

    /**
     * Get the property that owns the review.
     */
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
