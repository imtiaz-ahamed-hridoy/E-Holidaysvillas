<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\UserController;
use App\Http\Controllers\Web\Backend\Faq\FaqController;
use App\Http\Controllers\Web\Backend\Blog\TagController;
use App\Http\Controllers\Web\Backend\Blog\BlogController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\Dynamic\DynamicPage;
use App\Http\Controllers\Web\Backend\Rate\RateController;
use App\Http\Controllers\Web\Backend\Blog\CategoryController;
use App\Http\Controllers\Web\Backend\Review\ReviewController;
use App\Http\Controllers\Web\Backend\Support\ContactController;
use App\Http\Controllers\Web\Backend\Inquiry\InquiryController;
use App\Http\Controllers\Web\Backend\UnitType\UnitTypeController;
use App\Http\Controllers\Web\Backend\Property\CurrencyController;
use App\Http\Controllers\Web\Backend\Property\PropertyTypeController;
use App\Http\Controllers\Web\Backend\Property\PropertyController;
use App\Http\Controllers\Web\Backend\Property\AddonController;
use App\Http\Controllers\Web\Backend\Property\LegacyImportController;
use App\Http\Controllers\Web\Backend\Property\DiscountController;
use App\Http\Controllers\Web\Backend\Amenity\AmenityController;
use App\Http\Controllers\Web\Backend\Location\LocationController;
use App\Http\Controllers\Web\Backend\PropertyCategory\PropertyCategoryController;
use App\Http\Controllers\Web\Backend\Property\PropertyAvailabilityController;
use App\Http\Controllers\Web\Backend\Booking\BookingController;

