<?php
namespace App\Http\Controllers\API\Property;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\JsonResponse;

class PropertyTypeApiController extends Controller
{
    /**
     * List all active property types.
     */
    public function index(): JsonResponse
    {
        $types = PropertyType::where('status', 'active')
            ->select('property_type_id', 'title', 'slug')
            ->orderBy('title')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $types
        ], 200);
    }
}
