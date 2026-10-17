<?php

namespace App\Services;

use App\Models\Property;
use Carbon\Carbon;

/**
 * Customer-facing pricing service.
 *
 * Uses seasonal rates from the rates table when available.
 * For each night, finds the active rate whose date range covers that night.
 * Falls back to property base price if no matching rate exists.
 * Auto-applies discount & addon if the property has them.
 */
class ApiPricingService
{
    /**
     * Calculate price for the customer-facing API using seasonal rates.
     */
    public function calculate(
        int    $propertyId,
        string $checkinDate,
        string $checkoutDate,
        int    $noOfGuests,
    ): array {
        $property = Property::with(['currency', 'addon', 'discount', 'rates' => function ($q) {
            $q->where('status', 'active');
        }])->findOrFail($propertyId);

        // ── Date counting ──
        $checkin  = Carbon::parse($checkinDate)->startOfDay();
        $checkout = Carbon::parse($checkoutDate)->startOfDay();
        $totalNights = (int) $checkin->diffInDays($checkout);

        if ($totalNights <= 0) {
            return [
                'success' => false,
                'message' => 'Check-out date must be after check-in date.',
            ];
        }

        // ── Minimum-stay validation ──
        $minStay = $property->min_no_of_stay ?? 1;
        if ($totalNights < $minStay) {
            return [
                'success' => false,
                'message' => "Minimum stay is {$minStay} " . ($minStay > 1 ? 'nights' : 'night') . ". You selected {$totalNights}.",
            ];
        }

        // ── Currency ──
        $currency = '€';
        $basePricePerNight = (float) $property->price;

        // ── Calculate subtotal using seasonal rates ──
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
                // Extra person pricing when guests exceed the rate's included persons
                if ($noOfGuests > $matchedRate->persons && $matchedRate->extra_person_price_night > 0) {
                    $extraCharge = ($noOfGuests - $matchedRate->persons) * (float) $matchedRate->extra_person_price_night;
                }

                $nightTotal = $nightlyPrice + $extraCharge;
                $source = 'seasonal_rate';
            } elseif ($basePricePerNight > 0) {
                $nightlyPrice = $basePricePerNight;
                $extraCharge = 0;
                $nightTotal = $nightlyPrice;
                $source = 'base_price';
            } else {
                return [
                    'success' => false,
                    'message' => 'No active rate or base price available for ' . $currentDate->format('M d, Y') . '. Please try different dates.',
                ];
            }

            $subtotal += $nightTotal;

            $nightlyBreakdown[] = [
                'date'                => $currentDate->format('Y-m-d'),
                'base_rate'           => round($nightlyPrice, 2),
                'extra_person_charge' => round($extraCharge, 2),
                'total'               => round($nightTotal, 2),
                'is_weekend'          => $isWeekend,
                'source'              => $source,
            ];
        }

        // ── Auto-apply Discount ──
        $discountAmount  = 0;
        $appliedDiscount = null;
        $discountModel   = $property->discount;

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

        // ── Auto-apply Addon (service fee) ──
        $addonAmount  = 0;
        $appliedAddon = null;
        $addonModel   = $property->addon;

        if ($addonModel && $addonModel->status === 'active') {
            $addonAmount = (float) $addonModel->price;
            $appliedAddon = [
                'addon_id' => $addonModel->addon_id,
                'title'    => $addonModel->addon_type,
                'amount'   => round($addonAmount, 2),
            ];
        }

        // ── Grand total ──
        $grandTotal = max(0, $subtotal - $discountAmount) + $addonAmount;

        return [
            'success' => true,
            'data'    => [
                'nights'            => $totalNights,
                'guests'            => $noOfGuests,
                'subtotal'          => round($subtotal, 2),
                'discount_amount'   => round($discountAmount, 2),
                'addon_total'       => round($addonAmount, 2),
                'grand_total'       => round($grandTotal, 2),
                'currency'          => $currency,
                'applied_discount'  => $appliedDiscount,
                'applied_addon'     => $appliedAddon,
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
