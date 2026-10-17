<?php

namespace App\Http\Controllers\Web\Backend\Property;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class AddonController extends Controller
{
    /**
     * Display a listing of addons.
     */
    public function index(Request $request): JsonResponse | View
    {
        if ($request->ajax()) {
            $data = Addon::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('price', function ($data) {
                    return '€' . number_format($data->price, 2);
                })
                ->addColumn('status', function ($data) {
                    $badgeClass = $data->status === 'active' ? 'bg-success' : 'bg-danger';
                    $statusText = $data->status === 'active' ? 'Active' : 'Inactive';
                    return '<span class="badge ' . $badgeClass . '" style="cursor: pointer;" onclick="toggleStatus(' . $data->addon_id . ')">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <a href="' . route('addons.edit', $data->addon_id) . '" class="btn btn-primary fs-14 text-white edit-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                                <button type="button" onclick="deleteData(' . $data->addon_id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make();
        }
        return view('backend.layouts.addons.index');
    }

    /**
     * Show the form for creating a new addon.
     */
    public function create(): View
    {
        $properties = Property::all();
        return view('backend.layouts.addons.create', compact('properties'));
    }

    /**
     * Store a newly created addon in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'addon_type' => 'required|string|max:100',
            'price' => 'required|numeric',
            'property_id' => 'required|exists:properties,property_id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->except('property_id');
            $addon = Addon::create($data);

            if ($request->filled('property_id')) {
                Property::where('property_id', $request->property_id)->update(['addon_id' => $addon->addon_id]);
            }

            return redirect()->route('addons.index')->with('t-success', 'Addon created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error creating Addon: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified addon.
     */
    public function edit(int $id): View
    {
        $data = Addon::findOrFail($id);
        $properties = Property::all();
        // Determine which property is currently using this addon
        $associatedProperty = Property::where('addon_id', $id)->first();
        $data->property_id = $associatedProperty ? $associatedProperty->property_id : null;

        return view('backend.layouts.addons.edit', compact('data', 'properties'));
    }

    /**
     * Update the specified addon in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $addon = Addon::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'addon_type' => 'required|string|max:100',
            'price' => 'required|numeric',
            'property_id' => 'required|exists:properties,property_id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->except('property_id');
            $addon->update($data);

            if ($request->filled('property_id')) {
                // Clear old associations for this property if needed (one property has one addon)
                // But specifically, we are setting THIS addon as the property's primary addon.
                Property::where('property_id', $request->property_id)->update(['addon_id' => $addon->addon_id]);
            }

            return redirect()->route('addons.index')->with('t-success', 'Addon updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error updating Addon: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified addon from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $addon = Addon::findOrFail($id);
            $addon->delete();

            return response()->json([
                't-success' => true,
                'message' => 'Addon deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                't-success' => false,
                'message' => 'Error deleting Addon',
            ]);
        }
    }

    /**
     * Toggle the active status of the addon.
     */
    public function status(int $id): JsonResponse
    {
        $addon = Addon::findOrFail($id);
        $addon->status = $addon->status === 'active' ? 'inactive' : 'active';
        $addon->save();

        return response()->json([
            't-success' => true,
            'message' => 'Status updated successfully',
        ]);
    }
}
