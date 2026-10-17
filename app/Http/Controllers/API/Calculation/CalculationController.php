<?php

namespace App\Http\Controllers\API\Calculation;

use App\Http\Controllers\Controller;
use App\Services\ApiPricingService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CalculationController extends Controller
{
    use ApiResponse;

    /**
     * Customer-facing price estimate.
     * Uses property base price only (no rate-table lookup).
     * Auto-applies discount & addon. No coupon.
     */
    public function calculate(Request $request, ApiPricingService $pricingService)
    {
        $validated = $request->validate([
            'property_id'   => 'required|exists:properties,property_id',
            'checkin_date'  => 'required|date',
            'checkout_date' => 'required|date|after:checkin_date',
            'no_of_guests'  => 'required|integer|min:1',
        ]);

        $result = $pricingService->calculate(
            $validated['property_id'],
            $validated['checkin_date'],
            $validated['checkout_date'],
            $validated['no_of_guests'],
        );

        if (!$result['success']) {
            return $this->error($result['message']);
        }

        $data = $result['data'];

        return $this->success('Calculation performed successfully', [
            'nights'             => $data['nights'],
            'guests'             => $data['guests'],
            'subtotal'           => $data['subtotal'],
            'discount'           => $data['applied_discount'],
            'service_fee'        => $data['applied_addon'],
            'total_price'        => $data['grand_total'],
            'currency'           => $data['currency'],
            'nightly_breakdown'  => $data['nightly_breakdown'],
        ]);
    }
}
