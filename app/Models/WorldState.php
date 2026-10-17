<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldState extends Model
{
    protected $table = 'world_states';
    public $timestamps = false;

    protected $fillable = ['country_id', 'name'];

    public function country()
    {
        return $this->belongsTo(WorldCountry::class, 'country_id');
    }

    public function cities()
    {
        return $this->hasMany(WorldCity::class, 'state_id');
    }
}
