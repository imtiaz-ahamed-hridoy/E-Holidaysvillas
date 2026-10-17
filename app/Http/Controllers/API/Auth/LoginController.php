<?php

namespace App\Http\Controllers\API\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    use ApiResponse;

    /**
     * Handle authentication attempt.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return $this->error('The provided credentials do not match our records.', 401, [
                'email' => ['The provided credentials do not match our records.']
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->email_verified_at === null) {
            return $this->error('Email not verified.', 403);
        }

        // Handle nullable remember_token
        if ($request->boolean('remember_token')) {
            $user->setRememberToken(Str::random(60));
            $user->save();
        }

        $token = $user->createToken('AuthToken')->plainTextToken;

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Login Successful',
            'token_type' => 'Bearer',
            'token' => $token,
            'data' => $user
        ], 200);
    }

    /**
     * Log the user out (Revoke token).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            /** @var \Laravel\Sanctum\PersonalAccessToken $token */
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $token->delete();
            }
            return $this->ok('Logged out successfully.');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    /**
     * Fetch the authenticated user's details.
     *
     * @return JsonResponse
     */
    public function userDetails(): JsonResponse
    {
        return $this->ok('User details fetched successfully.', Auth::user());
    }
}
