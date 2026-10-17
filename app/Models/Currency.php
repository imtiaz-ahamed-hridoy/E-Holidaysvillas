<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currencies';
    protected $primaryKey = 'currency_id';

    protected $fillable = [
        'title',
        'currency_code',
        'currency_rate',
        'status',
        'is_default',
    ];
}
