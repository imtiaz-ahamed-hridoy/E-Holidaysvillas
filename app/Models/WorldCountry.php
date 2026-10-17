<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldCountry extends Model
{
    protected $table = 'world_countries';
    public $timestamps = false;

    protected $fillable = ['name', 'iso2'];

    public function states()
    {
        return $this->hasMany(WorldState::class, 'country_id');
    }

    public function cities()
    {
        return $this->hasMany(WorldCity::class, 'country_id');
    }
}
