<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSupport extends Model
{
    protected $table = 'contact_supports';

    protected $fillable = [
        'user_id',
        'name',
        'subject',
        'email',
        'message',
        'reply',
        'is_replied',
        'replied_at',
        'priority',
        'status'
    ];

    protected $casts = [
        'is_replied' => 'boolean',
        'replied_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
