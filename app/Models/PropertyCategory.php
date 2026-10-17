<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyCategory extends Model
{
    protected $table = 'property_categories';
    protected $primaryKey = 'property_category_id';
    protected $fillable = ['title', 'slug', 'status'];

    public function properties()
    {
        return $this->hasMany(Property::class, 'property_category_id', 'property_category_id');
    }
}
