<?php

use App\Http\Controllers\API\AboutUs\AboutUsController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\ProfileUpdateController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\SocialLoginController;
use App\Http\Controllers\API\Blog\BlogController;
use App\Http\Controllers\API\Blog\CategoryController;
use App\Http\Controllers\API\Blog\TagController;
use App\Http\Controllers\API\Calculation\CalculationController;
use App\Http\Controllers\API\DynamicPage\DynamicPageController;
use App\Http\Controllers\API\Faq\FaqController;
use App\Http\Controllers\API\Inquiry\InquiryController;
use App\Http\Controllers\API\LocationApiController;
use App\Http\Controllers\API\Property\PropertyApiController;
use App\Http\Controllers\API\Rate\RateController as RateApiController;
use App\Http\Controllers\API\Review\ReviewController;
use App\Http\Controllers\API\Support\SupportController;
use App\Http\Controllers\API\SystemSetting\SystemSettingController;
use App\Http\Controllers\API\Destination\DestinationApiController;
use App\Http\Controllers\API\PropertyCategory\PropertyCategoryApiController;
use App\Http\Controllers\API\Property\PropertyTypeApiController;
use Illuminate\Support\Facades\Route;

// Location API routes (cascading selects)
Route::get('/location/countries', [LocationApiController::class, 'countries']);
Route::get('/location/states/{country_id}', [LocationApiController::class, 'states']);
Route::get('/location/cities/{state_id}', [LocationApiController::class, 'cities']);
Route::get('/location/find-country', [LocationApiController::class, 'findCountryByName']);
Route::get('/location/find-state', [LocationApiController::class, 'findStateByName']);
Route::get('/location/find-city', [LocationApiController::class, 'findCityByName']);


// Destination API routes: specific routes before parameterized catch-alls.
Route::get('/destinations', [DestinationApiController::class, 'cities']);
Route::get('/destinations/{city}/{area}', [DestinationApiController::class, 'areaDetail']);
Route::get('/destinations/{city}', [DestinationApiController::class, 'cityDetail']);

// Property routes: specific named routes must come before parameterized {id} routes.
Route::post('/properties/search', [PropertyApiController::class, 'search']);
Route::get('/properties/explore/{property_category_id}', [PropertyApiController::class, 'exploreByCategory']);
Route::post('/properties/explore/popular-nearby', [PropertyApiController::class, 'explorePopularNearby']);
Route::get('/properties', [PropertyApiController::class, 'index']);
Route::get('/properties/{id}', [PropertyApiController::class, 'show']);

// Property Category API routes
Route::get('/property-categories', [PropertyCategoryApiController::class, 'index']);
Route::get('/property-categories/{id}', [PropertyCategoryApiController::class, 'show']);

// Property Type API routes
Route::get('/property-types', [PropertyTypeApiController::class, 'index']);


Route::post('/properties/calculate', [CalculationController::class, 'calculate']);

// Rate routes
Route::get('/rates', [RateApiController::class, 'index']);
Route::get('/rates/{property_id}', [RateApiController::class, 'propertyRates']);


// Route for System Setting
Route::get('/system-setting', [SystemSettingController::class, 'systemSetting']);
Route::get('/about-us', [AboutUsController::class, 'index']);

// Social login disabled
Route::post('/social/login', function () {
    return response()->json(['status' => false, 'message' => 'Social login is disabled.'], 403);
});

// Dynamic Pages routes
Route::get('/list', [DynamicPageController::class, 'list']);
Route::post('/show/{id}', [DynamicPageController::class, 'show']);

// Support routes disabled (prevents automated bot support emails)
Route::post('/support/contact', function () {
    return response()->json(['status' => false, 'message' => 'Support contact form is disabled. Please use property inquiry form.'], 403);
});

// Inquiry routes - ACTIVE for property manager inquiries
Route::post('/inquiry/contact', [InquiryController::class, 'inquiry']);


Route::middleware(['guest:sanctum'])->group(function () {

    //  Public Authentication & Registration disabled
    Route::post('login', function () {
        return response()->json(['status' => false, 'message' => 'Public user login is disabled. Please use property inquiry form.'], 403);
    });
    Route::post('register', function () {
        return response()->json(['status' => false, 'message' => 'Public user registration is disabled.'], 403);
    });
    Route::post('/email/verify/resend', function () {
        return response()->json(['status' => false, 'message' => 'Email verification is disabled.'], 403);
    });
    Route::post('/email/verify', function () {
        return response()->json(['status' => false, 'message' => 'Email verification is disabled.'], 403);
    });
    Route::post('/password/forgot', function () {
        return response()->json(['status' => false, 'message' => 'Password reset is disabled.'], 403);
    });
    Route::post('/password/forgot/verify', function () {
        return response()->json(['status' => false, 'message' => 'Password reset is disabled.'], 403);
    });
    Route::post('/password/reset', function () {
        return response()->json(['status' => false, 'message' => 'Password reset is disabled.'], 403);
    });
});

Route::group(['middleware' => 'auth:sanctum'], function ($router) {
    // common routes
    Route::get('/profile', [LoginController::class, 'userDetails']);
    Route::post('/logout', [LoginController::class, 'logout']);

    // profile update routes
    Route::post('/profile/update', [ProfileUpdateController::class, 'updateDetails']);
    Route::post('/profile/change-password', [ProfileUpdateController::class, 'changePassword']);
    Route::post('/profile/delete-account', [ProfileUpdateController::class, 'accountDelete']);


    // faq related controller
    Route::get('/faq', [FaqController::class, 'index']);
});

// Review routes (read-only for customers)
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/{id}', [ReviewController::class, 'show']);

// Blog routes
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/blog/{id}', [BlogController::class, 'show']);

// Tag routes
Route::get('/tags', [TagController::class, 'index']);
Route::get('/tags/{id}', [TagController::class, 'show']);


Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
