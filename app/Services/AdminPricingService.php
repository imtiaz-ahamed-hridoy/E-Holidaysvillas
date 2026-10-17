<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Discount;
use App\Models\Addon;
use App\Models\Rate;
use Carbon\Carbon;

/**
 * Admin-dashboard pricing service.
 *
 * Supports two pricing modes:
 *   • base  — property.price × nights
 *   • rate  — nightly rates from rates table, or custom rate entered by admin
 *
 * Discount & Addon auto-apply from the property defaults.
 * Coupon is not used in manual admin booking flow.
 */
class AdminPricingService
{
    /**
     * Calculate pricing for admin dashboard.
     *
     * @param array $params  Keys:
     *   - property_id        (int, required)
     *   - checkin_date        (string, required)
     *   - checkout_date       (string, required)
     *   - no_of_guests        (int, required)
     *   - pricing_mode        (string: 'base'|'rate', required)
     *   - rate_id             (int|null)            — when pricing_mode=rate and using an existing rate
     *   - custom_rate_mode    (string|null: 'flat'|'per_night') — when pricing_mode=rate and NOT using an existing rate
     *   - custom_flat_rate    (float|null)           — flat custom nightly rate
     *   - custom_nightly_rates(array|null)           — ['2026-03-02' => 4000, '2026-03-03' => 3500, ...]
     *   - discount_id         (int|null)             — override discount (null = use property default)
     *   - addon_id            (int|null)             — override addon   (null = use property default)
     */
    public function calculate(array $params): array
    {
        $propertyId      = $params['property_id'];
        $checkinDate     = $params['checkin_date'];
        $checkoutDate    = $params['checkout_date'];
        $noOfGuests      = (int) ($params['no_of_guests'] ?? 1);
        $pricingMode     = $params['pricing_mode'] ?? 'base';
        $rateId          = $params['rate_id'] ?? null;
        $customRateMode  = $params['custom_rate_mode'] ?? null;
        $customFlatRate  = isset($params['custom_flat_rate']) ? (float) $params['custom_flat_rate'] : null;
        $customNightlyRates = $params['custom_nightly_rates'] ?? [];
        $overrideDiscountId = $params['discount_id'] ?? null;
        $overrideAddonId    = $params['addon_id'] ?? null;

        // ── Load property with relationships ──
        $property = Property::with(['currency', 'addon', 'discount', 'rates' => function ($q) {
            $q->where('status', 'active');
        }])->findOrFail($propertyId);

        $currency = '€';

        // ── Date counting (startOfDay for both to get exact night count) ──
        $checkin  = Carbon::parse($checkinDate)->startOfDay();
        $checkout = Carbon::parse($checkoutDate)->startOfDay();
        $totalNights = (int) $checkin->diffInDays($checkout);

        if ($totalNights <= 0) {
            return [
                'success' => false,
                'message' => 'Check-out date must be after check-in date.',
            ];
        }

        // ── Minimum-stay validation (property level) ──
        $propertyMinStay = $property->min_no_of_stay ?? 1;
        if ($totalNights < $propertyMinStay) {
            return [
                'success' => false,
                'message' => "Minimum stay for this property is {$propertyMinStay} " .
                    ($propertyMinStay > 1 ? 'nights' : 'night') . ". You selected {$totalNights}.",
            ];
        }

        // ── Rate-level min_stay validation (only when an existing rate is selected) ──
        $selectedRate = null;
        if ($pricingMode === 'rate' && $rateId) {
            $selectedRate = Rate::find($rateId);
            if (!$selectedRate) {
                return ['success' => false, 'message' => 'Selected rate not found.'];
            }
            if ($selectedRate->min_stay && $totalNights < $selectedRate->min_stay) {
                return [
                    'success' => false,
                    'message' => "Selected rate requires a minimum stay of {$selectedRate->min_stay} " .
                        ($selectedRate->min_stay > 1 ? 'nights' : 'night') . ". You selected {$totalNights}.",
                ];
            }
        }

        // ═══════════════════════════════════════════
        //  CALCULATE SUBTOTAL
        // ═══════════════════════════════════════════
        $subtotal = 0;
        $nightlyBreakdown = [];

        for ($i = 0; $i < $totalNights; $i++) {
            $currentDate = $checkin->copy()->addDays($i);
            $isWeekend = $currentDate->isWeekend();

            // Find the active rate whose date range covers this night
            $matchedRate = $property->rates->first(function ($rate) use ($currentDate) {
                return $currentDate->between($rate->start_date, $rate->end_date);
            });

            if ($matchedRate) {
                // Use seasonal rate: weekend rate if applicable, otherwise nightly rate
                if ($isWeekend && $matchedRate->nightly_rate_weekend > 0) {
                    $nightlyPrice = (float) $matchedRate->nightly_rate_weekend;
                } else {
                    $nightlyPrice = (float) $matchedRate->nightly_rate;
                }

                $extraCharge = 0;
                if ($noOfGuests > $matchedRate->persons && $matchedRate->extra_person_price_night > 0) {
                    $extraCharge = ($noOfGuests - $matchedRate->persons) * (float) $matchedRate->extra_person_price_night;
                }

                $nightTotal = $nightlyPrice + $extraCharge;
                $source = 'rate';
            } elseif ((float) $property->price > 0) {
                $nightlyPrice = (float) $property->price;
                $extraCharge = 0;
                $nightTotal = $nightlyPrice;
                $source = 'base_price';
            } else {
                return [
                    'success' => false,
                    'message' => 'No active rate or base price available for ' . $currentDate->format('M d, Y') . '. Please adjust dates.'
                ];
            }

            $subtotal += $nightTotal;

            $nightlyBreakdown[] = [
                'date'                 => $currentDate->format('Y-m-d'),
                'base_rate'            => round($nightlyPrice, 2),
                'extra_person_charge'  => round($extraCharge, 2),
                'total'                => round($nightTotal, 2),
                'is_weekend'           => $isWeekend,
                'source'               => $source,
            ];
        }

        // ═══════════════════════════════════════════
        //  AUTO-APPLY DISCOUNT
        // ═══════════════════════════════════════════
        $discountAmount  = 0;
        $appliedDiscount = null;

        $discountModel = $overrideDiscountId
            ? Discount::find($overrideDiscountId)
            : $property->discount;

        if ($discountModel && $discountModel->status === 'active') {
            $today = now();
            $personReqMet = is_null($discountModel->persons) || ($noOfGuests >= $discountModel->persons);

            if ($today->between($discountModel->start_date, $discountModel->end_date) && $personReqMet) {
                $val = $discountModel->discount_percentage;
                $discountAmount = $this->parseValue($subtotal, $val);

                $appliedDiscount = [
                    'discount_id' => $discountModel->discount_id,
                    'title'       => $discountModel->discount_type,
                    'value'       => $val,
                    'amount'      => round($discountAmount, 2),
                ];
            }
        }

        // ═══════════════════════════════════════════
        //  AUTO-APPLY ADDON
        // ═══════════════════════════════════════════
        $addonAmount  = 0;
        $appliedAddon = null;

        $addonModel = $overrideAddonId
            ? Addon::find($overrideAddonId)
            : $property->addon;

        if ($addonModel && $addonModel->status === 'active') {
            $addonAmount = (float) $addonModel->price;
            $appliedAddon = [
                'addon_id' => $addonModel->addon_id,
                'title'    => $addonModel->addon_type,
                'amount'   => round($addonAmount, 2),
            ];
        }

        $couponAmount  = 0;
        $appliedCoupon = null;

        // ═══════════════════════════════════════════
        //  GRAND TOTAL
        // ═══════════════════════════════════════════
        $grandTotal = max(0, ($subtotal - $discountAmount - $couponAmount)) + $addonAmount;

        return [
            'success' => true,
            'data'    => [
                'nights'            => $totalNights,
                'guests'            => $noOfGuests,
                'pricing_mode'      => $pricingMode,
                'rate_id'           => $selectedRate?->rate_id,
                'subtotal'          => round($subtotal, 2),
                'discount_amount'   => round($discountAmount, 2),
                'addon_total'       => round($addonAmount, 2),
                'coupon_discount'   => round($couponAmount, 2),
                'grand_total'       => round($grandTotal, 2),
                'currency'          => $currency,
                'applied_discount'  => $appliedDiscount,
                'applied_addon'     => $appliedAddon,
                'applied_coupon'    => $appliedCoupon,
                'nightly_breakdown' => $nightlyBreakdown,
            ],
        ];
    }

    /**
     * Parse percentage ("10%") or fixed value ("50").
     */
    private function parseValue(float $base, ?string $val): float
    {
        if (empty($val)) {
            return 0.0;
        }
        if (str_ends_with($val, '%')) {
            $percentage = (float) rtrim($val, '%');
            return ($base * $percentage) / 100;
        }
        return (float) $val;
    }
}
