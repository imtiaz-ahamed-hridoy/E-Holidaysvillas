<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile settings page.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $user = User::find($request->id ?? auth()->user()->id);
        return view('backend.layouts.settings.profile_settings', compact('user'));
    }

    /**
     * Update the user's profile.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function UpdateProfile(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|max:100|min:2',
            'last_name'  => 'nullable|max:100|min:2',
            'email'      => 'nullable|email|unique:users,email,' . auth()->user()->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            $user             = User::find(auth()->user()->id);
            $user->first_name = $request->first_name;
            $user->last_name  = $request->last_name;
            $user->email      = $request->email;

            $user->save();
            return redirect()->back()->with('t-success', 'Profile updated successfully');
        } catch (Exception) {
            return redirect()->back()->with('t-error', 'Something went wrong');
        }
    }

    /**
     * Update the user's password.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function UpdatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password'     => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            $user = Auth::user();
            if (Hash::check($request->old_password, $user->password)) {
                $user->password = Hash::make($request->password);
                $user->save();

                return redirect()->back()->with('t-success', 'Password updated successfully');
            } else {
                return redirect()->back()->with('t-error', 'Current password is incorrect');
            }
        } catch (Exception) {
            return redirect()->back()->with('t-error', 'Something went wrong');
        }
    }

    /**
     * Update the user's profile picture.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function UpdateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|file',
        ]);

        try {
            $user = Auth::user();

            // Handle avatar upload
            if ($request->hasFile('profile_picture')) {
                $avatar = $request->file('profile_picture');
                $avatarName = time() . '_' . $avatar->getClientOriginalName();
                $avatarPath = $avatar->storeAs('uploads/avatar', $avatarName, 'public');

                // Update user's avatar path
                $user->avatar = $avatarPath;
                $user->save();

                return response()->json([
                    'success'   => true,
                    'image_url' => \App\Helpers\Helper::imageUrl($avatarPath),
                ]);
            } else {
                throw new \Exception('No image was uploaded.');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
