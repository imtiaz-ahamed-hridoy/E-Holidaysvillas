<?php

namespace App\Http\Controllers\API\Support;

use App\Http\Controllers\Controller;
use App\Models\{ContactSupport};
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    use ApiResponse;

    /**
     * POST /support/contact
     */
    public function contact(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'subject' => ['required', 'in:booking,support,partnership,other'],
            'email'   => ['required', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        if (auth('sanctum')->check()) {
            $validated['user_id'] = auth('sanctum')->id();
        }

        $support = ContactSupport::create($validated);

        try {
            $adminEmail = \App\Models\SystemSetting::first()->email ?? 'admin@example.com';
            \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\AdminSupportMail($support));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send support admin email: ' . $e->getMessage());
        }

        return $this->success(
            'Support request submitted successfully',
            [
                'id'      => $support->id,
                'name'    => $support->name,
                'subject' => $support->subject,
                'email'   => $support->email,
                'message' => $support->message,
            ],
            201
        );
    }
}

