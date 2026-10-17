<?php

namespace App\Http\Controllers\Web\Backend\Amenity;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class AmenityController extends Controller
{
    /**
     * Display a property of amenities.
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            $data = Amenity::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('icon', function ($row) {
                    if (!$row->icon) return '-';
                    // Check if icon is an SVG or font icon code
                    if (str_starts_with($row->icon, '<svg') || str_starts_with($row->icon, '<i')) {
                        return '<div style="width: 30px; height: 30px; display: inline-block;">' . $row->icon . '</div>';
                    }
                    // Otherwise treat as an image path
                    return '<img src="' . \App\Helpers\Helper::imageUrl($row->icon) . '" alt="icon" style="height: 30px; width: 30px; object-fit: contain;">';
                })
                ->addColumn('title', fn($row) => $row->title)
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <a href="' . route('amenity.edit', $row->amenity_id) . '" class="btn btn-primary fs-14 text-white edit-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                                <button type="button" onclick="deleteData(' . $row->amenity_id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['icon', 'action'])
                ->make();
        }

        return view('backend.layouts.amenity.index');
    }

    /**
     * Show the form for creating a new amenity.
     */
    public function create(): View
    {
        return view('backend.layouts.amenity.create');
    }

    /**
     * Store a newly created amenity in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'title'     => 'required|string|max:100',
            'icon'      => 'nullable|string',
            'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $iconValue = $request->icon;

            if ($request->hasFile('icon_file')) {
                $file = $request->file('icon_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $iconValue = $file->storeAs('uploads/amenity', $filename, 'public');
            }

            Amenity::create([
                'title'      => $request->title,
                'icon'       => $iconValue,
            ]);

            return redirect()->route('amenity.index')->with('t-success', 'Amenity created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error creating Amenity: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified amenity.
     */
    public function edit(int $id): View
    {
        $data = Amenity::findOrFail($id);
        return view('backend.layouts.amenity.edit', compact('data'));
    }

    /**
     * Update the specified amenity in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $data = Amenity::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title'     => 'required|string|max:100',
            'icon'      => 'nullable|string',
            'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $iconValue = $data->icon;

            if ($request->hasFile('icon_file')) {
                // Delete old file if it exists and is actually a file path
                if ($data->icon && Storage::disk('public')->exists($data->icon)) {
                    Storage::disk('public')->delete($data->icon);
                }

                $file = $request->file('icon_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $iconValue = $file->storeAs('uploads/amenity', $filename, 'public');
            } elseif ($request->filled('icon')) {
                $iconValue = $request->icon;
            }

            $data->update([
                'title'      => $request->title,
                'icon'       => $iconValue,
            ]);

            return redirect()->route('amenity.index')->with('t-success', 'Amenity updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error updating Amenity: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified amenity from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $data = Amenity::findOrFail($id);

            // Delete file if it exists
            if ($data->icon && Storage::disk('public')->exists($data->icon)) {
                Storage::disk('public')->delete($data->icon);
            }

            $data->delete();

            return response()->json([
                't-success' => true,
                'message'   => 'Amenity deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                't-success' => false,
                'message'   => 'Error deleting Amenity.',
            ]);
        }
    }

    /**
     * Toggle the active status of the amenity.
     */
    public function status(int $id): JsonResponse
    {
        $amenity = Amenity::findOrFail($id);
        $amenity->status = ($amenity->status === 'active') ? 'inactive' : 'active';
        $amenity->save();

        return response()->json([
            't-success' => true,
            'message'   => 'Status updated successfully',
        ]);
    }
}
