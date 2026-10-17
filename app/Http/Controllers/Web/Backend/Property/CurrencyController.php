<?php

namespace App\Http\Controllers\Web\Backend\Property;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class CurrencyController extends Controller
{
    /**
     * Display a listing of currencies.
     *
     * @param Request $request
     * @return JsonResponse|View
     */
    public function index(Request $request): JsonResponse | View
    {
        if ($request->ajax()) {
            $data = Currency::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('title', function ($data) {
                    return $data->title;
                })
                ->addColumn('code', function ($data) {
                    return $data->currency_code;
                })
                ->addColumn('rate', function ($data) {
                    return $data->currency_rate;
                })
                ->addColumn('is_default', function ($data) {
                    $badgeClass = $data->is_default === 'yes' ? 'bg-info' : 'bg-secondary';
                    $text = $data->is_default === 'yes' ? 'Default' : 'Regular';
                    return '<span class="badge ' . $badgeClass . '">' . $text . '</span>';
                })
                ->addColumn('status', function ($data) {
                    $badgeClass = $data->status === 'active' ? 'bg-success' : 'bg-danger';
                    $statusText = ucfirst($data->status);
                    return '<span class="badge ' . $badgeClass . '" style="cursor: pointer;" onclick="toggleStatus(' . $data->currency_id . ')">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group">
                                <a href="' . route('currency.edit', $data->currency_id) . '" class="btn btn-primary fs-14 text-white edit-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                                <button type="button" onclick="deleteData(' . $data->currency_id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['status', 'is_default', 'action'])
                ->make();
        }
        return view('backend.layouts.currency.index');
    }

    /**
     * Show the form for creating a new currency.
     */
    public function create(): View
    {
        return view('backend.layouts.currency.create');
    }

    /**
     * Store a newly created currency in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:50',
            'currency_code' => 'required|string|max:10|unique:currencies,currency_code',
            'currency_rate' => 'required|numeric',
            'status' => 'required|in:active,inactive',
            'is_default' => 'required|in:yes,no',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Handle unique default currency
            if ($request->is_default === 'yes') {
                Currency::where('is_default', 'yes')->update(['is_default' => 'no']);
            }

            Currency::create($request->all());

            return redirect()->route('currency.index')->with('t-success', 'Currency created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error creating Currency: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified currency.
     */
    public function edit(int $id): View
    {
        $data = Currency::findOrFail($id);
        return view('backend.layouts.currency.edit', compact('data'));
    }

    /**
     * Update the specified currency in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $data = Currency::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:50',
            'currency_code' => 'required|string|max:10|unique:currencies,currency_code,' . $id . ',currency_id',
            'currency_rate' => 'required|numeric',
            'status' => 'required|in:active,inactive',
            'is_default' => 'required|in:yes,no',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Handle unique default currency
            if ($request->is_default === 'yes') {
                Currency::where('is_default', 'yes')->update(['is_default' => 'no']);
            }

            $data->update($request->all());

            return redirect()->route('currency.index')->with('t-success', 'Currency updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error updating Currency: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified currency from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $data = Currency::findOrFail($id);
            $data->delete();

            return response()->json([
                't-success' => true,
                'message' => 'Currency deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                't-success' => false,
                'message' => 'Error deleting Currency',
            ]);
        }
    }

    /**
     * Toggle the active status of the currency.
     */
    public function status(int $id): JsonResponse
    {
        $data = Currency::findOrFail($id);
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();

        return response()->json([
            't-success' => true,
            'message' => 'Status updated successfully',
        ]);
    }
}
