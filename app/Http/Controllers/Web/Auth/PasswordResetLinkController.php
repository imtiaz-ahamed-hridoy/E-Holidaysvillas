<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;


class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.layouts.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);


        try {
            // We will send the password reset link to this user. Once we have attempted
            // to send the link, we will examine the response then see the message we
            // need to show to the user. Finally, we'll send out a proper response.
            $status = Password::sendResetLink(
                $request->only('email')
            );

            // Handle the response status
            return $status === Password::RESET_LINK_SENT
                ? back()->with('status', __($status))  // Success response
                : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);  // Error response

        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            Log::error('Failed to send password reset link: ' . $e->getMessage());

            // Return an error message to the user
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'An error occurred while sending the password reset link. Please try again later.']);
        }
    }
}
