<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'booking_id';

    protected $fillable = [
        'booking_code',
        'booking_source',
        'property_id',
        'user_id',
        'created_by',
        'guest_name',
        'guest_email',
        'guest_phone',
        'guest_country',
        'guest_address',
        'guest_zip',
        'check_in',
        'check_out',
        'number_adults',
        'number_children',
        'number_nights',
        'admin_base_price_per_night',
        'rental_price',
        'addon_total',
        'discount_amount',
        'coupon_discount',
        'subtotal',
        'grand_total',
        'addon_id',
        'discount_id',
        'payment_method',
        'payment_status',
        'booking_status',
        'confirmation_email_sent',
        'confirmation_email_sent_at',
        'guest_message',
        'internal_notes',
        'status',
    ];

    protected $casts = [
        'check_in'                   => 'date',
        'check_out'                  => 'date',
        'admin_base_price_per_night' => 'decimal:2',
        'rental_price'               => 'decimal:2',
        'addon_total'                => 'decimal:2',
        'discount_amount'            => 'decimal:2',
        'coupon_discount'            => 'decimal:2',
        'subtotal'                   => 'decimal:2',
        'grand_total'                => 'decimal:2',
        'confirmation_email_sent'    => 'boolean',
        'confirmation_email_sent_at' => 'datetime',
    ];

    /* ──────────────────────────────────────────────
     |  Auto-generate booking code: BK-YYYYMMDD-serial
     |──────────────────────────────────────────── */
    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = self::generateBookingCode();
            }
        });
    }

    /**
     * Generate a unique booking code in the format: BK-YYYYMMDD-XXXX
     */
    public static function generateBookingCode(): string
    {
        $datePrefix = 'BK-' . now()->format('Ymd') . '-';

        // Find the highest serial for today
        $latestCode = self::where('booking_code', 'like', $datePrefix . '%')
            ->orderByDesc('booking_code')
            ->value('booking_code');

        if ($latestCode) {
            $lastSerial = (int) substr($latestCode, strrpos($latestCode, '-') + 1);
            $nextSerial = $lastSerial + 1;
        } else {
            $nextSerial = 1;
        }

        return $datePrefix . str_pad($nextSerial, 4, '0', STR_PAD_LEFT);
    }

    /* ──────────────────────────────────────────────
     |  Relationships
     |──────────────────────────────────────────── */

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class, 'addon_id', 'addon_id');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'discount_id', 'discount_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id', 'booking_id');
    }

    /* ──────────────────────────────────────────────
     |  Helpers
     |──────────────────────────────────────────── */

    /**
     * Get total amount paid so far.
     */
    public function getTotalPaidAttribute(): float
    {
        if ($this->relationLoaded('payments')) {
            $sum = (float) $this->payments->where('payment_status', 'completed')->sum('amount');
            return $sum > 0 ? $sum : ($this->payment_status === 'paid' ? (float) $this->grand_total : 0.0);
        }
        $sum = (float) $this->payments()->where('payment_status', 'completed')->sum('amount');
        return $sum > 0 ? $sum : ($this->payment_status === 'paid' ? (float) $this->grand_total : 0.0);
    }

    /**
     * Get remaining balance.
     */
    public function getBalanceDueAttribute(): float
    {
        return (float) $this->grand_total - $this->total_paid;
    }

    /**
     * Check if the booking is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->balance_due <= 0;
    }
}
