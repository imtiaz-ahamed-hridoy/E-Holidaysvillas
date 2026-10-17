<?php

namespace App\Http\Controllers\Web\Backend\Property;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class PropertyTypeController extends Controller
{
    /**
     * Display a listing of property types.
     */
    public function index(Request $request): JsonResponse | View
    {
        if ($request->ajax()) {
            $data = PropertyType::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($data) {
                    return $data->title;
                })
                ->addColumn('status', function ($data) {
                    $badgeClass = $data->status === 'active' ? 'bg-success' : 'bg-danger';
                    $statusText = $data->status === 'active' ? 'Active' : 'Inactive';
                    return '<span class="badge ' . $badgeClass . '" style="cursor: pointer;" onclick="toggleStatus(' . $data->property_type_id . ')">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <a href="' . route('property-type.edit', $data->property_type_id) . '" class="btn btn-primary fs-14 text-white edit-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                                <button type="button" onclick="deleteData(' . $data->property_type_id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make();
        }
        return view('backend.layouts.property-type.index');
    }

    /**
     * Show the form for creating a new property type.
     */
    public function create(): View
    {
        return view('backend.layouts.property-type.create');
    }

    /**
     * Store a newly created property type in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'marker_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $markerPath = null;
            if ($request->hasFile('marker_file')) {
                $marker = $request->file('marker_file');
                $markerName = time() . '_' . $marker->getClientOriginalName();
                $markerPath = $marker->storeAs('uploads/marker', $markerName, 'public');
            } elseif ($request->existing_marker) {
                $markerPath = $request->existing_marker;
            }

            // Format slug properly, or auto-generate from title
            $slugStr = $request->slug ? \Illuminate\Support\Str::slug($request->slug) : \Illuminate\Support\Str::slug($request->title);

            PropertyType::create([
                'title' => $request->title,
                'slug' => $slugStr,
                'status' => $request->status,
                'marker_file' => $markerPath,
            ]);

            return redirect()->route('property-type.index')->with('t-success', 'Property Type created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error creating Property Type: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified property type.
     */
    public function edit(int $id): View
    {
        $data = PropertyType::findOrFail($id);
        return view('backend.layouts.property-type.edit', compact('data'));
    }

    /**
     * Update the specified property type in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $data = PropertyType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'marker_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $markerPath = $data->marker_file;
            if ($request->hasFile('marker_file')) {
                if ($data->marker_file && Storage::disk('public')->exists($data->marker_file)) {
                    Storage::disk('public')->delete($data->marker_file);
                }

                $marker = $request->file('marker_file');
                $markerName = time() . '_' . $marker->getClientOriginalName();
                $markerPath = $marker->storeAs('uploads/marker', $markerName, 'public');
            } elseif ($request->existing_marker) {
                $markerPath = $request->existing_marker;
            }

            // Format slug properly, or auto-generate from title
            $slugStr = $request->slug ? \Illuminate\Support\Str::slug($request->slug) : \Illuminate\Support\Str::slug($request->title);

            $data->update([
                'title' => $request->title,
                'slug' => $slugStr,
                'status' => $request->status,
                'marker_file' => $markerPath,
            ]);

            return redirect()->route('property-type.index')->with('t-success', 'Property Type updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error updating Property Type: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified property type from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $data = PropertyType::findOrFail($id);
            $data->delete();

            return response()->json([
                't-success' => true,
                'message' => 'Property Type deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                't-success' => false,
                'message' => 'Error deleting Property Type',
            ]);
        }
    }

    /**
     * Toggle the active status of the property type.
     */
    public function status(int $id): JsonResponse
    {
        $data = PropertyType::findOrFail($id);
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();

        return response()->json([
            't-success' => true,
            'message' => 'Status updated successfully',
        ]);
    }
}
