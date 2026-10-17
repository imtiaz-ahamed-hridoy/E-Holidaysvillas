<?php

namespace App\Http\Controllers\API\PropertyCategory;

use App\Http\Controllers\Controller;
use App\Models\PropertyCategory;

class PropertyCategoryApiController extends Controller
{
    /**
     * List all active property categories.
     */
    public function index()
    {
        $categories = PropertyCategory::where('status', 'active')
            ->select('property_category_id', 'title', 'slug')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No property categories found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ], 200);
    }

    /**
     * Display a specific category with its properties.
     */
    public function show($id)
    {
        // Try to find by ID or Slug
        $category = PropertyCategory::where('status', 'active')
            ->where(function ($query) use ($id) {
                $query->where('property_category_id', $id)
                    ->orWhere('slug', $id);
            })
            ->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Property category not found'
            ], 404);
        }

        // Load only 10 active properties — flat array, NO pagination metadata.
        // The frontend was reading 'last_page' and looping through all 130 pages (1,297 villas).
        // Using take(10)->get() returns a clean array so the frontend gets everything in ONE request.
        $properties = $category->properties()
            ->select('property_id', 'property_category_id', 'title', 'description', 'stars', 'no_guests', 'no_bedrooms', 'no_bathrooms', 'status', 'created_at')
            ->with([
                'location:location_id,property_id,city,region,country,area',
                'photos' => function ($query) {
                    $query->select('property_photo_id', 'property_id', 'property_photo_file')
                          ->where('status', 'active')
                          ->limit(1);
                },
                'propertyType:property_type_id,title',
                // Rates loaded ONLY for the price attribute computation — stripped from response below
                'rates' => function ($query) {
                    $query->select('rate_id', 'property_id', 'nightly_rate', 'start_date', 'end_date', 'status')
                          ->where('status', 'active');
                }
            ])
            ->where('status', 'active')
            ->latest()
            ->take(10)
            ->get();

        // Format: truncate description, fix asset URLs, remove raw rates (frontend only needs computed 'price')
        $formattedItems = $properties->map(function ($prop) {
            $item = $prop->toArray();

            // Truncate description for grid view
            if (!empty($item['description'])) {
                $stripped = strip_tags($item['description']);
                $item['description'] = mb_strimwidth($stripped, 0, 150, '...');
            }

            // Fix photo asset URLs
            if (!empty($item['photos'])) {
                foreach ($item['photos'] as &$photo) {
                    if (!empty($photo['property_photo_file'])) {
                        $photo['property_photo_file'] = \App\Helpers\Helper::imageUrl($photo['property_photo_file']);
                    }
                }
            }

            // Ensure price is present (should be from the append)
            $item['price'] = $prop->price;

            // Remove raw rates to minimize payload
            unset($item['rates']);

            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'category' => [
                    'property_category_id' => $category->property_category_id,
                    'title' => $category->title,
                    'slug' => $category->slug,
                ],
                'properties' => [
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
            ]
        ], 200);
    }
}
