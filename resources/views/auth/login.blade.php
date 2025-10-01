@extends('layouts.app')

@section('content')
<div class="container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Login</h1>
        </div>

        @if (session('status'))
            <div class="auth-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="auth-label">Email</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                @error('email')
                    <div class="auth-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="auth-label">Password</label>
                <input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password">
                @error('password')
                    <div class="auth-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Remember Me 
            <div>
                <label class="auth-checkbox-label">
                    <input type="checkbox" class="auth-checkbox" name="remember">
                    <span>Remember me</span>
                </label>
            </div>-->

            <div class="auth-footer">
              <!--  @if (Route::has('password.request'))
                    <a class="auth-link" href="{{ route('password.request') }}">
                        Forgot your password?
                    </a>
                @endif-->

                <button type="submit" class="auth-button">
                    Log in
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
