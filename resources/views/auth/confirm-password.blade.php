@extends('auth.app')

@section('title', 'Confirm Password')

@section('content')
    <div class="wrap-login100 p-0">
        <div class="card-body">
            <form class="login100-form validate-form" action="{{ route('password.confirm') }}" method="POST">
                @csrf
                <span class="login100-form-title">Confirm Password</span>
                <p class="text-muted text-center">Confirm your password before continuing.</p>

                <div class="wrap-input100 validate-input">
                    <input class="input100" type="password" name="password" placeholder="Password">
                    <span class="focus-input100"></span>
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="container-login100-form-btn">
                    <button type="submit" class="login100-form-btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>
@endsection
