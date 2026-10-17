<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'email',
        'number',
        'system_name',
        'address',
        'copyright_text',
        'logo',
        'favicon',
        'description',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'linkedin_url',
        'youtube_url',
        'whatsapp_url',
        'telegram_url',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getLogoAttribute($value)
    {
        return \App\Helpers\Helper::imageUrl($value);
    }

    public function getFaviconAttribute($value)
    {
        return \App\Helpers\Helper::imageUrl($value);
    }

    protected static function booted()
    {
        static::updating(function ($systemSetting) {
            if ($systemSetting->isDirty('favicon')) {
                $oldFavicon = $systemSetting->getOriginal('favicon');
                if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                }
            }

            if ($systemSetting->isDirty('logo')) {
                $oldLogo = $systemSetting->getOriginal('logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
            }
        });
    }
}
