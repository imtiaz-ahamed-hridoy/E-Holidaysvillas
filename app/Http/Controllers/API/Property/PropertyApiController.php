<?php

namespace App\Http\Controllers\API\Property;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Http\Requests\API\PropertySearchRequest;
use App\Services\PropertySearchService;
use App\Http\Resources\API\PropertyResource;
use App\Http\Resources\API\PropertyDetailResource;

class PropertyApiController extends Controller
{
    protected $searchService;

    public function __construct(PropertySearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Search properties based on location, dates, guests, property type, and price.
     */
    public function search(PropertySearchRequest $request)
    {
        $properties = $this->searchService->search($request->validated());

        return response()->json([
            'status' => 'success',
            'data'   => PropertyResource::collection($properties)->response()->getData(true)
        ], 200);
    }

    /**
     * List all active properties.
     */
    public function index()
    {
        $properties = Property::where('status', 'active')
            ->with([
                'location',
                'photos' => fn($q) => $q->where('status', 'active')->take(1),
                'propertyType'
            ])
            ->latest()
            ->paginate(10);

        if ($properties->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No properties found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => PropertyResource::collection($properties)->response()->getData(true)
        ], 200);
    }

    /**
     * Display the specified property with all relationships and availability.
     */
    public function show($id)
    {
        $property = Property::with([
            'location',
            'photos',
            'videos',
            'amenities',
            'rates',
            'facilities',
            'policies',
            'unitType',
            'currency',
            'addon',
            'discount'
        ])->find($id);

        if (!$property) {
            return response()->json([
                'status' => 'error',
                'message' => 'Property not found'
            ], 404);
        }

        $resource = new PropertyDetailResource($property);
        $data = $resource->toArray(request());

        // --- Similar Properties logic ---
        $similarProperties = [];
        if ($property->location) {
            $location = $property->location;
            $similarProperties = Property::where('status', 'active')
                ->where('property_id', '!=', $id)
                ->whereHas('location', function ($q) use ($location) {
                    $q->where(function ($sub) use ($location) {
                        if (!empty($location->city)) $sub->orWhere('city', $location->city);
                        if (!empty($location->area)) $sub->orWhere('area', $location->area);
                        if (!empty($location->region)) $sub->orWhere('region', $location->region);
                        if (!empty($location->country)) $sub->orWhere('country', $location->country);
                    });
                })
                ->with(['location', 'photos', 'rates'])
                ->take(4)
                ->get();
        }
        $data['similar'] = PropertyResource::collection($similarProperties);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200);
    }

    /**
     * Explore properties by category, sorted by high reviews.
     */
    public function exploreByCategory($property_category_id)
    {
        $properties = Property::select('property_id', 'property_category_id', 'title', 'description', 'stars', 'no_guests', 'no_bedrooms', 'no_bathrooms', 'status', 'created_at')
            ->with([
                'location:location_id,property_id,city,region,country,area',
                'photos' => function ($query) {
                    $query->select('property_photo_id', 'property_id', 'property_photo_file')
                          ->where('status', 'active')
                          ->limit(1);
                },
                'propertyType:property_type_id,title',
                'rates' => function ($query) {
                    $query->select('rate_id', 'property_id', 'nightly_rate', 'start_date', 'end_date', 'status')
                          ->where('status', 'active');
                }
            ])
            ->where('status', 'active')
            ->where('property_category_id', $property_category_id)
            ->withAvg('reviews', 'rating_stars')
            ->withCount('reviews')
            ->orderByDesc('reviews_avg_rating_stars')
            ->orderByDesc('reviews_count')
            ->take(10)
            ->get();

        if ($properties->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No properties found for this category'
            ], 404);
        }

        $formattedItems = $properties->map(function ($prop) {
            $item = $prop->toArray();

            if (!empty($item['description'])) {
                $stripped = strip_tags($item['description']);
                $item['description'] = mb_strimwidth($stripped, 0, 150, '...');
            }

            if (!empty($item['photos'])) {
                foreach ($item['photos'] as &$photo) {
                    if (!empty($photo['property_photo_file'])) {
                        $photo['property_photo_file'] = \App\Helpers\Helper::imageUrl($photo['property_photo_file']);
                    }
                }
            }

            $item['price'] = $prop->price;
            unset($item['rates']);
            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'current_page' => 1,
                'data' => $formattedItems,
                'first_page_url' => request()->url() . '?page=1',
                'from' => 1,
                'last_page' => 1,
                'last_page_url' => request()->url() . '?page=1',
                'links' => [],
                'next_page_url' => null,
                'path' => request()->url(),
                'per_page' => 10,
                'prev_page_url' => null,
                'to' => count($formattedItems),
                'total' => count($formattedItems),
            ]
        ], 200);
    }

    /**
     * Explore popular properties in a specific location.
     */
    public function explorePopularNearby(Request $request)
    {
        $query = Property::select('property_id', 'property_category_id', 'title', 'description', 'stars', 'no_guests', 'no_bedrooms', 'no_bathrooms', 'status', 'created_at')
            ->with([
                'location:location_id,property_id,city,region,country,area',
                'photos' => function ($query) {
                    $query->select('property_photo_id', 'property_id', 'property_photo_file')
                          ->where('status', 'active')
                          ->limit(1);
                },
                'propertyType:property_type_id,title',
                'rates' => function ($query) {
                    $query->select('rate_id', 'property_id', 'nightly_rate', 'start_date', 'end_date', 'status')
                          ->where('status', 'active');
                }
            ])
            ->where('status', 'active');

        // Filter by Location parameters (POST body)
        if ($request->hasAny(['country', 'city', 'area'])) {
            $query->whereHas('location', function ($q) use ($request) {
                if ($request->has('country')) {
                    $q->where('country', 'like', "%{$request->country}%");
                }
                if ($request->has('city')) {
                    $q->where('city', 'like', "%{$request->city}%");
                }
                if ($request->has('area')) {
                    $q->where('area', 'like', "%{$request->area}%");
                }
            });
        }

        $properties = $query->withAvg('reviews', 'rating_stars')
            ->withCount('reviews')
            ->orderByDesc('reviews_avg_rating_stars')
            ->orderByDesc('reviews_count')
            ->take(10)
            ->get();

        if ($properties->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No popular properties found in that location'
            ], 404);
        }

        $formattedItems = $properties->map(function ($prop) {
            $item = $prop->toArray();

            if (!empty($item['description'])) {
                $stripped = strip_tags($item['description']);
                $item['description'] = mb_strimwidth($stripped, 0, 150, '...');
            }

            if (!empty($item['photos'])) {
                foreach ($item['photos'] as &$photo) {
                    if (!empty($photo['property_photo_file'])) {
                        $photo['property_photo_file'] = \App\Helpers\Helper::imageUrl($photo['property_photo_file']);
                    }
                }
            }

            $item['price'] = $prop->price;
            unset($item['rates']);
            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'current_page' => 1,
                'data' => $formattedItems,
                'first_page_url' => request()->url() . '?page=1',
                'from' => 1,
                'last_page' => 1,
                'last_page_url' => request()->url() . '?page=1',
                'links' => [],
                'next_page_url' => null,
                'path' => request()->url(),
                'per_page' => 10,
                'prev_page_url' => null,
                'to' => count($formattedItems),
                'total' => count($formattedItems),
            ]
        ], 200);
    }

}
