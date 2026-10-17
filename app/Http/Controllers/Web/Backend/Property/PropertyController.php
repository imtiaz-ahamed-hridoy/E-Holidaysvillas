<?php

namespace App\Http\Controllers\Web\Backend\Property;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\UnitType;
use App\Models\Currency;
use App\Models\Amenity;
use App\Models\User;
use App\Models\Addon;
use App\Models\Discount;
use App\Models\PropertyCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class PropertyController extends Controller
{
    /**
     * Display a property of properties.
     */
    public function index(Request $request): JsonResponse | View
    {
        if ($request->ajax()) {
            $data = Property::with(['propertyType', 'unitType'])->latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($data) {
                    return $data->title;
                })
                ->addColumn('property_type', function ($data) {
                    return $data->propertyType ? $data->propertyType->title : 'N/A';
                })
                ->addColumn('unit_type', function ($data) {
                    return $data->unitType ? $data->unitType->title : 'N/A';
                })
                ->addColumn('status', function ($data) {
                    $badges = [
                        'inactive' => 'bg-secondary',
                        'active' => 'bg-success',
                        'pending' => 'bg-warning',
                        'sold' => 'bg-info',
                        'withdrawn' => 'bg-danger',
                    ];
                    $badgeClass = $badges[$data->property_status] ?? 'bg-primary';
                    return '<span class="badge ' . $badgeClass . '" style="cursor: pointer;" onclick="toggleStatus(' . $data->property_id . ')">' . ucfirst($data->property_status) . '</span>';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <a href="' . route('property.show', $data->property_id) . '" class="btn btn-info fs-14 text-white" title="View">
                                    <i class="fe fe-eye"></i>
                                </a>
                                <a href="' . route('property.edit', $data->property_id) . '" class="btn btn-primary fs-14 text-white edit-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                                <button type="button" onclick="deleteData(' . $data->property_id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make();
        }
        return view('backend.layouts.property.index');
    }

    /**
     * Show the form for creating a new property.
     */
    public function create(): View
    {
        $users = User::all();
        $propertyTypes = PropertyType::where('status', 'active')->get();
        $unitTypes = UnitType::where('status', 'active')->get();
        $currencies = Currency::where('status', 'active')->get();
        $amenities = Amenity::all();
        $propertyCategories = PropertyCategory::where('status', 'active')->get();
        $addons = Addon::where('status', 'active')->get();
        $discounts = Discount::where('status', 'active')->get();

        return view('backend.layouts.property.create', compact(
            'users',
            'propertyTypes',
            'unitTypes',
            'currencies',
            'amenities',
            'propertyCategories',
            'addons',
            'discounts'
        ));
    }

    /**
     * Store a newly created property in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // For brevity in scaffolding, using basic validator. In production, use a FormRequest.
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'property_status' => 'nullable|in:inactive,active,pending,sold,withdrawn,others',
            'property_main_type' => 'nullable|in:sale,rent,others',
            'property_type_id' => 'nullable|exists:property_types,property_type_id',
            'unit_type_id' => 'nullable|exists:unit_types,unit_type_id',
            'property_category_id' => 'nullable|exists:property_categories,property_category_id',
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'currency_id' => 'nullable|exists:currencies,currency_id',

            'stars' => 'required|in:1 Star,2 Stars,3 Stars,4 Stars,5 Stars',
            'available_from' => 'required|date',
            'no_bedrooms' => 'required|integer',
            'no_bathrooms' => 'required|integer',
            'no_floors' => 'required|integer',
            'no_guests' => 'required|integer',
            'min_no_of_stay' => 'required|integer|min:1',
            'parking' => 'nullable',
            'car_essential' => 'nullable',
            'total_square_feet' => 'required|numeric',
            'allow_bookings' => 'nullable',
            'country' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'lat' => 'nullable|string|max:50',
            'lng' => 'nullable|string|max:50',
            'zoom' => 'nullable|integer',
            'country_code' => 'nullable|string|max:2',
            'addon_id' => 'nullable|exists:addons,addon_id',
            'discount_id' => 'nullable|exists:discounts,discount_id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $data = $request->except(['address', 'zip_code', 'lat', 'lng', 'zoom', 'location_id', 'country', 'region', 'city', 'area', 'landmark', 'country_code']);

            // Handle booleans
            $data['parking'] = $request->has('parking') ? 1 : 0;
            $data['allow_bookings'] = $request->has('allow_bookings') ? 1 : 0;
            $data['car_essential'] = $request->has('car_essential') && $request->car_essential == 1 ? 1 : 0;

            $property = Property::create($data);

            // Create flat location record per property
            $propertyLocation = \App\Models\Location::create([
                'property_id' => $property->property_id,
                'country' => $request->country,
                'region' => $request->region,
                'city' => $request->city,
                'area' => $request->area,
                'landmark' => $request->landmark,
                'address' => $request->address,
                'zip_code' => $request->zip_code,
                'lat' => $request->lat,
                'lng' => $request->lng,
                'zoom' => $request->zoom ?? 12,
                'country_code' => $request->country_code ? strtoupper($request->country_code) : null,
            ]);

            // Handle Photos
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photoFile) {
                    $photoPath = $this->saveUploadedFile($photoFile, 'properties/photos/' . $property->property_id);

                    $photo = new \App\Models\PropertyPhoto();
                    $photo->property_id = $property->property_id;
                    $photo->property_photo_file = $photoPath;
                    $photo->status = 'active';
                    $photo->save();
                }
            }

            // Handle Videos
            if ($request->has('videos')) {
                foreach ($request->videos as $vData) {
                    $videoType = $vData['video_type'] ?? 'embed';
                    $video = new \App\Models\PropertyVideo();
                    $video->property_id = $property->property_id;
                    $video->video_type = $videoType;
                    $video->status = 'active';

                    if ($videoType === 'upload') {
                        if (isset($vData['video_file'])) {
                            $file = $vData['video_file'];
                            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $file->move(storage_path('app/public/uploads/properties/videos'), $filename);
                            $video->video_file = 'uploads/properties/videos/' . $filename;
                        } else {
                            $video->video_file = '';
                        }
                        if (isset($vData['poster_file'])) {
                            $file = $vData['poster_file'];
                            $filename = time() . '_poster_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $file->move(storage_path('app/public/uploads/properties/videos'), $filename);
                            $video->poster_file = 'uploads/properties/videos/' . $filename;
                        } else {
                            $video->poster_file = '';
                        }
                        $video->embed = '';
                    } else {
                        // embed type
                        $video->embed = $vData['embed'] ?? '';
                        $video->video_file = '';
                        $video->poster_file = '';
                    }

                    $video->save();
                }
            }

            // Handle Amenities
            if ($request->has('amenities')) {
                foreach ($request->amenities as $aData) {
                    if (!empty($aData['title'])) {
                        $amenity = new Amenity();
                        $amenity->property_id = $property->property_id;
                        $amenity->title = $aData['title'];

                        $amenity->icon_type = $aData['icon_type'] ?? 'image';

                        if ($amenity->icon_type === 'image') {
                            if (isset($aData['icon_file'])) {
                                $file = $aData['icon_file'];
                                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                                $file->move(storage_path('app/public/uploads/amenities'), $filename);
                                $amenity->icon = 'uploads/amenities/' . $filename;
                            } else {
                                $amenity->icon = null; // icon is optional
                            }
                        } elseif ($amenity->icon_type === 'code') {
                            if (!empty($aData['icon_code'])) {
                                $amenity->icon = $aData['icon_code'];
                            } else {
                                throw new \Exception("Icon SVG/HTML code is required for Amenity: " . $aData['title']);
                            }
                        }

                        $amenity->save();
                    }
                }
            }

            // Handle Facilities
            if ($request->has('facilities')) {
                foreach ($request->facilities as $fData) {
                    if (!empty($fData['title'])) {
                        $facility = new \App\Models\PropertyFacility();
                        $facility->property_id = $property->property_id;
                        $facility->title = $fData['title'];
                        $facility->description = $fData['description'] ?? null;
                        $facility->icon_type = $fData['icon_type'] ?? 'image';

                        if ($facility->icon_type === 'image') {
                            if (isset($fData['icon_file'])) {
                                $file = $fData['icon_file'];
                                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                                $file->move(storage_path('app/public/uploads/facilities'), $filename);
                                $facility->icon = 'uploads/facilities/' . $filename;
                            } else {
                                $facility->icon = null; // icon is optional
                            }
                        } elseif ($facility->icon_type === 'code') {
                            if (!empty($fData['icon_code'])) {
                                $facility->icon = $fData['icon_code'];
                            } else {
                                throw new \Exception("Icon SVG/HTML code is required for Facility: " . $fData['title']);
                            }
                        }

                        $facility->save();
                    }
                }
            }

            // Handle Policies
            if ($request->has('policies_data')) {
                foreach ($request->policies_data as $pData) {
                    if (!empty($pData['title'])) {
                        $policy = new \App\Models\PropertyPolicy();
                        $policy->property_id = $property->property_id;
                        $policy->title = $pData['title'];
                        $policy->description = $pData['description'] ?? null;
                        $policy->icon_type = $pData['icon_type'] ?? 'image';

                        if ($policy->icon_type === 'image') {
                            if (isset($pData['icon_file'])) {
                                $file = $pData['icon_file'];
                                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                                $file->move(storage_path('app/public/uploads/policies'), $filename);
                                $policy->icon = 'uploads/policies/' . $filename;
                            } else {
                                $policy->icon = null; // icon is optional
                            }
                        } elseif ($policy->icon_type === 'code') {
                            if (!empty($pData['icon_code'])) {
                                $policy->icon = $pData['icon_code'];
                            } else {
                                $policy->icon = null; // icon is optional
                            }
                        }

                        $policy->status = 'active';
                        $policy->save();
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('property.index')->with('t-success', 'Property created successfully');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('t-error', 'Error creating Property: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified property details.
     */
    public function show(int $id): View
    {
        $property = Property::with([
            'user',
            'propertyType',
            'unitType',
            'propertyCategory',
            'currency',
            'amenities',
            'rates',
            'addon',
            'discount',
            'bookings',
            'photos',
            'videos',
            'facilities',
            'policies',
            'location',
        ])->findOrFail($id);

        return view('backend.layouts.property.show', compact('property'));
    }

    /**
     * Show the form for editing the specified property.
     */
    public function edit(int $id): View
    {
        $data = Property::with(['amenities', 'rates', 'addon', 'discount', 'bookings', 'photos', 'videos', 'facilities', 'policies', 'location'])->findOrFail($id);

        $users = User::all();
        $propertyTypes = PropertyType::where('status', 'active')->get();
        $unitTypes = UnitType::where('status', 'active')->get();
        $currencies = Currency::where('status', 'active')->get();
        $amenities = Amenity::all();
        $propertyCategories = PropertyCategory::where('status', 'active')->get();
        $addons = Addon::where('status', 'active')->get();
        $discounts = Discount::where('status', 'active')->get();

        return view('backend.layouts.property.edit', compact(
            'data',
            'users',
            'propertyTypes',
            'unitTypes',
            'currencies',
            'amenities',
            'propertyCategories',
            'addons',
            'discounts'
        ));
    }

    /**
     * Update the specified property in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $property = Property::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'property_status' => 'nullable|in:inactive,active,pending,sold,withdrawn,others',
            'property_main_type' => 'nullable|in:sale,rent,others',
            'property_type_id' => 'nullable|exists:property_types,property_type_id',
            'unit_type_id' => 'nullable|exists:unit_types,unit_type_id',
            'property_category_id' => 'nullable|exists:property_categories,property_category_id',
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'currency_id' => 'nullable|exists:currencies,currency_id',

            'stars' => 'required|in:1 Star,2 Stars,3 Stars,4 Stars,5 Stars',
            'available_from' => 'required|date',
            'no_bedrooms' => 'required|integer',
            'no_bathrooms' => 'required|integer',
            'no_floors' => 'required|integer',
            'no_guests' => 'required|integer',
            'min_no_of_stay' => 'required|integer|min:1',
            'parking' => 'nullable',
            'car_essential' => 'nullable',
            'total_square_feet' => 'required|numeric',
            'allow_bookings' => 'nullable',
            'country' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'lat' => 'nullable|string|max:50',
            'lng' => 'nullable|string|max:50',
            'zoom' => 'nullable|integer',
            'country_code' => 'nullable|string|max:2',
            'addon_id' => 'nullable|exists:addons,addon_id',
            'discount_id' => 'nullable|exists:discounts,discount_id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $data = $request->except(['address', 'zip_code', 'lat', 'lng', 'zoom', 'location_id', 'country', 'region', 'city', 'area', 'landmark', 'country_code']);

            // Handle booleans
            $data['parking'] = $request->has('parking') ? 1 : 0;
            $data['allow_bookings'] = $request->has('allow_bookings') ? 1 : 0;
            $data['car_essential'] = $request->has('car_essential') && $request->car_essential == 1 ? 1 : 0;

            $property->update($data);

            // Handle Property Location
            $propertyLocation = $property->location;

            if ($propertyLocation) {
                // Update existing location attached to property
                $propertyLocation->update([
                    'country' => $request->country,
                    'region' => $request->region,
                    'city' => $request->city,
                    'area' => $request->area,
                    'landmark' => $request->landmark,
                    'address' => $request->address,
                    'zip_code' => $request->zip_code,
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'zoom' => $request->zoom ?? 12,
                    'country_code' => $request->country_code ? strtoupper($request->country_code) : null,
                ]);
            } else {
                // Create new location for property
                \App\Models\Location::create([
                    'property_id' => $property->property_id,
                    'country' => $request->country,
                    'region' => $request->region,
                    'city' => $request->city,
                    'area' => $request->area,
                    'landmark' => $request->landmark,
                    'address' => $request->address,
                    'zip_code' => $request->zip_code,
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'zoom' => $request->zoom ?? 12,
                    'country_code' => $request->country_code ? strtoupper($request->country_code) : null,
                ]);
            }

            // Handle Amenities
            if ($request->has('amenities')) {
                // Sync Amenities: Keep track of icons to preserve
                $existingIcons = [];
                foreach ($request->amenities as $aData) {
                    if (isset($aData['existing_icon'])) {
                        $existingIcons[] = $aData['existing_icon'];
                    }
                }

                // Delete old icon files if they are images and NOT in the current (to-be-kept) list
                foreach ($property->amenities as $oldAmenity) {
                    if ($oldAmenity->icon_type === 'image' && !empty($oldAmenity->icon)) {
                        if (!in_array($oldAmenity->icon, $existingIcons) && file_exists(storage_path('app/public/' . $oldAmenity->icon))) {
                            @unlink(storage_path('app/public/' . $oldAmenity->icon));
                        }
                    }
                }
                $property->amenities()->delete();

                foreach ($request->amenities as $aData) {
                    if (!empty($aData['title'])) {
                        $amenity = new \App\Models\Amenity();
                        $amenity->property_id = $property->property_id;
                        $amenity->title = $aData['title'];
                        $amenity->icon_type = $aData['icon_type'] ?? 'image';

                        if ($amenity->icon_type === 'image') {
                            if (isset($aData['icon_file'])) {
                                $file = $aData['icon_file'];
                                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                                $file->move(storage_path('app/public/uploads/amenities'), $filename);
                                $amenity->icon = 'uploads/amenities/' . $filename;
                            } elseif (isset($aData['existing_icon'])) {
                                $amenity->icon = $aData['existing_icon'];
                            } else {
                                $amenity->icon = null; // icon is optional
                            }
                        } elseif ($amenity->icon_type === 'code') {
                            if (!empty($aData['icon_code'])) {
                                $amenity->icon = $aData['icon_code'];
                            } else {
                                throw new \Exception("Icon SVG/HTML code is required for Amenity: " . $aData['title']);
                            }
                        }
                        $amenity->save();
                    }
                }
            } else {
                // If no amenities provided, delete existing ones
                foreach ($property->amenities as $oldAmenity) {
                    if ($oldAmenity->icon_type === 'image' && !empty($oldAmenity->icon) && file_exists(storage_path('app/public/' . $oldAmenity->icon))) {
                        @unlink(storage_path('app/public/' . $oldAmenity->icon));
                    }
                }
                $property->amenities()->delete();
            }

            // Handle Facilities
            if ($request->has('facilities')) {
                // Sync Facilities: Keep track of icons to preserve
                $existingIcons = [];
                foreach ($request->facilities as $fData) {
                    if (isset($fData['existing_icon'])) {
                        $existingIcons[] = $fData['existing_icon'];
                    }
                }

                // Delete old icon files if they are images and NOT in the current list
                foreach ($property->facilities as $oldFacility) {
                    if ($oldFacility->icon_type === 'image' && !empty($oldFacility->icon)) {
                        if (!in_array($oldFacility->icon, $existingIcons) && file_exists(storage_path('app/public/' . $oldFacility->icon))) {
                            @unlink(storage_path('app/public/' . $oldFacility->icon));
                        }
                    }
                }
                $property->facilities()->delete();

                foreach ($request->facilities as $fData) {
                    if (!empty($fData['title'])) {
                        $facility = new \App\Models\PropertyFacility();
                        $facility->property_id = $property->property_id;
                        $facility->title = $fData['title'];
                        $facility->description = $fData['description'] ?? null;
                        $facility->icon_type = $fData['icon_type'] ?? 'image';

                        if ($facility->icon_type === 'image') {
                            if (isset($fData['icon_file'])) {
                                $file = $fData['icon_file'];
                                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                                $file->move(storage_path('app/public/uploads/facilities'), $filename);
                                $facility->icon = 'uploads/facilities/' . $filename;
                            } elseif (isset($fData['existing_icon'])) {
                                $facility->icon = $fData['existing_icon'];
                            } else {
                                $facility->icon = null; // icon is optional
                            }
                        } elseif ($facility->icon_type === 'code') {
                            if (!empty($fData['icon_code'])) {
                                $facility->icon = $fData['icon_code'];
                            } else {
                                throw new \Exception("Icon SVG/HTML code is required for Facility: " . $fData['title']);
                            }
                        }
                        $facility->save();
                    }
                }
            } else {
                // If no facilities provided, delete existing ones
                foreach ($property->facilities as $oldFacility) {
                    if ($oldFacility->icon_type === 'image' && !empty($oldFacility->icon) && file_exists(storage_path('app/public/' . $oldFacility->icon))) {
                        @unlink(storage_path('app/public/' . $oldFacility->icon));
                    }
                }
                $property->facilities()->delete();
            }


            // Handle Photos (additive - new uploads added alongside existing)
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photoFile) {
                    $filename = time() . '_' . uniqid() . '.' . $photoFile->getClientOriginalExtension();
                    $photoFile->move(storage_path('app/public/uploads/properties/photos'), $filename);

                    $photo = new \App\Models\PropertyPhoto();
                    $photo->property_id = $property->property_id;
                    $photo->property_photo_file = 'uploads/properties/photos/' . $filename;
                    $photo->status = 'active';
                    $photo->save();
                }
            }

            // Handle delete of individual photos (fallback for traditional form submission)
            if ($request->has('delete_photos')) {
                foreach ($request->delete_photos as $photoId) {
                    $photo = \App\Models\PropertyPhoto::find($photoId);
                    if ($photo && $photo->property_id == $property->property_id) {
                        $rawFile = $photo->getRawOriginal('property_photo_file');
                        if (!empty($rawFile) && file_exists(storage_path('app/public/' . $rawFile))) {
                            @unlink(storage_path('app/public/' . $rawFile));
                        }
                        $photo->delete();
                    }
                }
            }

            // Handle Videos
            if ($request->has('videos')) {
                // Sync Videos: Track files to keep
                $existingVideoFiles = [];
                $existingPosterFiles = [];
                foreach ($request->videos as $vData) {
                    if (isset($vData['existing_video_file'])) $existingVideoFiles[] = $vData['existing_video_file'];
                    if (isset($vData['existing_poster_file'])) $existingPosterFiles[] = $vData['existing_poster_file'];
                }

                foreach ($property->videos as $oldVideo) {
                    if ($oldVideo->video_type === 'upload') {
                        if (!empty($oldVideo->video_file) && !in_array($oldVideo->video_file, $existingVideoFiles) && file_exists(storage_path('app/public/' . $oldVideo->video_file))) {
                            @unlink(storage_path('app/public/' . $oldVideo->video_file));
                        }
                        if (!empty($oldVideo->poster_file) && !in_array($oldVideo->poster_file, $existingPosterFiles) && file_exists(storage_path('app/public/' . $oldVideo->poster_file))) {
                            @unlink(storage_path('app/public/' . $oldVideo->poster_file));
                        }
                    }
                }
                $property->videos()->delete();

                foreach ($request->videos as $vData) {
                    $videoType = $vData['video_type'] ?? 'embed';
                    $video = new \App\Models\PropertyVideo();
                    $video->property_id = $property->property_id;
                    $video->video_type = $videoType;
                    $video->status = 'active';

                    if ($videoType === 'upload') {
                        if (isset($vData['video_file'])) {
                            $file = $vData['video_file'];
                            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $file->move(storage_path('app/public/uploads/properties/videos'), $filename);
                            $video->video_file = 'uploads/properties/videos/' . $filename;
                        } elseif (isset($vData['existing_video_file'])) {
                            $video->video_file = $vData['existing_video_file'];
                        } else {
                            $video->video_file = '';
                        }

                        if (isset($vData['poster_file'])) {
                            $file = $vData['poster_file'];
                            $filename = time() . '_poster_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $file->move(storage_path('app/public/uploads/properties/videos'), $filename);
                            $video->poster_file = 'uploads/properties/videos/' . $filename;
                        } elseif (isset($vData['existing_poster_file'])) {
                            $video->poster_file = $vData['existing_poster_file'];
                        } else {
                            $video->poster_file = '';
                        }
                        $video->embed = '';
                    } else {
                        $video->embed = $vData['embed'] ?? '';
                        $video->video_file = '';
                        $video->poster_file = '';
                    }
                    $video->save();
                }
            }


            // Handle Policies
            if ($request->has('policies_data')) {
                // Sync Policies: Track icons to preserve
                $existingIcons = [];
                foreach ($request->policies_data as $pData) {
                    if (isset($pData['existing_icon'])) $existingIcons[] = $pData['existing_icon'];
                }

                foreach ($property->policies as $oldPolicy) {
                    if ($oldPolicy->icon_type === 'image' && !empty($oldPolicy->icon)) {
                        if (!in_array($oldPolicy->icon, $existingIcons) && file_exists(storage_path('app/public/' . $oldPolicy->icon))) {
                            @unlink(storage_path('app/public/' . $oldPolicy->icon));
                        }
                    }
                }
                $property->policies()->delete();

                foreach ($request->policies_data as $pData) {
                    if (!empty($pData['title'])) {
                        $policy = new \App\Models\PropertyPolicy();
                        $policy->property_id = $property->property_id;
                        $policy->title = $pData['title'];
                        $policy->description = $pData['description'] ?? null;
                        $policy->icon_type = $pData['icon_type'] ?? 'image';

                        if ($policy->icon_type === 'image') {
                            if (isset($pData['icon_file'])) {
                                $file = $pData['icon_file'];
                                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                                $file->move(storage_path('app/public/uploads/policies'), $filename);
                                $policy->icon = 'uploads/policies/' . $filename;
                            } elseif (isset($pData['existing_icon'])) {
                                $policy->icon = $pData['existing_icon'];
                            } else {
                                $policy->icon = null; // icon is optional
                            }
                        } elseif ($policy->icon_type === 'code') {
                            if (!empty($pData['icon_code'])) {
                                $policy->icon = $pData['icon_code'];
                            } else {
                                $policy->icon = null; // icon is optional
                            }
                        }
                        $policy->status = 'active';
                        $policy->save();
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->back()->with('t-success', 'Property updated successfully');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('t-error', 'Error updating Property: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified property from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $data = Property::findOrFail($id);
            $data->delete();

            return response()->json([
                't-success' => true,
                'message' => 'Property deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                't-success' => false,
                'message' => 'Error deleting Property',
            ]);
        }
    }

    /**
     * Toggle the status of the property.
     */
    public function status(int $id): JsonResponse
    {
        $data = Property::findOrFail($id);
        $data->status = ($data->status == 'active') ? 'inactive' : 'active';
        $data->save();

        return response()->json([
            't-success' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    /**
     * AJAX: Delete selected property photos.
     */
    public function deletePhotos(Request $request): JsonResponse
    {
        $request->validate([
            'photo_ids'   => 'required|array|min:1',
            'photo_ids.*' => 'integer',
            'property_id' => 'required|integer',
        ]);

        $deleted = 0;
        foreach ($request->photo_ids as $photoId) {
            $photo = \App\Models\PropertyPhoto::find($photoId);
            if ($photo && $photo->property_id == $request->property_id) {
                $rawFile = $photo->getRawOriginal('property_photo_file');
                if (!empty($rawFile) && file_exists(storage_path('app/public/' . $rawFile))) {
                    @unlink(storage_path('app/public/' . $rawFile));
                }
                $photo->delete();
                $deleted++;
            }
        }

        return response()->json([
            't-success' => true,
            'deleted'   => $deleted,
            'message'   => $deleted . ' photo(s) deleted successfully.',
        ]);
    }

    /**
     * Save an uploaded file to storage/app/public/uploads/
     */
    private function saveUploadedFile($file, string $subfolder, string $filenamePrefix = ''): string
    {
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = ($filenamePrefix ? $filenamePrefix . '_' : '') . time() . '_' . uniqid() . '.' . $extension;

        return $file->storeAs('uploads/' . $subfolder, $filename, 'public');
    }

    /**
     * Delete an uploaded file from storage/app/public/
     */
    private function deleteUploadedFile(?string $relativePath): void
    {
        if (empty($relativePath)) {
            return;
        }

        $cleanPath = ltrim(str_replace(url('/'), '', $relativePath), '/');

        if (\Illuminate\Support\Str::startsWith($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($cleanPath);
        }
    }
}
