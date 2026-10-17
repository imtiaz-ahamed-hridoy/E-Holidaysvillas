<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    public function edit()
    {
        $aboutUs = AboutUs::find(1);
        return view('backend.layouts.settings.about_us', compact('aboutUs'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'hero_title'     => 'nullable|string|max:255',
            'hero_subtitle'  => 'nullable|string|max:255',
            'hero_image'     => 'nullable|file',
            'intro_mission'  => 'nullable|string',
            'intro_vision'   => 'nullable|string',
            'intro_story'    => 'nullable|string',
            'intro_image'    => 'nullable|file',
            'travel_title'   => 'nullable|string|max:255',
            'travel_text'    => 'nullable|string',
            'travel_image_1' => 'nullable|file',
            'travel_image_2' => 'nullable|file',
        ]);

        try {
            // Enforce single record (ID 1)
            $aboutUs = AboutUs::find(1) ?? new AboutUs();
            if (!$aboutUs->exists) {
                $aboutUs->id = 1;
            }

            // Handle Text Inputs - Use setAttribute to be safe
            $aboutUs->setAttribute('hero_title', $request->hero_title);
            $aboutUs->setAttribute('hero_subtitle', $request->hero_subtitle);
            $aboutUs->setAttribute('intro_mission', $request->intro_mission);
            $aboutUs->setAttribute('intro_vision', $request->intro_vision);
            $aboutUs->setAttribute('intro_story', $request->intro_story);
            $aboutUs->setAttribute('travel_title', $request->travel_title);
            $aboutUs->setAttribute('travel_text', $request->travel_text);

            $baseURL = url('/');

            // Handle File Uploads
            if ($request->hasFile('hero_image')) {
                Helper::fileDelete($aboutUs->getRawOriginal('hero_image'));
                $imageURL = Helper::fileUpload($request->file('hero_image'), 'about_us', 'hero_image_' . time());
                $aboutUs->setAttribute('hero_image', ltrim(str_replace($baseURL, '', $imageURL), '/'));
            }

            if ($request->hasFile('intro_image')) {
                Helper::fileDelete($aboutUs->getRawOriginal('intro_image'));
                $imageURL = Helper::fileUpload($request->file('intro_image'), 'about_us', 'intro_image_' . time());
                $aboutUs->setAttribute('intro_image', ltrim(str_replace($baseURL, '', $imageURL), '/'));
            }

            // Handle Travel Images
            $rawTravel = $aboutUs->getRawOriginal('travel_images');
            $travelImages = is_string($rawTravel) ? json_decode($rawTravel, true) : ($rawTravel ?? []);

            if ($request->hasFile('travel_image_1')) {
                if (isset($travelImages[0])) Helper::fileDelete($travelImages[0]);
                $imageURL = Helper::fileUpload($request->file('travel_image_1'), 'about_us', 'travel_image_1_' . time());
                $travelImages[0] = ltrim(str_replace($baseURL, '', $imageURL), '/');
            }

            if ($request->hasFile('travel_image_2')) {
                if (isset($travelImages[1])) Helper::fileDelete($travelImages[1]);
                $imageURL = Helper::fileUpload($request->file('travel_image_2'), 'about_us', 'travel_image_2_' . time());
                $travelImages[1] = ltrim(str_replace($baseURL, '', $imageURL), '/');
            }

            // Clean array and ensure indices
            $travelImages[0] = $travelImages[0] ?? null;
            $travelImages[1] = $travelImages[1] ?? null;
            ksort($travelImages);
            $aboutUs->setAttribute('travel_images', array_values($travelImages));

            // Handle Why Choose Us Features
            if ($request->has('why_choose_us')) {
                $rawFeatures = $aboutUs->getRawOriginal('why_choose_us');
                $existingFeatures = is_string($rawFeatures) ? json_decode($rawFeatures, true) : ($rawFeatures ?? []);

                $features = [];
                // Standardizing on 4 slots as per the view
                for ($i = 0; $i < 4; $i++) {
                    $item = $request->why_choose_us[$i] ?? null;
                    if (!$item) continue;

                    // If all fields are empty and no new file, we skip/delete the slot
                    if (empty($item['title']) && empty($item['description']) && !$request->hasFile("why_choose_us.$i.icon_file")) {
                        // Delete old file if it existed
                        if (!empty($existingFeatures[$i]['icon_content']) && ($existingFeatures[$i]['icon_type'] ?? '') === 'file') {
                            Helper::fileDelete($existingFeatures[$i]['icon_content']);
                        }
                        continue;
                    }

                    $iconType = $item['icon_type'] ?? 'file';
                    $iconContent = null;

                    if ($iconType === 'file') {
                        if ($request->hasFile("why_choose_us.$i.icon_file")) {
                            // Delete old image if it was a file
                            if (!empty($existingFeatures[$i]['icon_content']) && ($existingFeatures[$i]['icon_type'] ?? '') === 'file') {
                                Helper::fileDelete($existingFeatures[$i]['icon_content']);
                            }
                            $imageURL = Helper::fileUpload($request->file("why_choose_us.$i.icon_file"), 'about_us', "feature_icon_{$i}_" . time());
                            $iconContent = ltrim(str_replace($baseURL, '', $imageURL), '/');
                        } else {
                            // Keep existing file if it was already a file
                            $iconContent = ($existingFeatures[$i]['icon_type'] ?? '') === 'file'
                                ? ($existingFeatures[$i]['icon_content'] ?? null)
                                : null;
                        }
                    } else {
                        // Switching to HTML type: delete old file if it existed
                        if (!empty($existingFeatures[$i]['icon_content']) && ($existingFeatures[$i]['icon_type'] ?? '') === 'file') {
                            Helper::fileDelete($existingFeatures[$i]['icon_content']);
                        }
                        $iconContent = $item['icon_html'] ?? null;
                    }

                    $features[] = [
                        'title'        => $item['title'] ?? '',
                        'description'  => $item['description'] ?? '',
                        'icon_type'    => $iconType,
                        'icon_content' => $iconContent,
                    ];
                }
                $aboutUs->setAttribute('why_choose_us', $features);
            }

            $aboutUs->save();

            return redirect()->back()->with('t-success', 'About Us settings updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', 'Error saving settings: ' . $e->getMessage())->withInput();
        }
    }
}