// Route for Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Route for Users Page
Route::get('/user', [UserController::class, 'index'])->name('user.index');
Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
Route::post('/user/store', [UserController::class, 'store'])->name('user.store');
Route::get('/user/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
Route::patch('/user/update/{id}', [UserController::class, 'update'])->name('user.update');
Route::delete('/user/destroy/{id}', [UserController::class, 'destroy'])->name('user.destroy');

// Route for Dynamic Page
Route::get('/page', [DynamicPage::class, 'index'])->name('page.index');
Route::get('/page/create', [DynamicPage::class, 'create'])->name('page.create');
Route::post('/page/store', [DynamicPage::class, 'store'])->name('page.store');
Route::get('/page/edit/{id}', [DynamicPage::class, 'edit'])->name('page.edit');
Route::patch('/page/update/{id}', [DynamicPage::class, 'update'])->name('page.update');
Route::delete('/page/destroy/{id}', [DynamicPage::class, 'destroy'])->name('page.destroy');

//faq
Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
Route::get('/faq/create', [FaqController::class, 'create'])->name('faq.create');
Route::post('/faq/create', [FaqController::class, 'store'])->name('faq.store');
Route::get('/faq/{id}', [FaqController::class, 'show'])->name('faq.show');
Route::get('/faq/edit/{id}', [FaqController::class, 'edit'])->name('faq.edit');
Route::put('/faq/update/{id}', [FaqController::class, 'update'])->name('faq.update');
Route::delete('/faq/delete/{id}', [FaqController::class, 'destroy'])->name('faq.destroy');


//review
Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
Route::get('/review/create', [ReviewController::class, 'create'])->name('review.create');
Route::post('/review/store', [ReviewController::class, 'store'])->name('review.store');
Route::get('/review/edit/{id}', [ReviewController::class, 'edit'])->name('review.edit');
Route::put('/review/update/{id}', [ReviewController::class, 'update'])->name('review.update');
Route::delete('/review/delete/{id}', [ReviewController::class, 'destroy'])->name('review.destroy');

// Blog Routes
Route::prefix('blog')->name('blog.')->group(function () {
    // Categories
    Route::get('/category', [CategoryController::class, 'index'])->name('category.index');
    Route::get('/category/create', [CategoryController::class, 'create'])->name('category.create');
    Route::post('/category/store', [CategoryController::class, 'store'])->name('category.store');
    Route::get('/category/edit/{id}', [CategoryController::class, 'edit'])->name('category.edit');
    Route::put('/category/update/{id}', [CategoryController::class, 'update'])->name('category.update');
    Route::delete('/category/delete/{id}', [CategoryController::class, 'destroy'])->name('category.destroy');

    // Tags
    Route::get('/tag', [TagController::class, 'index'])->name('tag.index');
    Route::get('/tag/create', [TagController::class, 'create'])->name('tag.create');
    Route::post('/tag/store', [TagController::class, 'store'])->name('tag.store');
    Route::get('/tag/edit/{id}', [TagController::class, 'edit'])->name('tag.edit');
    Route::put('/tag/update/{id}', [TagController::class, 'update'])->name('tag.update');
    Route::delete('/tag/delete/{id}', [TagController::class, 'destroy'])->name('tag.destroy');

    // Posts (Blog)
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/create', [BlogController::class, 'create'])->name('create');
    Route::post('/store', [BlogController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [BlogController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [BlogController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [BlogController::class, 'destroy'])->name('destroy');
});

// Support Contact Routes (read-only)
Route::get('/support-contact', [ContactController::class, 'index'])->name('support.contact.index');
Route::get('/support-contact/{id}', [ContactController::class, 'show'])->name('support.contact.show');

// Inquiry Routes (read-only)
Route::get('/inquiry', [InquiryController::class, 'index'])->name('inquiry.index');
Route::get('/inquiry/{id}', [InquiryController::class, 'show'])->name('inquiry.show');

// Property Type Routes
Route::prefix('property-type')->name('property-type.')->group(function () {
    Route::get('/', [PropertyTypeController::class, 'index'])->name('index');
    Route::get('/create', [PropertyTypeController::class, 'create'])->name('create');
    Route::post('/store', [PropertyTypeController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [PropertyTypeController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [PropertyTypeController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [PropertyTypeController::class, 'destroy'])->name('destroy');
    Route::get('/status/{id}', [PropertyTypeController::class, 'status'])->name('status');
});

// Currency Routes
Route::prefix('currency')->name('currency.')->group(function () {
    Route::get('/', [CurrencyController::class, 'index'])->name('index');
    Route::get('/create', [CurrencyController::class, 'create'])->name('create');
    Route::post('/store', [CurrencyController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [CurrencyController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [CurrencyController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [CurrencyController::class, 'destroy'])->name('destroy');
    Route::get('/status/{id}', [CurrencyController::class, 'status'])->name('status');
});

// Unit Type Routes
Route::prefix('unit-type')->name('unit-type.')->group(function () {
    Route::get('/', [UnitTypeController::class, 'index'])->name('index');
    Route::get('/create', [UnitTypeController::class, 'create'])->name('create');
    Route::post('/store', [UnitTypeController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [UnitTypeController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [UnitTypeController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [UnitTypeController::class, 'destroy'])->name('destroy');
    Route::get('/status/{id}', [UnitTypeController::class, 'status'])->name('status');
});

// Property Category Routes
Route::prefix('property-category')->name('property-category.')->group(function () {
    Route::get('/', [PropertyCategoryController::class, 'index'])->name('index');
    Route::get('/create', [PropertyCategoryController::class, 'create'])->name('create');
    Route::post('/store', [PropertyCategoryController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [PropertyCategoryController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [PropertyCategoryController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [PropertyCategoryController::class, 'destroy'])->name('destroy');
    Route::get('/status/{id}', [PropertyCategoryController::class, 'status'])->name('status');
});
// Rate Routes
Route::prefix('rate')->name('rate.')->group(function () {
    Route::get('/', [RateController::class, 'index'])->name('index');
    Route::get('/create', [RateController::class, 'create'])->name('create');
    Route::post('/store', [RateController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [RateController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [RateController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [RateController::class, 'destroy'])->name('destroy');
    Route::get('/status/{id}', [RateController::class, 'status'])->name('status');
});

// Amenity Routes
Route::prefix('amenities')->name('amenity.')->group(function () {
    Route::get('/', [AmenityController::class, 'index'])->name('index');
    Route::get('/create', [AmenityController::class, 'create'])->name('create');
    Route::post('/store', [AmenityController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [AmenityController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [AmenityController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [AmenityController::class, 'destroy'])->name('destroy');
    Route::get('/status/{id}', [AmenityController::class, 'status'])->name('status');
});

//property Routes
Route::prefix('property')->name('property.')->group(function () {

    Route::get('/', [PropertyController::class, 'index'])->name('index');

    Route::get('/create', [PropertyController::class, 'create'])->name('create');

    Route::post('/store', [PropertyController::class, 'store'])->name('store');

    Route::get('/show/{id}', [PropertyController::class, 'show'])->name('show');

    Route::get('/edit/{id}', [PropertyController::class, 'edit'])->name('edit');

    Route::put('/update/{id}', [PropertyController::class, 'update'])->name('update');

    Route::delete('/delete/{id}', [PropertyController::class, 'destroy'])->name('destroy');

    Route::post('/delete-photos', [PropertyController::class, 'deletePhotos'])->name('delete-photos');

    Route::get('/status/{id}', [PropertyController::class, 'status'])->name('status');
});

Route::prefix('property-import')->name('property-import.')->group(function () {
    Route::get('/', [LegacyImportController::class, 'index'])->name('index');
    Route::post('/store', [LegacyImportController::class, 'store'])->name('store');
    Route::get('/progress', [LegacyImportController::class, 'progress'])->name('progress');
});

// Property Availability Routes
Route::prefix('property-availability')->name('property-availability.')->group(function () {
    Route::get('/', [PropertyAvailabilityController::class, 'index'])->name('index');
    Route::get('/{id}', [PropertyAvailabilityController::class, 'show'])->name('show');
    Route::get('/{id}/events', [PropertyAvailabilityController::class, 'fetchEvents'])->name('events');
    Route::get('/{id}/rates', [PropertyAvailabilityController::class, 'propertyRates'])->name('rates');
    Route::post('/store', [PropertyAvailabilityController::class, 'store'])->name('store');
    Route::post('/update-customer-status/{id}', [PropertyAvailabilityController::class, 'updateCustomerStatus'])->name('update-customer-status');
    Route::post('/calculate-price', [PropertyAvailabilityController::class, 'calculatePrice'])->name('calculate-price');
});

// Location Routes
Route::get('/locations/children/{id}', [LocationController::class, 'getChildren'])->name('locations.children');

// Addon Routes
Route::prefix('addons')->name('addons.')->group(function () {
    Route::get('/', [AddonController::class, 'index'])->name('index');
    Route::get('/create', [AddonController::class, 'create'])->name('create');
    Route::post('/store', [AddonController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [AddonController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [AddonController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [AddonController::class, 'destroy'])->name('destroy');
    Route::get('/status/{id}', [AddonController::class, 'status'])->name('status');
});

// Discount Routes
Route::prefix('discounts')->name('discounts.')->group(function () {
    Route::get('/', [DiscountController::class, 'index'])->name('index');
    Route::get('/create', [DiscountController::class, 'create'])->name('create');
    Route::post('/store', [DiscountController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [DiscountController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [DiscountController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [DiscountController::class, 'destroy'])->name('destroy');
    Route::get('/status/{id}', [DiscountController::class, 'status'])->name('status');
});

// Booking Management Routes
Route::prefix('bookings')->name('bookings.')->group(function () {
    Route::get('/', [BookingController::class, 'index'])->name('index');
    Route::get('/{id}', [BookingController::class, 'show'])->name('show');
    Route::put('/{id}', [BookingController::class, 'update'])->name('update');
    Route::post('/{id}/payment', [BookingController::class, 'recordPayment'])->name('record-payment');
    Route::delete('/{id}', [BookingController::class, 'destroy'])->name('destroy');
});



