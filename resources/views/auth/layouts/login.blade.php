@extends('auth.app')

@section('title', 'Admin Login')

@section('content')
    <div class="wrap-login100 p-0">
        <div class="card-body">
            <form class="login100-form validate-form" action="{{ route('login') }}" method="POST">
                @csrf
                <span class="login100-form-title">
                    Admin Login
                </span>
                <div class="wrap-input100 validate-input" data-bs-validate="Valid email is required: ex@abc.xyz">
                    <input class="input100" type="text" name="email" placeholder="Email" value="{{ old('email') }}">
                    <span class="focus-input100"></span>
                    <span class="symbol-input100">
                        <i class="zmdi zmdi-email" aria-hidden="true"></i>
                    </span>
                    @error('email')
                        <span class="textt-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="wrap-input100 validate-input" data-bs-validate="Password is required">
                    <input class="input100" type="password" name="password" placeholder="Password">
                    <span class="focus-input100"></span>
                    <span class="symbol-input100">
                        <i class="zmdi zmdi-lock" aria-hidden="true"></i>
                    </span>
                    @error('password')
                        <span class="textt-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="text-end pt-1">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-primary ms-1">Forgot Password?</a>
                    @endif
                </div>
                <div class="container-login100-form-btn">
                    <button type="submit" class="login100-form-btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
@endsection
