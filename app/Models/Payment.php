<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'booking_id',
        'transaction_id',
        'amount',
        'payment_date',
        'payment_method',
        'payment_status',
        'collected_by',
        'notes',
        'gateway_response',
        'status',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'payment_date'     => 'date',
        'gateway_response' => 'array',
    ];

    /* ──────────────────────────────────────────────
     |  Relationships
     |──────────────────────────────────────────── */

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by', 'id');
    }
}
