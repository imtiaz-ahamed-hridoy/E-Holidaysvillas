<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyPolicy extends Model
{
    protected $table = 'property_policies';
    protected $primaryKey = 'property_policy_id';

    protected $fillable = [
        'property_id',
        'title',
        'icon',
        'icon_type',
        'description',
        'status',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
