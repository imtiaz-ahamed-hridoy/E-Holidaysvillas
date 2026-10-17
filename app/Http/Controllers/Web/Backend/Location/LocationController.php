<?php

namespace App\Http\Controllers\Web\Backend\Location;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    /**
     * Get children locations for a given parent.
     */
    public function getChildren(int $id): JsonResponse
    {
        $children = Location::where('location_parent_id', $id)->get(['location_id', 'title', 'lat', 'lng']);
        return response()->json($children);
    }
}
