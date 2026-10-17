@extends('backend.app')

@section('title', 'Dashboard')

@section('content')
    {{-- PAGE-HEADER --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
        </div>
        <div class="ms-auto pageheader-btn">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </div>
    </div>
    {{-- PAGE-HEADER --}}


    <div class="row">

        <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
            <a href="/admin/user" class="card-link">
                <div class="card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="mb-2 fw-semibold">{{ $admins }}</h3>
                                <p class="text-muted fs-13 mb-0">Total Admins</p>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-primary dash ms-auto box-shadow-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
                                        <g data-name="28-Agency">
                                            <path style="fill:#ffee6e" d="M47 11h10v2H47z" />
                                            <path transform="rotate(-29.745 50.5 6)" style="fill:#ffee6e"
                                                d="M46.469 5h8.062v2h-8.062z" />
                                            <path transform="rotate(-60.255 50.5 18)" style="fill:#ffee6e"
                                                d="M49.5 13.969h2v8.062h-2z" />
                                            <path style="fill:#ffee6e" d="M7 11h10v2H7z" />
                                            <path transform="rotate(-60.255 13.5 6)" style="fill:#ffee6e"
                                                d="M12.5 1.969h2v8.062h-2z" />
                                            <path transform="rotate(-29.745 13.499 18)" style="fill:#ffee6e"
                                                d="M9.469 17h8.062v2H9.469z" />
                                            <path
                                                d="m36.707 26.293-1.414 1.414L37.586 30H33v-6h-2v6h-4.586l2.293-2.293-1.414-1.414-4 4A.974.974 0 0 0 23.3 31.7l-.006.006 4 4 1.414-1.414L26.414 32h11.172l-2.293 2.293 1.414 1.414 4-4-.007-.007a.974.974 0 0 0 .006-1.408z"
                                                style="fill:#afb4c8" />
                                            <path style="fill:#b2876d" d="M26 13h4v8h-4z" />
                                            <path style="fill:#966857" d="M28 13h2v8h-2zM27 1v2.73l-4 2.18V1h4z" />
                                            <path style="fill:#b2876d" d="M25 1h-2v4.91l2-1.09V1z" />
                                            <path style="fill:#a3d4ff" d="M33 13h5v4h-5z" />
                                            <path style="fill:#65b1fc" d="M33 15h5v2h-5z" />
                                            <path d="M38 13h-5v4h5zm3-3.09V21H30v-8h-4v8h-3V9.91L32 5z"
                                                style="fill:#f7f7f7" />
                                            <path style="fill:#cfcfcf" d="m32 5-9 4.91v2L32 7l9 4.91v-2L32 5z" />
                                            <path style="fill:#ff936b"
                                                d="M27 3.73 32 1l11 6v4l-2-1.09L32 5l-9 4.91L21 11V7l2-1.09 4-2.18z" />
                                            <path style="fill:#ff7045"
                                                d="m32 3-9 4.91L21 9v2l2-1.09L32 5l9 4.91L43 11V9l-2-1.09L32 3z" />
                                            <path d="M43 31v2h3c7 0 7-3 7-3a2.938 2.938 0 0 0 3 3h3v-2a8 8 0 0 0-16 0z"
                                                style="fill:#966857" />
                                            <path
                                                d="M53 28a2.938 2.938 0 0 0 3 3h3v2h-3a2.938 2.938 0 0 1-3-3s0 3-7 3h-3v-2h3c7 0 7-3 7-3z"
                                                style="fill:#8d5c4d" />
                                            <path style="fill:#e9edf5" d="M39 47v16h24V47l-10-2-2 2-2-2-10 2z" />
                                            <path style="fill:#cdd2e1"
                                                d="m49 45 2 2 2-2 10 2v3l-10-2-2 2-2-2-10 2v-3l10-2z" />
                                            <path
                                                d="M49 42.59V45l2 2 2-2v-2.41a5.083 5.083 0 0 1-4 0zM56 33v5h1a2.006 2.006 0 0 0 2-2v-3zM43 33v3a2.006 2.006 0 0 0 2 2h1v-5z"
                                                style="fill:#faa68e" />
                                            <path
                                                d="M49 42.59a5 5 0 0 0 5.54-1.05A5.022 5.022 0 0 0 56 38v-5a2.938 2.938 0 0 1-3-3s0 3-7 3v5a5.029 5.029 0 0 0 3 4.59z"
                                                style="fill:#ffcdbe" />
                                            <path
                                                d="M53 30a2.938 2.938 0 0 0 3 3v2a2.938 2.938 0 0 1-3-3s0 3-7 3v-2c7 0 7-3 7-3z"
                                                style="fill:#ffbeaa" />
                                            <path style="fill:#a3d4ff" d="m50 52-1 6 2 2 2-2-1-6h-2z" />
                                            <path style="fill:#65b1fc" d="m48 50 2 2h2l2-2-3-3-3 3z" />
                                            <path style="fill:#afb4c8"
                                                d="M46.22 45.56 48 50l3-3-2-2-2.78.56zM51 47l3 3 1.78-4.44L53 45l-2 2z" />
                                            <path d="M5 31v2h3c7 0 7-3 7-3a2.938 2.938 0 0 0 3 3h3v-2a8 8 0 0 0-16 0z"
                                                style="fill:#966857" />
                                            <path
                                                d="M15 28a2.938 2.938 0 0 0 3 3h3v2h-3a2.938 2.938 0 0 1-3-3s0 3-7 3H5v-2h3c7 0 7-3 7-3z"
                                                style="fill:#8d5c4d" />
                                            <path style="fill:#9c9c9c" d="M1 47v16h24V47l-10-2-2 2-2-2-10 2z" />
                                            <path style="fill:#cfcfcf"
                                                d="m11 45 2 2 2-2 10 2v3l-10-2-2 2-2-2-10 2v-3l10-2z" />
                                            <path
                                                d="M11 42.59V45l2 2 2-2v-2.41a5.083 5.083 0 0 1-4 0zM18 33v5h1a2.006 2.006 0 0 0 2-2v-3zM5 33v3a2.006 2.006 0 0 0 2 2h1v-5z"
                                                style="fill:#faa68e" />
                                            <path
                                                d="M11 42.59a5 5 0 0 0 5.54-1.05A5.022 5.022 0 0 0 18 38v-5a2.938 2.938 0 0 1-3-3s0 3-7 3v5a5.029 5.029 0 0 0 3 4.59z"
                                                style="fill:#ffcdbe" />
                                            <path
                                                d="M15 30a2.938 2.938 0 0 0 3 3v2a2.938 2.938 0 0 1-3-3s0 3-7 3v-2c7 0 7-3 7-3z"
                                                style="fill:#ffbeaa" />
                                            <path
                                                d="M25.2 46.02 16 44.18v-.992A6.007 6.007 0 0 0 18.91 39H19a3 3 0 0 0 3-3v-5a9 9 0 0 0-18 0v5a3 3 0 0 0 3 3h.09A6.007 6.007 0 0 0 10 43.188v.992L.8 46.02A1 1 0 0 0 0 47v16a1 1 0 0 0 1 1h24a1 1 0 0 0 1-1V47a1 1 0 0 0-.8-.98zM19 37v-3h1v2a1 1 0 0 1-1 1zM6 36v-2h1v3a1 1 0 0 1-1-1zm2-4H6v-1a7 7 0 0 1 14 0v1h-2a1.883 1.883 0 0 1-2-2 1.019 1.019 0 0 0-1-1.023.979.979 0 0 0-1 .966v.015c-.017.11-.414 2.042-6 2.042zm1 1.979c2.99-.129 4.7-.84 5.687-1.628A3.963 3.963 0 0 0 17 33.874V38a4 4 0 0 1-8 0zM13 44a6 6 0 0 0 1-.09v.676l-1 1-1-1v-.676a6 6 0 0 0 1 .09zM2 47.819l8.671-1.734L12 47.414V62H2zM24 62H14V47.414l1.329-1.329L24 47.819zM63.2 46.02 54 44.18v-.992A6.007 6.007 0 0 0 56.91 39H57a3 3 0 0 0 3-3v-5a9 9 0 0 0-18 0v5a3 3 0 0 0 3 3h.09A6.007 6.007 0 0 0 48 43.188v.992l-9.2 1.84a1 1 0 0 0-.8.98v16a1 1 0 0 0 1 1h24a1 1 0 0 0 1-1V47a1 1 0 0 0-.8-.98zm-14.525.065.915.915-1.221 1.221L47.6 46.3zm3.258 11.572-.933.929-.929-.929.776-4.657h.306zM51.586 51h-1.172l-1-1L51 48.414 52.586 50zm.828-4 .915-.915 1.071.215-.768 1.921zM57 37v-3h1v2a1 1 0 0 1-1 1zm-13-1v-2h1v3a1 1 0 0 1-1-1zm2-4h-2v-1a7 7 0 0 1 14 0v1h-2a1.883 1.883 0 0 1-2-2 1.019 1.019 0 0 0-1-1.023.979.979 0 0 0-1 .966v.015c-.017.11-.414 2.042-6 2.042zm1 1.979c2.99-.129 4.7-.84 5.687-1.628A3.963 3.963 0 0 0 55 33.874V38a4 4 0 0 1-8 0zM51 44a6 6 0 0 0 1-.09v.676l-1 1-1-1v-.676a6 6 0 0 0 1 .09zm-11 3.819 5.6-1.12 1.469 3.672a.978.978 0 0 0 .227.331l-.005.005 1.636 1.636-.915 5.493a1 1 0 0 0 .279.871L50 60.414V62H40zM62 62H52v-1.586l1.707-1.707a1 1 0 0 0 .279-.871l-.915-5.493 1.636-1.636-.007-.007a.978.978 0 0 0 .227-.331L56.4 46.7l5.6 1.12zM20.489 11.86a1 1 0 0 0 .99.018l.521-.285V21a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1v-9.407l.521.285A1 1 0 0 0 44 11V7a1 1 0 0 0-.521-.878l-11-6a1 1 0 0 0-.958 0L28 2.043V1a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v4.316l-1.479.806A1 1 0 0 0 20 7v4a1 1 0 0 0 .489.86zM27 20v-6h2v6zm13 0h-9v-7a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v7h-1v-9.5l8-4.364 8 4.364zM24 2h2v1.134l-2 1.091zm-2 5.594 10-5.455 10 5.455v1.721l-9.521-5.193a1 1 0 0 0-.958 0L22 9.315z" />
                                            <path
                                                d="M32 17a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1h-5a1 1 0 0 0-1 1zm2-3h3v2h-3zM47 11h10v2H47zM46.504 7.132l7-4 .992 1.737-7 4zM46.504 16.869l.993-1.737 7 4-.993 1.736zM7 11h10v2H7zM9.504 4.868l.993-1.737 7 4-.993 1.737zM9.504 19.131l7-4 .992 1.737-7 4zM40.7 31.7a.974.974 0 0 0 .006-1.408l-4-4-1.414 1.414L37.586 30H33v-6h-2v6h-4.586l2.293-2.293-1.414-1.414-4 4A.974.974 0 0 0 23.3 31.7l-.006.006 4 4 1.414-1.414L26.414 32h11.172l-2.293 2.293 1.414 1.414 4-4z" />
                                        </g>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
            <a href="/admin/user" class="card-link">
                <div class="card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="mb-2 fw-semibold">{{ $users }}</h3>
                                <p class="text-muted fs-13 mb-0">Total Users</p>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-secondary dash ms-auto box-shadow-secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                        <path
                                            d="M160 64C142.3 64 128 78.3 128 96C128 113.7 142.3 128 160 128L160 139C160 181.4 176.9 222.1 206.9 252.1L274.8 320L206.9 387.9C176.9 417.9 160 458.6 160 501L160 512C142.3 512 128 526.3 128 544C128 561.7 142.3 576 160 576L480 576C497.7 576 512 561.7 512 544C512 526.3 497.7 512 480 512L480 501C480 458.6 463.1 417.9 433.1 387.9L365.2 320L433.1 252.1C463.1 222.1 480 181.4 480 139L480 128C497.7 128 512 113.7 512 96C512 78.3 497.7 64 480 64L160 64zM224 139L224 128L416 128L416 139C416 158 410.4 176.4 400 192L240 192C229.7 176.4 224 158 224 139zM240 448C243.5 442.7 247.6 437.7 252.1 433.1L320 365.2L387.9 433.1C392.5 437.7 396.5 442.7 400.1 448L240 448z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
            <a href="/admin/review" class="card-link">
                <div class="card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="mb-2 fw-semibold">{{ $review ?? '0' }}</h3>
                                <p class="text-muted fs-13 mb-0">Total Reviews</p>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-primary dash ms-auto box-shadow-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 640 640">
                                        <path
                                            d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
            <a href="/admin/faq" class="card-link">
                <div class="card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="mb-2 fw-semibold">{{ $faq ?? '0' }}</h3>
                                <p class="text-muted fs-13 mb-0">Total FAQs</p>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-success dash ms-auto box-shadow-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 640 640">
                                        <path
                                            d="M320 576C461.4 576 576 461.4 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 461.4 178.6 576 320 576zM320 240C302.3 240 288 254.3 288 272C288 285.3 277.3 296 264 296C250.7 296 240 285.3 240 272C240 227.8 275.8 192 320 192C364.2 192 400 227.8 400 272C400 319.2 364 339.2 344 346.5L344 350.3C344 363.6 333.3 374.3 320 374.3C306.7 374.3 296 363.6 296 350.3L296 342.2C296 321.7 310.8 307 326.1 302C332.5 299.9 339.3 296.5 344.3 291.7C348.6 287.5 352 281.7 352 272.1C352 254.4 337.7 240.1 320 240.1zM288 432C288 414.3 302.3 400 320 400C337.7 400 352 414.3 352 432C352 449.7 337.7 464 320 464C302.3 464 288 449.7 288 432z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
            <a href="{{ route('blog.index') }}" class="card-link">
                <div class="card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="mb-2 fw-semibold">{{ $blogs ?? '0' }}</h3>
                                <p class="text-muted fs-13 mb-0">Total Blogs</p>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-info dash ms-auto box-shadow-info">
                                    <svg class="side-menu__icon" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 122.88 95.51">
                                        <path class="cls-1"
                                            d="M12.6,26.42h96.77V70.15H12.6V26.42Zm9.55,32.23V36.86h11.3q3.13,0,4.47,1.23a4.53,4.53,0,0,1,1.35,3.55,6.44,6.44,0,0,1-.86,3.61,3.9,3.9,0,0,1-2.28,1.65v.21q4.21.73,4.22,5.75a6.14,6.14,0,0,1-1.4,4.2,5.4,5.4,0,0,1-4.28,1.59ZM31.84,50H29.12v3.55h2.69c1,0,1.46-.59,1.46-1.77S32.79,50,31.84,50Zm-.52-8.33h-2.2v3.24h2.17q1.32,0,1.32-1.62t-1.29-1.62Zm26.19,17H43.57V36.86h7V53.07h7v5.58Zm1.66-10.88q0-6,2.23-8.69c1.48-1.83,4.17-2.74,8.05-2.74s6.56.91,8,2.74,2.23,4.72,2.23,8.69a22.66,22.66,0,0,1-.47,5,9.33,9.33,0,0,1-1.62,3.52,6.58,6.58,0,0,1-3.17,2.19,15.46,15.46,0,0,1-5,.7,15.41,15.41,0,0,1-5-.7,6.49,6.49,0,0,1-3.17-2.19,9,9,0,0,1-1.62-3.52,22.07,22.07,0,0,1-.47-5Zm7.49-3.62v9.06h2.89a4.86,4.86,0,0,0,2.08-.33c.43-.22.64-.73.64-1.52V42.3H69.34a4.72,4.72,0,0,0-2,.33c-.43.22-.64.73-.64,1.52ZM94.1,52.23v-.94H92.33V46h8.4V57.6a24,24,0,0,1-8.58,1.57q-6,0-8.19-2.89a9,9,0,0,1-1.6-3.52,23.52,23.52,0,0,1-.45-5,22,22,0,0,1,.47-5,8.47,8.47,0,0,1,1.69-3.5q2.3-2.93,8.88-2.93a28.26,28.26,0,0,1,3.54.28,18,18,0,0,1,3.19.63l-1.05,5.33a30.75,30.75,0,0,0-5.1-.49,10.29,10.29,0,0,0-3.21.35,1.36,1.36,0,0,0-.92,1.4V53.7h2.3a4.78,4.78,0,0,0,1.83-.26c.38-.18.57-.58.57-1.21ZM8.32,0H114.56a8.34,8.34,0,0,1,8.32,8.32V87.19a8.35,8.35,0,0,1-8.32,8.32H8.32A8.34,8.34,0,0,1,0,87.19V8.32A8.34,8.34,0,0,1,8.32,0ZM118,20.1H5.29V88a2.29,2.29,0,0,0,2.3,2.3h108a2.29,2.29,0,0,0,2.3-2.3V20.1ZM106.64,7.22a4.11,4.11,0,1,1-4.11,4.11,4.11,4.11,0,0,1,4.11-4.11Zm-27.84,0a4.11,4.11,0,1,1-4.11,4.11A4.11,4.11,0,0,1,78.8,7.22Zm13.92,0a4.11,4.11,0,1,1-4.11,4.11,4.11,4.11,0,0,1,4.11-4.11ZM66.87,84.74a3.51,3.51,0,0,1,0-7h39.65a3.51,3.51,0,0,1,0,7ZM15,84.74a3.52,3.52,0,0,1,0-7H51.72a3.52,3.52,0,0,1,0,7Z"
                                            fill="currentColor" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
            <a href="{{ route('property.index') }}" class="card-link">
                <div class="card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="mb-2 fw-semibold">{{ $properties ?? '0' }}</h3>
                                <p class="text-muted fs-13 mb-0">Total Properties</p>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-warning dash ms-auto box-shadow-warning">
                                    <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376C296.3 401.1 253.9 416 208 416 93.1 416 0 322.9 0 208S93.1 0 208 0 416 93.1 416 208zM288 176c0-44.2-35.8-80-80-80s-80 35.8-80 80c0 48.8 46.5 111.6 68.6 138.6 6 7.3 16.8 7.3 22.7 0 22.1-27 68.6-89.8 68.6-138.6zm-112 0a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z" fill="currentColor"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
            <a href="{{ route('bookings.index') }}" class="card-link">
                <div class="card overflow-hidden">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h3 class="mb-2 fw-semibold">{{ $bookings ?? '0' }}</h3>
                                <p class="text-muted fs-13 mb-0">Total Bookings</p>
                            </div>
                            <div class="col col-auto top-icn dash">
                                <div class="counter-icon bg-danger dash ms-auto box-shadow-danger">
                                    <svg class="side-menu__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

@endsection