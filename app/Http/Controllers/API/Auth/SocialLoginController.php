<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    use ApiResponse;

    /**
     * Handle social login authentication.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function socialLogin(Request $request): JsonResponse
    {
        $request->validate([
            'provider_id' => 'required|string|in:google',
            'token' => 'required|string',
        ]);

        try {
            if ($request->provider_id === 'google') {
                $socialUser = Socialite::driver('google')->stateless()->userFromToken($request->token);
                // } elseif ($request->provider_id === 'apple') {
                // $socialUser = Socialite::driver('apple')->stateless()->userFromToken($request->token);
            } else {
                return $this->error('Unsupported provider', 422);
            }

            if ($socialUser) {
                $user = User::where('email', $socialUser->email)->first();

                if (!$user) {
                    $fullName  = $socialUser->getName();
                    $nameParts = explode(' ', $fullName, 2);
                    $firstName = $nameParts[0] ?? 'User';
                    $lastName  = $nameParts[1] ?? '';

                    $user = User::create([
                        'provider_id'       => $request->provider_id,
                        'first_name'        => $firstName,
                        'last_name'         => $lastName,
                        'email'             => $socialUser->email,
                        'password'          => Hash::make(Str::random(16)),
                        'avatar'            => $socialUser->getAvatar(),
                        'email_verified_at' => now(),
                    ]);
                }

                Auth::login($user);
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

            return $this->error('Invalid or Expired Token', 401);
        } catch (Exception $e) {
            Log::error('Social login failed: ' . $e->getMessage());
            return $this->error('Social authentication failed', 500);
        }
    }

    // /**
    //  * Placeholder/redirect for Apple callback if needed.
    //  *
    //  * @return JsonResponse
    //  */
    // public function redirectCallbackApple(): JsonResponse
    // {
    //     return $this->ok('Apple authentication processed.');
    // }
}
