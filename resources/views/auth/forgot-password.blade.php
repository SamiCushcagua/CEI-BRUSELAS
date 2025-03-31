@extends('layouts.app')

@section('content')
<div class="container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Forgot Password</h1>
        </div>

        <div class="auth-message warning">
            Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="auth-message success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="auth-label">Email</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="auth-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-footer">
                <a class="auth-link" href="{{ route('login') }}">
                    Back to login
                </a>

                <button type="submit" class="auth-button">
                    Email Password Reset Link
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
