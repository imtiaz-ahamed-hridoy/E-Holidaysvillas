<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    protected $table = 'payment_requests';
    protected $primaryKey = 'payment_request_id';

    protected $fillable = [
        'user_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'credits',
        'credits_request',
        'service',
        'payment_system',
        'payment_status',
        'request_type',
        'locked',
        'hit_by_ipn',
        'status',
    ];
}
