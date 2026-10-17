<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $primaryKey = 'faq_id';

    protected $table = "faqs";

    protected $fillable = [
        "question",
        "answer",
        "status",
    ];

    protected $casts = [
        'created_at' => 'date: d-m-Y',
        'updated_at' => 'date: d-m-Y',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
