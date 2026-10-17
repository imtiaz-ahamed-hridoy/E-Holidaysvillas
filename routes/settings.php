<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Stripe\StripeKeyController;
use App\Http\Controllers\Web\Backend\Settings\MailSettingController;
use App\Http\Controllers\Web\Backend\Settings\SystemSettingController;
use App\Http\Controllers\Web\Backend\Settings\AboutUsController;


Route::middleware('auth')->group(function () {

    // Route for Profile Settings

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile.setting');
        Route::patch('/update-profile', 'UpdateProfile')->name('update.profile');
        Route::put('/update-profile-password', 'UpdatePassword')->name('update.Password');
        Route::post('/update-profile-picture', 'UpdateProfilePicture')->name('update.profile.picture');
    });

    // Route for System Settings
    Route::controller(SystemSettingController::class)->group(function () {
        Route::get('/system-setting', 'index')->name('system.index');
        Route::patch('/system-setting', 'update')->name('system.update');
    });

    // Route for Mail Settings
    Route::controller(MailSettingController::class)->group(function () {
        Route::get('/mail-setting', 'index')->name('mail.setting');
        Route::patch('/mail-setting', 'update')->name('mail.update');
    });

    // Route for Stripe Settings
    Route::controller(StripeKeyController::class)->group(function () {
        Route::get('/stripe/edit', 'edit')->name('stripe.edit');
        Route::put('/stripe/update', 'update')->name('stripe.update');
    });

    // Route for About Us Settings
    Route::controller(AboutUsController::class)->group(function () {
        Route::get('/about-us', 'edit')->name('about_us.edit');
        Route::post('/about-us', 'update')->name('about_us.update');
    });
});
