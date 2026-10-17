<?php

namespace App\Http\Controllers\Web\Backend\Review;

use App\Models\Review;
use App\Models\Property;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Review::with(['property'])->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('reviewer', function ($data) {
                    $avatar = $data->avatar
                        ? '<img src="' . \App\Helpers\Helper::imageUrl('uploads/reviews/' . $data->avatar) . '" class="rounded-circle me-2" width="32" height="32" style="object-fit:cover;">'
                        : '<span class="avatar avatar-sm bg-primary rounded-circle me-2" style="width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;font-size:0.75rem;color:#fff;">' . strtoupper(mb_substr($data->name ?? 'G', 0, 1)) . '</span>';
                    return $avatar . e($data->name ?? 'Guest');
                })
                ->addColumn('rating', function ($data) {
                    $rating = (int) $data->rating_stars;
                    $stars = '';
                    for ($i = 1; $i <= 5; $i++) {
                        $stars .= $i <= $rating
                            ? '<i class="fa fa-star text-warning"></i>'
                            : '<i class="fa fa-star-o text-muted"></i>';
                    }
                    return $stars;
                })
                ->addColumn('comment', function ($data) {
                    return \Illuminate\Support\Str::limit($data->details, 50);
                })
                ->addColumn('property', function ($data) {
                    return $data->property ? $data->property->title : 'N/A';
                })
                ->addColumn("status", function ($data) {
                    if ($data->status === 'active') {
                        return "<span class='badge bg-success'>Active</span>";
                    } else {
                        return "<span class='badge bg-danger'>Inactive</span>";
                    }
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group">
                        <a href="' . route('review.edit', ['id' => $data->review_id]) . '" class="btn btn-primary fs-14 text-white" title="Edit">
                            <i class="fe fe-edit"></i>
                        </a>
                        <a href="#" onclick="showDeleteConfirm(' . $data->review_id . ')" class="btn btn-danger fs-14 text-white" title="Delete">
                            <i class="fe fe-trash"></i>
                        </a>
                    </div>';
                })
                ->rawColumns(['action', 'reviewer', 'status', 'rating'])
                ->make();
        }
        return view('backend.layouts.review.index');
    }

    public function create()
    {
        $properties = Property::select('property_id', 'title')->get();
        return view('backend.layouts.review.create', compact('properties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id'  => 'required|exists:properties,property_id',
            'name'         => 'required|string|max:80',
            'rating_stars' => 'required|integer|min:1|max:5',
            'details'      => 'required|string',
            'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = [
            'property_id'  => $validated['property_id'],
            'name'         => $validated['name'],
            'rating_stars' => $validated['rating_stars'],
            'details'      => $validated['details'],
            'status'       => 'active',
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'review_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(storage_path('app/public/uploads/reviews'), $filename);
            $data['avatar'] = $filename;
        }

        Review::create($data);

        return redirect()->route('review.index')->with('t-success', 'Review created successfully!');
    }

    public function edit($id)
    {
        $data = Review::with(['property'])->findOrFail($id);
        return view('backend.layouts.review.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $id = $request->route('id') ?? $request->id;
        $review = Review::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'required|string|max:80',
            'rating_stars' => 'required|integer|min:1|max:5',
            'details'      => 'required|string',
            'status'       => 'required|in:active,inactive',
            'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $review->name         = $validated['name'];
        $review->rating_stars = $validated['rating_stars'];
        $review->details      = $validated['details'];
        $review->status       = $validated['status'];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($review->avatar && file_exists(storage_path('app/public/' . ltrim('uploads/reviews/' . $review->avatar, '/')))) {
                unlink(storage_path('app/public/' . ltrim('uploads/reviews/' . $review->avatar, '/')));
            }
            $file = $request->file('avatar');
            $filename = 'review_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(storage_path('app/public/uploads/reviews'), $filename);
            $review->avatar = $filename;
        }

        $review->save();

        return redirect()->route('review.index')->with('t-success', 'Review updated successfully!');
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        // Delete avatar file
        if ($review->avatar && file_exists(storage_path('app/public/' . ltrim('uploads/reviews/' . $review->avatar, '/')))) {
            unlink(storage_path('app/public/' . ltrim('uploads/reviews/' . $review->avatar, '/')));
        }

        $review->delete();

        return response()->json([
            't-success' => true,
            'message' => 'Review deleted successfully'
        ], 200);
    }
}
