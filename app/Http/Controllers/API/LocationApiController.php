<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WorldCountry;
use App\Models\WorldState;
use App\Models\WorldCity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationApiController extends Controller
{
    /**
     * Get all countries.
     */
    public function countries(): JsonResponse
    {
        $countries = WorldCountry::orderBy('name')->get(['id', 'name', 'iso2']);
        return response()->json($countries);
    }

    /**
     * Get states/regions for a country.
     */
    public function states(int $countryId): JsonResponse
    {
        $states = WorldState::where('country_id', $countryId)
            ->orderBy('name')
            ->get(['id', 'country_id', 'name']);
        return response()->json($states);
    }

    /**
     * Get cities for a state.
     */
    public function cities(int $stateId): JsonResponse
    {
        $cities = WorldCity::where('state_id', $stateId)
            ->orderBy('name')
            ->get(['id', 'state_id', 'country_id', 'name']);
        return response()->json($cities);
    }

    /**
     * Find a country by name (for map reverse-geocode matching).
     */
    public function findCountryByName(Request $request): JsonResponse
    {
        $name = $request->query('name');
        // Try exact match first, then bidirectional LIKE
        $country = WorldCountry::where('name', $name)
            ->orWhere('name', 'LIKE', "%{$name}%")
            ->orWhereRaw("? LIKE CONCAT('%', name, '%')", [$name])
            ->first(['id', 'name', 'iso2']);
        return response()->json($country);
    }

    /**
     * Find a state by name within a country.
     */
    public function findStateByName(Request $request): JsonResponse
    {
        $name = $request->query('name');
        $countryId = $request->query('country_id');
        $query = WorldState::query();
        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        $state = (clone $query)->where('name', $name)->first(['id', 'country_id', 'name']);
        if (!$state) {
            $state = (clone $query)->where('name', 'LIKE', "%{$name}%")->first(['id', 'country_id', 'name']);
        }
        if (!$state) {
            $state = (clone $query)->whereRaw("? LIKE CONCAT('%', name, '%')", [$name])->first(['id', 'country_id', 'name']);
        }

        return response()->json($state);
    }

    /**
     * Find a city by name within a state.
     */
    public function findCityByName(Request $request): JsonResponse
    {
        $name = $request->query('name');
        $stateId = $request->query('state_id');
        $query = WorldCity::query();
        if ($stateId) {
            $query->where('state_id', $stateId);
        }

        $city = (clone $query)->where('name', $name)->first(['id', 'state_id', 'country_id', 'name']);
        if (!$city) {
            $city = (clone $query)->where('name', 'LIKE', "%{$name}%")->first(['id', 'state_id', 'country_id', 'name']);
        }
        if (!$city) {
            $city = (clone $query)->whereRaw("? LIKE CONCAT('%', name, '%')", [$name])->first(['id', 'state_id', 'country_id', 'name']);
        }

        return response()->json($city);
    }
}
