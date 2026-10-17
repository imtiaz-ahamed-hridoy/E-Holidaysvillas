<?php

namespace App\Http\Controllers\Web\Backend\Faq;

use App\Models\Faq;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Faq::all();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn("status", function ($data) {
                    $badgeClass = $data->status === 'active' ? 'bg-success' : 'bg-danger';
                    $statusText = $data->status === 'active' ? 'Active' : 'Inactive';
                    // We can also make it clickable like other modules if needed, but for now just align the colors.
                    return '<span class="badge ' . $badgeClass . '">' . $statusText . '</span>';
                })
                ->addColumn('action', function ($data) {
                    $buttons = '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">';

                    // Edit button
                    $buttons .= '<a href="' . route('faq.edit', ['id' => $data->faq_id]) . '" type="button" class="btn btn-primary fs-14 text-white edit-icn" title="Edit">
                    <i class="fe fe-edit"></i>
                    </a>';
                    $buttons .= '<a href="#" type="button" onclick="showDeleteConfirm(' . $data->faq_id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                    <i class="fe fe-trash"></i>
                    </a>';
                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['action', 'status'])
                ->make();
        }
        return view('backend.layouts.faq.index');
    }

    public function create()
    {
        return view('backend.layouts.faq.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required',
            'answer' => 'required',
            'status' => 'required|in:active,inactive',
        ]);

        Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'status' => $request->status,
        ]);

        return redirect()->route('faq.index')->with('t-success', 'Faq saved successfully!');
    }

    public function show($id)
    {
        $data = Faq::findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }

        return redirect()->route('faq.edit', ['id' => $id]);
    }

    public function edit($id)
    {
        $data = Faq::findOrFail($id);

        return view('backend.layouts.faq.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'question' => 'required',
            'answer' => 'required',
            'status' => 'required|in:active,inactive',
        ]);
        $data = Faq::findOrFail($id);
        $data->update($request->all());

        return redirect()->route('faq.index')->with('t-success', 'FAQ updated successfully!');
    }

    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);
        if ($faq) {
            $faq->delete();
        }
        return response()->json([
            't-success' => true,
            'message' => 'Data deleted successfully'
        ], 200);
    }
}
