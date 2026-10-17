<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AboutUs extends Model
{
    use HasFactory;

    protected $table = 'about_us';

    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'intro_mission',
        'intro_vision',
        'intro_story',
        'intro_image',
        'travel_title',
        'travel_text',
        'travel_images',
        'why_choose_us',
    ];

    protected $hidden = [
        'travel_btn_text',
        'travel_btn_url',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'travel_images' => 'array',
        'why_choose_us' => 'array',
    ];

    // Accessor for Hero Image URL
    public function getHeroImageAttribute($value)
    {
        return \App\Helpers\Helper::imageUrl($value);
    }

    // Accessor for Intro Image URL
    public function getIntroImageAttribute($value)
    {
        return \App\Helpers\Helper::imageUrl($value);
    }

    // Accessor for Travel Images URLs
    public function getTravelImagesAttribute($value)
    {
        $images = is_string($value) ? json_decode($value, true) : $value;
        if (is_array($images)) {
            return array_map(function ($image) {
                return \App\Helpers\Helper::imageUrl($image);
            }, $images);
        }
        return $images ?? [];
    }

    // Accessor for Why Choose Us Icon URLs
    public function getWhyChooseUsAttribute($value)
    {
        $features = is_string($value) ? json_decode($value, true) : $value;
        if (is_array($features)) {
            foreach ($features as &$feature) {
                if (($feature['icon_type'] ?? '') === 'file' && !empty($feature['icon_content'])) {
                    $feature['icon_content'] = \App\Helpers\Helper::imageUrl($feature['icon_content']);
                }
            }
        }
        return $features ?? [];
    }
}
