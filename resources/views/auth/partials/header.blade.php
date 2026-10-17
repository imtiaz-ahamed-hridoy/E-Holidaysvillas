@php
    $systemSetting = App\Models\SystemSetting::first();
    $logo = $systemSetting && $systemSetting->logo
        ? \App\Helpers\Helper::imageUrl($systemSetting->logo)
        : null;
@endphp

<div class="col col-login mx-auto text-center">
    <a href="{{ route('welcome') }}">
        <img src="{{ $logo ?? asset('frontend/eVento_logo.png') }}"
            class="header-brand-img" alt="">
    </a>
</div>
