<?php

namespace App\Http\Controllers\API\Rate;

use App\Http\Controllers\Controller;
use App\Models\Rate;
use Illuminate\Http\JsonResponse;

class RateController extends Controller
{
    /**
     * Display a property of rates.
     */
    public function index(): JsonResponse
    {
        try {
            $rates = Rate::with(['property', 'unitType'])->where('status', 'active')->paginate(20);

            if ($rates->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No rates found.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rates retrieved successfully.',
                'data' => $rates
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve rates.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display rates for a specific property.
     */
    public function propertyRates($property_id): JsonResponse
    {
        try {
            $rates = Rate::with('unitType')
                ->where('property_id', $property_id)
                ->where('status', 'active')
                ->orderBy('start_date', 'asc')
                ->paginate(20);

            if ($rates->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No rates found for the specified property.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Property rates retrieved successfully.',
                'data' => $rates
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve property rates.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
