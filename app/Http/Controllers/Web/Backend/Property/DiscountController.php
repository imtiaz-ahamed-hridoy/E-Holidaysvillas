<?php

namespace App\Http\Controllers\Web\Backend\Property;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class DiscountController extends Controller
{
    /**
     * Display a listing of discounts.
     */
    public function index(Request $request): JsonResponse | View
    {
        if ($request->ajax()) {
            $data = Discount::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($data) {
                    $badgeClass = $data->status === 'active' ? 'bg-success' : 'bg-danger';
                    $statusText = $data->status === 'active' ? 'Active' : 'Inactive';
                    return '<span class="badge ' . $badgeClass . '" style="cursor: pointer;" onclick="toggleStatus(' . $data->discount_id . ')">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <a href="' . route('discounts.edit', $data->discount_id) . '" class="btn btn-primary fs-14 text-white edit-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                                <button type="button" onclick="deleteData(' . $data->discount_id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make();
        }
        return view('backend.layouts.discounts.index');
    }

    /**
     * Show the form for creating a new discount.
     */
    public function create(): View
    {
        $properties = Property::all();
        return view('backend.layouts.discounts.create', compact('properties'));
    }

    /**
     * Store a newly created discount in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'discount_type' => 'required|string|max:255',
            'discount_percentage' => 'required|string|max:255',
            'persons' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'property_id' => 'required|exists:properties,property_id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->except('property_id');
            $discount = Discount::create($data);

            if ($request->filled('property_id')) {
                Property::where('property_id', $request->property_id)->update(['discount_id' => $discount->discount_id]);
            }

            return redirect()->route('discounts.index')->with('t-success', 'Discount created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error creating Discount: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified discount.
     */
    public function edit(int $id): View
    {
        $data = Discount::findOrFail($id);
        $properties = Property::all();
        $associatedProperty = Property::where('discount_id', $id)->first();
        $data->property_id = $associatedProperty ? $associatedProperty->property_id : null;

        return view('backend.layouts.discounts.edit', compact('data', 'properties'));
    }

    /**
     * Update the specified discount in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $discount = Discount::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'discount_type' => 'required|string|max:255',
            'discount_percentage' => 'required|string|max:255',
            'persons' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'property_id' => 'required|exists:properties,property_id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $request->except('property_id');
            $discount->update($data);

            if ($request->filled('property_id')) {
                Property::where('property_id', $request->property_id)->update(['discount_id' => $discount->discount_id]);
            }

            return redirect()->route('discounts.index')->with('t-success', 'Discount updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error updating Discount: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified discount from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $discount = Discount::findOrFail($id);
            $discount->delete();

            return response()->json([
                't-success' => true,
                'message' => 'Discount deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                't-success' => false,
                'message' => 'Error deleting Discount',
            ]);
        }
    }

    /**
     * Toggle the status of the discount.
     */
    public function status(int $id): JsonResponse
    {
        $discount = Discount::findOrFail($id);
        $discount->status = $discount->status === 'active' ? 'inactive' : 'active';
        $discount->save();

        return response()->json([
            't-success' => true,
            'message' => 'Status updated successfully',
        ]);
    }
}
