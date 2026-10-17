<?php

namespace App\Http\Controllers\Web\Backend\Inquiry;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Mail\InquiryReplyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\DataTables;

class InquiryController extends Controller
{
    /**
     * Display a list of inquiries.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Inquiry::with('property')->latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('property_name', function ($data) {
                    return $data->property->title ?? 'N/A';
                })
                ->addColumn('status', function ($data) {
                    $badges = [
                        'pending' => 'bg-warning',
                        'replied' => 'bg-success',
                        'spam'    => 'bg-danger',
                    ];
                    $badgeClass = $badges[$data->status] ?? 'bg-secondary';
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($data->status) . '</span>';
                })
                ->addColumn('action', function ($data) {
                    $buttons = '<div class="btn-group btn-group-sm" role="group">';
                    // View button
                    $buttons .= '<a href="' . route('inquiry.show', $data->inquiry_id) . '" class="btn btn-info fs-14 text-white" title="View">
                    <i class="fe fe-eye"></i>
                    </a>';

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['status', 'action'])
                ->make();
        }
        return view('backend.layouts.inquiry.index');
    }

    /**
     * Display the specified inquiry.
     */
    public function show($id)
    {
        $data = Inquiry::with('property')->findOrFail($id);
        return view('backend.layouts.inquiry.show', compact('data'));
    }

}
