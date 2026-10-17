<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use ApiResponse;

    /**
     * Handle user registration.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors()->toArray());
        }

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'User',
            ]);

            // Send OTP
            $otp = $this->send_otp($user);

            if (!$otp) {
                throw new \Exception('Failed to generate OTP.');
            }

            DB::commit();
            return $this->success('Registered successfully. Please verify your email.', [
                'email' => $user->email,
            ], 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage(), 500);
        }
    }

    /**
     * Generate and send OTP to user email.
     *
     * @param User $user
     * @param string $mailType
     * @return mixed
     */
    public function send_otp(User $user, string $mailType = 'verify')
    {
        $otp  = (new Otp)->generate($user->email, 'numeric', 6, 60);
        $message = $mailType === 'verify' ? 'Verify Your Email Address' : 'Reset Your Password';
        Mail::to($user->email)->send(new \App\Mail\OTP($otp->token, $user, $message, $mailType));
        return $otp;
    }

    /**
     * Resend OTP to user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resend_otp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors()->toArray());
        }

        try {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $otp = $this->send_otp($user);
                return $this->success('OTP sent successfully.');
            } else {
                return $this->error('User not found', 404);
            }
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    /**
     * Verify user email via OTP.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verify_otp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'otp' => 'required|string|digits:6',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors()->toArray());
        }

        try {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->error('User not found', 404);
            }

            if ($user->email_verified_at !== null) {
                return $this->error('Email already verified', 400);
            }

            $verify = (new Otp)->validate($request->email, $request->otp);
            if ($verify->status) {
                $user->email_verified_at = now();
                $user->save();

                $token = $user->createToken('AuthToken')->plainTextToken;

                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'message' => 'Email verified successfully',
                    'token_type' => 'Bearer',
                    'token' => $token,
                    'data' => $user
                ], 200);
            } else {
                return $this->error($verify->message, 400);
            }
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    /**
     * Initiation of forgot password process.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgot_password(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['email' => 'required|email']);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors()->toArray());
        }

        try {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->error('User not found', 404);
            }
            $otp = $this->send_otp($user, 'forget');
            return $this->success('OTP sent successfully.');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    /**
     * Verify forgot password OTP and return a reset token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgot_verify_otp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'otp' => 'required|string|digits:6',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors()->toArray());
        }

        $verify = (new Otp)->validate($request->email, $request->otp);
        if ($verify->status) {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->error('User not found', 404);
            }
            $user->reset_code = Str::random(40);
            $user->reset_code_expires_at = Carbon::now()->addHours(1);
            $user->save();
            return $this->success('OTP verified successfully', [
                'token' => $user->reset_code,
            ]);
        } else {
            return $this->error($verify->message, 400);
        }
    }

    /**
     * Reset user password using reset token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reset_password(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', 422, $validator->errors()->toArray());
        }

        try {
            $user = User::where('reset_code', $request->token)->first();

            if (!$user) {
                return $this->error('Invalid Token', 404);
            }

            if ($user->reset_code_expires_at < Carbon::now()) {
                return $this->error('Token expired', 400);
            }

            $user->password = Hash::make($request->password);
            $user->reset_code = null;
            $user->reset_code_expires_at = null;
            $user->save();

            return $this->ok('Password reset successfully');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
