@php
    $systemSetting = App\Models\SystemSetting::first();
    $logo = $systemSetting && $systemSetting->logo
        ? \App\Helpers\Helper::imageUrl($systemSetting->logo)
        : null;
@endphp

<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('dashboard') }}">
                <img src="{{ $logo ?? asset('frontend/logo.png') }}" class="header-brand-img desktop-logo" alt="logo">
                <img src="{{ $logo ?? asset('frontend/logo.png') }}" class="header-brand-img toggle-logo" alt="logo">
                <img src="{{ $logo ?? asset('frontend/logo.png') }}" class="header-brand-img light-logo" alt="logo">
                <img src="{{ $logo ?? asset('frontend/logo.png') }}" class="header-brand-img light-logo1" alt="logo">
            </a>
        </div>

        <div class="main-sidemenu">
            <div class="slide-left disabled" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                </svg>
            </div>

            <ul class="side-menu">
                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('dashboard') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon"
                            enable-background="new 0 0 24 24" viewBox="0 0 24 24">
                            <path
                                d="M19.9794922,7.9521484l-6-5.2666016c-1.1339111-0.9902344-2.8250732-0.9902344-3.9589844,0l-6,5.2666016C3.3717041,8.5219116,2.9998169,9.3435669,3,10.2069702V19c0.0018311,1.6561279,1.3438721,2.9981689,3,3h2.5h7c0.0001831,0,0.0003662,0,0.0006104,0H18c1.6561279-0.0018311,2.9981689-1.3438721,3-3v-8.7930298C21.0001831,9.3435669,20.6282959,8.5219116,19.9794922,7.9521484z M15,21H9v-6c0.0014038-1.1040039,0.8959961-1.9985962,2-2h2c1.1040039,0.0014038,1.9985962,0.8959961,2,2V21z M20,19c-0.0014038,1.1040039-0.8959961,1.9985962-2,2h-2v-6c-0.0018311-1.6561279-1.3438721-2.9981689-3-3h-2c-1.6561279,0.0018311-2.9981689,1.3438721-3,3v6H6c-1.1040039-0.0014038-1.9985962-0.8959961-2-2v-8.7930298C3.9997559,9.6313477,4.2478027,9.0836182,4.6806641,8.7041016l6-5.2666016C11.0455933,3.1174927,11.5146484,2.9414673,12,2.9423828c0.4853516-0.0009155,0.9544067,0.1751099,1.3193359,0.4951172l6,5.2665405C19.7521973,9.0835571,20.0002441,9.6313477,20,10.2069702V19z" />
                        </svg>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('user.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span class="side-menu__label">Users</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span class="side-menu__label">Manage Bookings</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)">Manage Bookings</a></li>
                        <li><a href="{{ route('bookings.index') }}" class="slide-item">All Bookings</a></li>
                        <li><a href="{{ route('property-availability.index') }}" class="slide-item">Manage
                                Availability</a></li>
                    </ul>
                </li>

                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);">
                        <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path
                                d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376C296.3 401.1 253.9 416 208 416 93.1 416 0 322.9 0 208S93.1 0 208 0 416 93.1 416 208zM288 176c0-44.2-35.8-80-80-80s-80 35.8-80 80c0 48.8 46.5 111.6 68.6 138.6 6 7.3 16.8 7.3 22.7 0 22.1-27 68.6-89.8 68.6-138.6zm-112 0a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z" />
                        </svg>
                        <span class="side-menu__label">Manage Property</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)">Manage Property</a></li>
                        <li><a href="{{ route('property.index') }}" class="slide-item">All Properties</a></li>
                        <li><a href="{{ route('property-type.index') }}" class="slide-item">Manage Property Types</a>
                        </li>
                        <li><a href="{{ route('unit-type.index') }}" class="slide-item">Manage Unit Types</a></li>
                        <li><a href="{{ route('property-category.index') }}" class="slide-item">Manage Property
                                Categories</a></li>
                        <li><a href="{{ route('currency.index') }}" class="slide-item">Manage Currencies</a></li>
                        <li><a href="{{ route('rate.index') }}" class="slide-item">Manage Rates</a></li>
                        <li><a href="{{ route('amenity.index') }}" class="slide-item">Manage Amenities</a></li>
                        <li><a href="{{ route('addons.index') }}" class="slide-item">Manage Addons</a></li>
                        <li><a href="{{ route('discounts.index') }}" class="slide-item">Manage Discounts</a></li>
                        <li><a href="{{ route('property-import.index') }}" class="slide-item">Legacy SQL Import</a></li>

                    </ul>
                </li>

                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0);">
                        <svg class="side-menu__icon" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 122.88 95.51">
                            <path class="cls-1"
                                d="M12.6,26.42h96.77V70.15H12.6V26.42Zm9.55,32.23V36.86h11.3q3.13,0,4.47,1.23a4.53,4.53,0,0,1,1.35,3.55,6.44,6.44,0,0,1-.86,3.61,3.9,3.9,0,0,1-2.28,1.65v.21q4.21.73,4.22,5.75a6.14,6.14,0,0,1-1.4,4.2,5.4,5.4,0,0,1-4.28,1.59ZM31.84,50H29.12v3.55h2.69c1,0,1.46-.59,1.46-1.77S32.79,50,31.84,50Zm-.52-8.33h-2.2v3.24h2.17q1.32,0,1.32-1.62t-1.29-1.62Zm26.19,17H43.57V36.86h7V53.07h7v5.58Zm1.66-10.88q0-6,2.23-8.69c1.48-1.83,4.17-2.74,8.05-2.74s6.56.91,8,2.74,2.23,4.72,2.23,8.69a22.66,22.66,0,0,1-.47,5,9.33,9.33,0,0,1-1.62,3.52,6.58,6.58,0,0,1-3.17,2.19,15.46,15.46,0,0,1-5,.7,15.41,15.41,0,0,1-5-.7,6.49,6.49,0,0,1-3.17-2.19,9,9,0,0,1-1.62-3.52,22.07,22.07,0,0,1-.47-5Zm7.49-3.62v9.06h2.89a4.86,4.86,0,0,0,2.08-.33c.43-.22.64-.73.64-1.52V42.3H69.34a4.72,4.72,0,0,0-2,.33c-.43.22-.64.73-.64,1.52ZM94.1,52.23v-.94H92.33V46h8.4V57.6a24,24,0,0,1-8.58,1.57q-6,0-8.19-2.89a9,9,0,0,1-1.6-3.52,23.52,23.52,0,0,1-.45-5,22,22,0,0,1,.47-5,8.47,8.47,0,0,1,1.69-3.5q2.3-2.93,8.88-2.93a28.26,28.26,0,0,1,3.54.28,18,18,0,0,1,3.19.63l-1.05,5.33a30.75,30.75,0,0,0-5.1-.49,10.29,10.29,0,0,0-3.21.35,1.36,1.36,0,0,0-.92,1.4V53.7h2.3a4.78,4.78,0,0,0,1.83-.26c.38-.18.57-.58.57-1.21ZM8.32,0H114.56a8.34,8.34,0,0,1,8.32,8.32V87.19a8.35,8.35,0,0,1-8.32,8.32H8.32A8.34,8.34,0,0,1,0,87.19V8.32A8.34,8.34,0,0,1,8.32,0ZM118,20.1H5.29V88a2.29,2.29,0,0,0,2.3,2.3h108a2.29,2.29,0,0,0,2.3-2.3V20.1ZM106.64,7.22a4.11,4.11,0,1,1-4.11,4.11,4.11,4.11,0,0,1,4.11-4.11Zm-27.84,0a4.11,4.11,0,1,1-4.11,4.11A4.11,4.11,0,0,1,78.8,7.22Zm13.92,0a4.11,4.11,0,1,1-4.11,4.11,4.11,4.11,0,0,1,4.11-4.11ZM66.87,84.74a3.51,3.51,0,0,1,0-7h39.65a3.51,3.51,0,0,1,0,7ZM15,84.74a3.52,3.52,0,0,1,0-7H51.72a3.52,3.52,0,0,1,0,7Z" />
                        </svg>
                        <span class="side-menu__label">Blogs</span>
                        <i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li class="side-menu-label1"><a href="javascript:void(0)">Blogs</a></li>
                        <li><a href="{{ route('blog.index') }}" class="slide-item">All Posts</a></li>
                        <li><a href="{{ route('blog.category.index') }}" class="slide-item">Categories</a></li>
                        <li><a href="{{ route('blog.tag.index') }}" class="slide-item">Tags</a></li>
                    </ul>
                </li>

                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('review.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" class="side-menu__icon"
                            viewBox="0 0 640 640">
                            <path
                                d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z" />
                        </svg>
                        <span class="side-menu__label"> Reviews</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('faq.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 640 640">
                            <path
                                d="M280 88C280 57.1 254.9 32 224 32C193.1 32 168 57.1 168 88C168 118.9 193.1 144 224 144C254.9 144 280 118.9 280 88zM304 300.7L341 350.6C353.8 333.1 369.5 317.9 387.3 305.6L331.1 229.9C306 196 266.3 176 224 176C181.7 176 142 196 116.8 229.9L46.3 324.9C35.8 339.1 38.7 359.1 52.9 369.7C67.1 380.3 87.1 377.3 97.7 363.1L144 300.7L144 576C144 593.7 158.3 608 176 608C193.7 608 208 593.7 208 576L208 416C208 407.2 215.2 400 224 400C232.8 400 240 407.2 240 416L240 576C240 593.7 254.3 608 272 608C289.7 608 304 593.7 304 576L304 300.7zM496 608C575.5 608 640 543.5 640 464C640 384.5 575.5 320 496 320C416.5 320 352 384.5 352 464C352 543.5 416.5 608 496 608zM496 508C507 508 516 517 516 528C516 539 507 548 496 548C485 548 476 539 476 528C476 517 485 508 496 508zM496 408C484.4 408 474.7 416.2 472.5 427.2C470.7 435.9 462.3 441.5 453.6 439.7C444.9 437.9 439.3 429.5 441.1 420.8C446.3 395.2 468.9 376 496 376C526.9 376 552 401.1 552 432C552 451.8 540.3 469.8 522.2 477.9L511.8 482.5C510.6 490.2 504 496 496 496C487.2 496 480 488.8 480 480C480 468.8 486.6 458.7 496.8 454.1L509.2 448.6C515.8 445.7 520 439.2 520 432C520 418.7 509.3 408 496 408z" />
                        </svg>
                        <span class="side-menu__label"> Faqs</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('page.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 640 640">
                            <path
                                d="M192 64C156.7 64 128 92.7 128 128L128 512C128 547.3 156.7 576 192 576L448 576C483.3 576 512 547.3 512 512L512 234.5C512 217.5 505.3 201.2 493.3 189.2L386.7 82.7C374.7 70.7 358.5 64 341.5 64L192 64zM453.5 240L360 240C346.7 240 336 229.3 336 216L336 122.5L453.5 240z" />
                        </svg>
                        <span class="side-menu__label"> Dynamic Page </span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide"
                        href="{{ route('support.contact.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 512 512">
                            <path
                                d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z" />
                        </svg>
                        <span class="side-menu__label"> Support Contact </span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ route('inquiry.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 512 512">
                            <path
                                d="M256 32c14.2 0 27.3 7.5 34.5 19.8l216 368c7.3 12.4 7.3 27.7 .2 40.1S486.3 480 472 480H40c-14.3 0-27.6-7.7-34.7-20.1s-7-27.8 .3-40.1l216-368C228.7 39.5 241.8 32 256 32zm0 128c-13.3 0-24 10.7-24 24V296c0 13.3 10.7 24 24 24s24-10.7 24-24V184c0-13.3-10.7-24-24-24zm32 224a32 32 0 1 0 -64 0 32 32 0 1 0 64 0z" />
                        </svg>
                        <span class="side-menu__label"> Manage Inquiries </span>
                    </a>
                </li>

                <hr>

                <li class="slide {{ request()->is('admin/settings*') ? 'active is-expanded' : '' }}">
                    <a class="side-menu__item {{ request()->is('admin/settings*') ? 'active is-expanded' : '' }}"
                        data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 512 512">
                            <path
                                d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" />
                        </svg>
                        <span class="side-menu__label">Settings</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('profile.setting') }}" class="slide-item">Profile Settings</a></li>
                        <li><a href="{{ route('system.index') }}" class="slide-item">System Settings</a></li>
                        <li><a href="{{ route('about_us.edit') }}" class="slide-item">About Us Settings</a></li>
                        <li><a href="{{ route('mail.setting') }}" class="slide-item">Mail Settings</a></li>
                        <li><a href="{{ route('stripe.edit') }}" class="slide-item">Stripe Settings</a></li>
                    </ul>
                </li>
            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg>
            </div>
        </div>
    </div>
</div>
