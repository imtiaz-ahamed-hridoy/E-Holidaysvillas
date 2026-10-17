<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldCity extends Model
{
    protected $table = 'world_cities';
    public $timestamps = false;

    protected $fillable = ['state_id', 'country_id', 'name'];

    public function state()
    {
        return $this->belongsTo(WorldState::class, 'state_id');
    }

    public function country()
    {
        return $this->belongsTo(WorldCountry::class, 'country_id');
    }
}
