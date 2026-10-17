<?php

namespace App\Http\Controllers\Web\Backend\Rate;

use App\Http\Controllers\Controller;
use App\Models\Rate;
use App\Models\Property;
use App\Models\UnitType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class RateController extends Controller
{
    /**
     * Display a property of rates.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Rate::with(['property', 'unitType'])->latest();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('property', function ($line) {
                    return $line->property ? $line->property->title : 'N/A';
                })
                ->addColumn('unit_type', function ($line) {
                    return $line->unitType ? $line->unitType->title : 'N/A';
                })
                ->addColumn('status', function ($line) {
                    $status = $line->status === 'active' ? 'checked' : '';
                    return '<div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="switch-' . $line->rate_id . '" ' . $status . ' onclick="statusToggle(' . $line->rate_id . ')">
                            </div>';
                })
                ->addColumn('action', function ($line) {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <a href="' . route('rate.edit', $line->rate_id) . '" class="btn btn-primary" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                                <button type="button" onclick="deleteData(' . $line->rate_id . ')" class="btn btn-danger" title="Delete">
                                    <i class="fe fe-trash-2"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        return view('backend.layouts.rate.index');
    }

    /**
     * Show the form for creating a new rate.
     */
    public function create(): View
    {
        $properties = Property::all();
        $unitTypes = UnitType::all();
        return view('backend.layouts.rate.create', compact('properties', 'unitTypes'));
    }

    /**
     * Store a newly created rate in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'nightly_rate' => 'required|numeric|min:0',
            'nightly_rate_weekend' => 'nullable|numeric|min:0',
            'min_stay' => 'required|integer|min:1',
            'property_id' => 'required|exists:properties,property_id',
            'status' => 'required|in:active,inactive',
            'is_special_offer' => 'required|in:active,inactive',
            'changeover_day' => 'required|integer|between:0,7',
            'persons' => 'required|integer|min:1',
            'extra_person_price_night' => 'required|numeric|min:0',
            'unit_type_id' => 'nullable|exists:unit_types,unit_type_id',
            'is_rate_overwrite' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $request->all();
        $validated['status'] = $request->status;
        $validated['is_special_offer'] = $request->is_special_offer;
        $validated['is_rate_overwrite'] = $request->is_rate_overwrite;

        $days = [0 => null, 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday', 7 => 'sunday'];
        $validated['changeover_day'] = $days[(int) $request->changeover_day] ?? null;

        Rate::create($validated);

        return redirect()->route('rate.index')->with('t-success', 'Rate created successfully.');
    }

    /**
     * Show the form for editing the specified rate.
     */
    public function edit(int $id): View
    {
        $data = Rate::findOrFail($id);
        $properties = Property::all();
        $unitTypes = UnitType::all();
        return view('backend.layouts.rate.edit', compact('data', 'properties', 'unitTypes'));
    }

    /**
     * Update the specified rate in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $data = Rate::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'nightly_rate' => 'required|numeric|min:0',
            'nightly_rate_weekend' => 'nullable|numeric|min:0',
            'min_stay' => 'required|integer|min:1',
            'property_id' => 'required|exists:properties,property_id',
            'status' => 'required|in:active,inactive',
            'is_special_offer' => 'required|in:active,inactive',
            'changeover_day' => 'required|integer|between:0,7',
            'persons' => 'required|integer|min:1',
            'extra_person_price_night' => 'required|numeric|min:0',
            'unit_type_id' => 'nullable|exists:unit_types,unit_type_id',
            'is_rate_overwrite' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $request->all();
        $validated['status'] = $request->status;
        $validated['is_special_offer'] = $request->is_special_offer;
        $validated['is_rate_overwrite'] = $request->is_rate_overwrite;

        $days = [0 => null, 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday', 7 => 'sunday'];
        $validated['changeover_day'] = $days[(int) $request->changeover_day] ?? null;

        $data->update($validated);

        return redirect()->route('rate.index')->with('t-success', 'Rate updated successfully.');
    }

    /**
     * Remove the specified rate from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $data = Rate::findOrFail($id);
        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rate deleted successfully.',
        ]);
    }

    /**
     * Toggle the status of the specified rate.
     */
    public function status(int $id): JsonResponse
    {
        $data = Rate::findOrFail($id);
        $data->status = ($data->status == 'active') ? 'inactive' : 'active';
        $data->save();

        return response()->json([
            'success' => true,
            'message' => 'Rate status updated successfully.',
            'status' => $data->status,
        ]);
    }
}
