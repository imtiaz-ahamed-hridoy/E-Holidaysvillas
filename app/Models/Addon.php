<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $table = 'addons';
    protected $primaryKey = 'addon_id';

    protected $fillable = [
        'addon_type',
        'price',
        'status',
    ];

    // Relationship removed as it's now belongsTo from Property
}
