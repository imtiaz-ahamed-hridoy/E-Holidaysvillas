<?php

namespace App\Http\Controllers\Web\Backend\PropertyCategory;

use App\Http\Controllers\Controller;
use App\Models\PropertyCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class PropertyCategoryController extends Controller
{
    protected array $protectedSlugs = ['islands', 'surfing', 'national-parks', 'lake', 'beach', 'camp'];

    /**
     * Display a listing of property categories.
     */
    public function index(Request $request): JsonResponse | View
    {
        if ($request->ajax()) {
            $data = PropertyCategory::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($data) {
                    return $data->title;
                })
                ->addColumn('status', function ($data) {
                    $badgeClass = $data->status === 'active' ? 'bg-success' : 'bg-danger';
                    $statusText = $data->status === 'active' ? 'Active' : 'Inactive';
                    return '<span class="badge ' . $badgeClass . '" style="cursor: pointer;" onclick="toggleStatus(' . $data->property_category_id . ')">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($data) {
                    $deleteBtn = '<button type="button" onclick="deleteData(' . $data->property_category_id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </button>';

                    return '<div class="btn-group btn-group-sm" role="group">
                                <a href="' . route('property-category.edit', $data->property_category_id) . '" class="btn btn-primary fs-14 text-white edit-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                                ' . $deleteBtn . '
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make();
        }
        return view('backend.layouts.property-category.index');
    }

    /**
     * Show the form for creating a new property category.
     */
    public function create(): View
    {
        return view('backend.layouts.property-category.create');
    }

    /**
     * Store a newly created property category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:property_categories,slug',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $slugStr = $request->slug ? Str::slug($request->slug) : Str::slug($request->title);

            PropertyCategory::create([
                'title' => $request->title,
                'slug' => $slugStr,
                'status' => $request->status,
            ]);

            return redirect()->route('property-category.index')->with('t-success', 'Property Category created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error creating Property Category: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified property category.
     */
    public function edit(int $id): RedirectResponse | View
    {
        $propertyCategory = PropertyCategory::findOrFail($id);

        return view('backend.layouts.property-category.edit', compact('propertyCategory'));
    }

    /**
     * Update the specified property category in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $data = PropertyCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:property_categories,slug,' . $id . ',property_category_id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $slugStr = $request->slug ? Str::slug($request->slug) : Str::slug($request->title);

            $data->update([
                'title' => $request->title,
                'slug' => $slugStr,
                'status' => $request->status,
            ]);

            return redirect()->route('property-category.index')->with('t-success', 'Property Category updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error updating Property Category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified property category from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $data = PropertyCategory::findOrFail($id);

            $data->delete();

            return response()->json([
                't-success' => true,
                'message' => 'Property Category deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                't-success' => false,
                'message' => 'Error deleting Property Category',
            ]);
        }
    }

    /**
     * Toggle the active status of the property category.
     */
    public function status(int $id): JsonResponse
    {
        $data = PropertyCategory::findOrFail($id);
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();

        return response()->json([
            't-success' => true,
            'message' => 'Status updated successfully',
        ]);
    }
}
