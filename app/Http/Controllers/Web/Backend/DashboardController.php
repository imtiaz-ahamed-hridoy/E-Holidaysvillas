<?php

namespace App\Http\Controllers\Web\Backend;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\{Faq, User, Blog, Review, Property, Booking};
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display the dashboard page.
     *
     * @return View
     */


    public function index(): View
    {
        if (Auth::check() && (Auth::user()->role === 'Admin' || Auth::user()->role === 'Support')) {

            $admins = User::where('role', 'Admin')->count();
            $users = User::where('role', '!=', 'Admin')->count();
            $faq = Faq::where('status', 'active')->count();
            $blogs = Blog::count();
            $review = Review::count();
            $properties = Property::count();
            $bookings = Booking::count();

            return view(
                'backend.layouts.dashboard.index',
                compact('admins', 'users', 'faq', 'blogs', 'review', 'properties', 'bookings')
            );
        } else {
            return view('auth.layouts.login');
        }
    }
}
