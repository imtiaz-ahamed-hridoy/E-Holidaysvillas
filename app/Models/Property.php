<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $table = 'properties';
    protected $primaryKey = 'property_id';

    protected $fillable = [
        'user_id',
        'property_status',
        'property_main_type',
        'property_type_id',
        'unit_type_id',
        'property_category_id',
        'title',
        'description',
        'currency_id',
        'stars',
        'available_from',
        'no_bedrooms',
        'no_bathrooms',
        'no_floors',
        'no_guests',
        'min_no_of_stay',
        'parking',
        'car_essential',
        'total_square_feet',
        'allow_bookings',
        'discount_id',
        'addon_id',
        'property_meta_title',
        'property_meta_description',
        'property_meta_keywords',
        'status',
    ];

    protected $appends = ['price'];

    /**
     * Get the dynamically calculated price based on rates.
     */
    public function getPriceAttribute()
    {
        try {
            $date = request('check_in') ? \Carbon\Carbon::parse(request('check_in'))->startOfDay() : now()->startOfDay();
        } catch (\Exception $e) {
            $date = now()->startOfDay();
        }

        $rates = $this->relationLoaded('rates') ? $this->rates : $this->rates()->get();

        $rate = $rates->where('status', 'active')->first(function ($rate) use ($date) {
            return $rate->start_date && $rate->end_date &&
                   $rate->start_date->startOfDay()->lte($date) &&
                   $rate->end_date->startOfDay()->gte($date);
        });

        if ($rate) {
            return number_format((float) $rate->nightly_rate, 2, '.', '');
        }

        $fallbackRate = $rates->where('status', 'active')->first();
        if ($fallbackRate) {
            return number_format((float) $fallbackRate->nightly_rate, 2, '.', '');
        }

        return '0.00';
    }

    public function amenities()
    {
        return $this->hasMany(Amenity::class, 'property_id', 'property_id');
    }

    public function rates()
    {
        return $this->hasMany(Rate::class, 'property_id', 'property_id');
    }

    public function addon()
    {
        return $this->belongsTo(Addon::class, 'addon_id', 'addon_id');
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id', 'discount_id');
    }

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class, 'property_type_id', 'property_type_id');
    }

    public function unitType()
    {
        return $this->belongsTo(UnitType::class, 'unit_type_id', 'unit_type_id');
    }

    public function propertyCategory()
    {
        return $this->belongsTo(PropertyCategory::class, 'property_category_id', 'property_category_id');
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'property_id', 'property_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'currency_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'property_id', 'property_id');
    }

    public function facilities()
    {
        return $this->hasMany(PropertyFacility::class, 'property_id', 'property_id');
    }

    public function photos()
    {
        return $this->hasMany(PropertyPhoto::class, 'property_id', 'property_id');
    }

    public function videos()
    {
        return $this->hasMany(PropertyVideo::class, 'property_id', 'property_id');
    }

    public function policies()
    {
        return $this->hasMany(PropertyPolicy::class, 'property_id', 'property_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'property_id', 'property_id');
    }
}
