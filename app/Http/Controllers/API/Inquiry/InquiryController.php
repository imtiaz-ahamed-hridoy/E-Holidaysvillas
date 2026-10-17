<?php

namespace App\Http\Controllers\API\Inquiry;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InquiryController extends Controller
{
    use ApiResponse;

    /**
     * POST /inquiry/store
     */
    public function inquiry(Request $request)
    {
        $validated = $request->validate([
            'property_id'   => ['required', 'exists:properties,property_id'],
            'name'          => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'max:255'],
            'phone'         => ['required', 'string', 'max:50'],
            'checkin_date'  => ['required', 'date'],
            'checkout_date' => ['required', 'date', 'after:checkin_date'],
            'no_of_guests'  => ['required', 'integer', 'min:1'],
            'message'       => ['required', 'string', 'max:2000'],
        ]);

        // Automatically associate user if logged in
        $validated['user_id'] = Auth::id();
        $validated['status'] = 'pending';
        $validated['is_replied'] = 'no';

        $inquiry = Inquiry::create($validated);

        try {
            $adminEmail = \App\Models\SystemSetting::first()->email ?? 'admin@example.com';
            \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\AdminInquiryMail($inquiry));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send inquiry admin email: ' . $e->getMessage());
        }

        return $this->success(
            'Inquiry submitted successfully',
            [
                'inquiry_id' => $inquiry->inquiry_id,
                'property_id' => $inquiry->property_id,
                'name' => $inquiry->name,
            ],
            201
        );
    }
}

