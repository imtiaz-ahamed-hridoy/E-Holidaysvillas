<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    /**
     * Display the system settings page.
     *
     * @return View
     */
    public function index(): View
    {
        $setting = SystemSetting::latest('id')->first();
        return view('backend.layouts.settings.system_settings', compact('setting'));
    }

    /**
     * Update the system settings.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'title'                => 'nullable|string|max:255',
            'email'                => 'nullable|email|max:255',
            'number'               => 'nullable|string|max:255',
            'system_name'          => 'nullable|string|max:255',
            'address'              => 'nullable|string|max:255',
            'copyright_text'       => 'nullable|string|max:255',
            'logo'                 => 'nullable|file',
            'favicon'              => 'nullable|file',
            'description'          => 'nullable|string',
            'facebook_url'         => 'nullable|url|max:255',
            'twitter_url'          => 'nullable|url|max:255',
            'instagram_url'        => 'nullable|url|max:255',
            'linkedin_url'         => 'nullable|url|max:255',
            'youtube_url'          => 'nullable|url|max:255',
            'whatsapp_url'         => 'nullable|url|max:255',
            'telegram_url'         => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Use latest()->first() to ensure we are updating the existing record if it exists
            $setting = SystemSetting::latest('id')->first() ?? new SystemSetting();

            $setting->title          = $request->title;
            $setting->email          = $request->email;
            $setting->number         = $request->number;
            $setting->system_name    = $request->system_name;
            $setting->address        = $request->address;
            $setting->copyright_text = $request->copyright_text;
            $setting->description    = $request->description;

            $setting->facebook_url   = $request->facebook_url;
            $setting->twitter_url    = $request->twitter_url;
            $setting->instagram_url  = $request->instagram_url;
            $setting->linkedin_url   = $request->linkedin_url;
            $setting->youtube_url    = $request->youtube_url;
            $setting->whatsapp_url   = $request->whatsapp_url;
            $setting->telegram_url   = $request->telegram_url;

            // Handle Logo Upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists (handled by Observer typically, but safe to do here if not)
                if ($setting->logo && Storage::disk('public')->exists($setting->getRawOriginal('logo'))) {
                    Storage::disk('public')->delete($setting->getRawOriginal('logo'));
                }

                $logo = $request->file('logo');
                $logoName = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();
                $setting->logo = $logo->storeAs('uploads/logo', $logoName, 'public');
            }

            // Handle Favicon Upload
            if ($request->hasFile('favicon')) {
                if ($setting->favicon && Storage::disk('public')->exists($setting->getRawOriginal('favicon'))) {
                    Storage::disk('public')->delete($setting->getRawOriginal('favicon'));
                }

                $favicon = $request->file('favicon');
                $faviconName = time() . '_' . uniqid() . '.' . $favicon->getClientOriginalExtension();
                $setting->favicon = $favicon->storeAs('uploads/favicon', $faviconName, 'public');
            }

            $setting->save();

            return back()->with('t-success', 'Updated successfully');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }
}
