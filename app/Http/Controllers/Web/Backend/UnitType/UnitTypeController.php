<?php

namespace App\Http\Controllers\Web\Backend\UnitType;

use App\Http\Controllers\Controller;
use App\Models\UnitType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class UnitTypeController extends Controller
{
    /**
     * Display a listing of unit types.
     */
    public function index(Request $request): JsonResponse | View
    {
        if ($request->ajax()) {
            $data = UnitType::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($data) {
                    return $data->title;
                })
                ->addColumn('status', function ($data) {
                    $badgeClass = $data->status === 'active' ? 'bg-success' : 'bg-danger';
                    $statusText = $data->status === 'active' ? 'Active' : 'Inactive';
                    return '<span class="badge ' . $badgeClass . '" style="cursor: pointer;" onclick="toggleStatus(' . $data->unit_type_id . ')">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <a href="' . route('unit-type.edit', $data->unit_type_id) . '" class="btn btn-primary fs-14 text-white edit-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                                <button type="button" onclick="deleteData(' . $data->unit_type_id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make();
        }
        return view('backend.layouts.unit-type.index');
    }

    /**
     * Show the form for creating a new unit type.
     */
    public function create(): View
    {
        return view('backend.layouts.unit-type.create');
    }

    /**
     * Store a newly created unit type in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $slugStr = $request->slug ? Str::slug($request->slug) : Str::slug($request->title);

            UnitType::create([
                'title' => $request->title,
                'slug' => $slugStr,
                'status' => $request->status,
            ]);

            return redirect()->route('unit-type.index')->with('t-success', 'Unit Type created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error creating Unit Type: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified unit type.
     */
    public function edit(int $id): View
    {
        $data = UnitType::findOrFail($id);
        return view('backend.layouts.unit-type.edit', compact('data'));
    }

    /**
     * Update the specified unit type in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $data = UnitType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
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

            return redirect()->route('unit-type.index')->with('t-success', 'Unit Type updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error updating Unit Type: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified unit type from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $data = UnitType::findOrFail($id);
            $data->delete();

            return response()->json([
                't-success' => true,
                'message' => 'Unit Type deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                't-success' => false,
                'message' => 'Error deleting Unit Type',
            ]);
        }
    }

    /**
     * Toggle the active status of the unit type.
     */
    public function status(int $id): JsonResponse
    {
        $data = UnitType::findOrFail($id);
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();

        return response()->json([
            't-success' => true,
            'message' => 'Status updated successfully',
        ]);
    }
}
