<?php

namespace App\Http\Controllers\Web\Backend\Support;

use App\Http\Controllers\Controller;
use App\Models\ContactSupport;
use App\Mail\SupportReplyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\DataTables;

class ContactController extends Controller
{
    /**
     * Display a property of the contact support messages.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ContactSupport::latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($data) {
                    if ($data->is_replied) {
                        return '<span class="badge bg-success">Replied</span>';
                    }
                    return '<span class="badge bg-warning">Pending</span>';
                })
                ->addColumn('priority', function ($data) {
                    if ($data->priority === 'high') {
                        return '<span class="badge bg-danger">High</span>';
                    } elseif ($data->priority === 'medium') {
                        return '<span class="badge bg-warning">Medium</span>';
                    }
                    return '<span class="badge bg-info">Low</span>';
                })
                ->addColumn('action', function ($data) {
                    $buttons = '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">';

                    // View button (read-only)
                    $buttons .= '<a href="' . route('support.contact.show', $data->id) . '" class="btn btn-info fs-14 text-white" title="View">
                    <i class="fe fe-eye"></i>
                    </a>';

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['status', 'priority', 'action'])
                ->make();
        }
        return view('backend.layouts.support.index');
    }

    /**
     * Display the specified contact support message.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $data = ContactSupport::findOrFail($id);
        return view('backend.layouts.support.show', compact('data'));
    }

}
