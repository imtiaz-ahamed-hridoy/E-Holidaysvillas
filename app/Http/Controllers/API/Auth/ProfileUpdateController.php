<?php

namespace App\Http\Controllers\API\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileUpdateController extends Controller
{
    use ApiResponse;

    /**
     * Change the authenticated user's password.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            /** @var User $user */
            $user = Auth::user();

            if (!Hash::check($request->old_password, $user->password)) {
                return $this->error('Incorrect Current Password', 402);
            }

            $user->password = Hash::make($request->password);
            $user->save();
            return $this->ok('Password changed successfully');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    /**
     * Update the authenticated user's profile details.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email"      => 'nullable|string|max:255|email|unique:users,email,' . Auth::id(),
            "first_name" => 'nullable|string|max:255',
            "last_name"  => 'nullable|string|max:255',
            "phone"      => 'nullable|string|max:20',
            "avatar"     => 'nullable|file',
            "cover_photo" => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            /** @var User $user */
            $user = Auth::user();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                if ($user->avatar) {
                    Helper::fileDelete($user->avatar);
                }
                $user->avatar = Helper::fileUpload($request->file('avatar'), 'avatar', 'avatar-' . Auth::id() . '-' . time());
            }

            // Handle cover photo upload
            if ($request->hasFile('cover_photo')) {
                if ($user->cover_photo) {
                    Helper::fileDelete($user->cover_photo);
                }
                $user->cover_photo = Helper::fileUpload($request->file('cover_photo'), 'cover_photo', 'cover-' . Auth::id() . '-' . time());
            }

            // Update user details
            $user->email      = $request->email ?? $user->email;
            $user->first_name = $request->first_name ?? $user->first_name;
            $user->last_name  = $request->last_name ?? $user->last_name;
            $user->phone      = $request->phone ?? $user->phone;

            if ($user->save()) {
                return $this->ok('Profile info updated successfully', $user->refresh());
            }

            return $this->error('Failed to update profile info', 500);
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    /**
     * Delete the authenticated user's account.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountDelete(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Delete avatar if exists
            if ($user->avatar) {
                Helper::fileDelete($user->avatar);
            }

            // Delete cover photo if exists
            if ($user->cover_photo) {
                Helper::fileDelete($user->cover_photo);
            }

            $user->delete();

            return $this->ok('Account deleted successfully');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
