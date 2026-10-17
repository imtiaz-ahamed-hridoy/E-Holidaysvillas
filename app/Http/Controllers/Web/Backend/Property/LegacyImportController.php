<?php

namespace App\Http\Controllers\Web\Backend\Property;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessLegacyImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LegacyImportController extends Controller
{
    public function index(): View
    {
        return view('backend.layouts.property.import');
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'sql_file' => 'required|file|max:102400',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $uploadedFile = $request->file('sql_file');

        // Validate file extension manually (Laravel mimes:sql doesn't work reliably)
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        if (!in_array($extension, ['sql', 'txt'])) {
            return redirect()->back()->with('t-error', 'Invalid file type. Please upload a .sql or .txt file.');
        }

        // Move file to a known temp path so the queue job can access it later
        $tempFileName = uniqid('legacy_', true) . '.sql';
        $tempDir = storage_path('app/tmp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $tempPath = $tempDir . DIRECTORY_SEPARATOR . $tempFileName;
        $uploadedFile->move($tempDir, $tempFileName);

        // Clear any old progress data
        Cache::forget('legacy_import_progress');

        // Dispatch the import job to the queue
        ProcessLegacyImport::dispatch($tempPath);

        return redirect()->route('property-import.index')
            ->with('t-success', 'Import has been queued. You can track the progress below.');
    }

    /**
     * AJAX endpoint for the frontend to poll import progress.
     */
    public function progress(): JsonResponse
    {
        $progress = Cache::get('legacy_import_progress', [
            'status' => 'idle',
            'percent' => 0,
            'message' => 'No import running.',
        ]);

        return response()->json($progress);
    }
}
