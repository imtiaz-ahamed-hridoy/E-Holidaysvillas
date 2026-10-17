<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $table = 'inquiries';
    protected $primaryKey = 'inquiry_id';

    protected $fillable = [
        'user_id',
        'property_id',
        'name',
        'email',
        'phone',
        'checkin_date',
        'checkout_date',
        'no_of_guests',
        'message',
        'reply',
        'is_replied',
        'replied_at',
        'status',
    ];

    protected $attributes = [
        'is_replied' => 'no',
        'status' => 'pending',
    ];

    /**
     * Get the property associated with the inquiry.
     */
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }
}
