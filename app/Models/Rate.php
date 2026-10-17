<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    protected $table = 'rates';
    protected $primaryKey = 'rate_id';

    protected $fillable = [
        'property_id',
        'unit_type_id',
        'start_date',
        'end_date',
        'nightly_rate',
        'nightly_rate_weekend',
        'min_stay',
        'changeover_day',
        'persons',
        'extra_person_price_night',
        'status',
        'is_special_offer',
        'is_rate_overwrite',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'nightly_rate' => 'decimal:2',
        'nightly_rate_weekend' => 'decimal:2',
        'extra_person_price_night' => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }

    public function unitType()
    {
        return $this->belongsTo(UnitType::class, 'unit_type_id', 'unit_type_id');
    }
}
