<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Discount;
use App\Models\Addon;
use Carbon\Carbon;

class PricingService
{
    /**
     * Calculate total price for a stay.
     * 
     * @param int $propertyId
     * @param string $checkinDate
     * @param string $checkoutDate
     * @param int $noOfGuests
     * @param int|null $discountId
     * @param int|null $addonId
     * @return array
     */
    public function calculate(
        int $propertyId,
        string $checkinDate,
        string $checkoutDate,
        int $noOfGuests,
        ?int $discountId = null,
        ?int $addonId = null,
        ?int $couponId = null,
    ): array {
        $property = Property::with(['currency', 'addon', 'discount', 'rates' => function ($q) {
            $q->where('status', 'active');
        }])->findOrFail($propertyId);

        $checkin = Carbon::parse($checkinDate)->startOfDay();
        $checkout = Carbon::parse($checkoutDate)->startOfDay();
        $totalNights = (int) $checkin->diffInDays($checkout);

        $minStay = $property->min_no_of_stay ?? 1;

        if ($totalNights < $minStay) {
            return [
                'success' => false,
                'message' => "Minimum stay is $minStay " . ($minStay > 1 ? 'nights' : 'night')
            ];
        }

        $subtotal = 0;
        $nightlyBreakdown = [];

        for ($i = 0; $i < $totalNights; $i++) {
            $currentDate = $checkin->copy()->addDays($i);
            $isWeekend = $currentDate->isWeekend();

            // Find specific rate for this date
            $rate = $property->rates->first(function ($r) use ($currentDate) {
                return $currentDate->between($r->start_date, $r->end_date);
            });

            if (!$rate) {
                return [
                    'success' => false,
                    'message' => 'No active rate available for ' . $currentDate->format('M d, Y') . '. Please adjust dates.'
                ];
            }

            if ($isWeekend && $rate->nightly_rate_weekend > 0) {
                $nightlyPrice = (float) $rate->nightly_rate_weekend;
            } else {
                $nightlyPrice = (float) $rate->nightly_rate;
            }

            $extraCharge = 0;
            // Extra person pricing
            if ($noOfGuests > $rate->persons && $rate->extra_person_price_night > 0) {
                $extraCharge = ($noOfGuests - $rate->persons) * (float) $rate->extra_person_price_night;
            }

            $currentNightTotal = $nightlyPrice + $extraCharge;
            $subtotal += $currentNightTotal;

            $nightlyBreakdown[] = [
                'date' => $currentDate->format('Y-m-d'),
                'base_rate' => round($nightlyPrice, 2),
                'extra_person_charge' => round($extraCharge, 2),
                'total' => round($currentNightTotal, 2),
                'is_weekend' => $isWeekend
            ];
        }

        // Apply Discount
        $discountAmount = 0;
        $appliedDiscount = null;
        $discountModel = $this->resolveModel(Discount::class, $discountId, $property->discount);

        if ($discountModel && $discountModel->status === 'active') {
            $today = now();
            // Check if guests meet requirement (or if no requirement set)
            $personRequirementMet = is_null($discountModel->persons) || ($noOfGuests >= $discountModel->persons);

            if ($today->between($discountModel->start_date, $discountModel->end_date) && $personRequirementMet) {
                $val = $discountModel->discount_percentage;
                $discountAmount = $this->parseValue($subtotal, $val);

                $appliedDiscount = [
                    'discount_id' => $discountModel->discount_id,
                    'title' => $discountModel->discount_type,
                    'value' => $val,
                    'amount' => round($discountAmount, 2),
                ];
            }
        }

        // Apply Addon
        $addonAmount = 0;
        $appliedAddon = null;
        $addonModel = $this->resolveModel(Addon::class, $addonId, $property->addon);

        if ($addonModel && $addonModel->status === 'active') {
            $addonAmount = (float) $addonModel->price;
            $appliedAddon = [
                'addon_id' => $addonModel->addon_id,
                'title' => $addonModel->addon_type,
                'amount' => round($addonAmount, 2)
            ];
        }

        $couponAmount = 0;
        $appliedCoupon = null;

        $grandTotal = max(0, ($subtotal - $discountAmount - $couponAmount)) + $addonAmount;

        return [
            'success' => true,
            'data' => [
                'nights' => $totalNights,
                'guests' => $noOfGuests,
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($discountAmount, 2),
                'addon_total' => round($addonAmount, 2),
                'coupon_discount' => round($couponAmount, 2),
                'grand_total' => round($grandTotal, 2),
                'currency' => '€',
                'applied_discount' => $appliedDiscount,
                'applied_addon' => $appliedAddon,
                'applied_coupon' => $appliedCoupon,
                'nightly_breakdown' => $nightlyBreakdown
            ]
        ];
    }

    /**
     * Resolve which model to use (passed ID vs property default).
     */
    private function resolveModel($class, $id, $default)
    {
        if ($id) {
            return $class::find($id);
        }
        return $default;
    }

    /**
     * Parse percentage or fixed value.
     */
    private function parseValue($base, $val)
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
