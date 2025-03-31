@extends('layouts.app')

@section('content')
<div class="container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Register</h1>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="auth-label">Name</label>
                <input id="name" class="auth-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                @error('name')
                    <div class="auth-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="auth-label">Email</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                @error('email')
                    <div class="auth-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="auth-label">Password</label>
                <input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password">
                @error('password')
                    <div class="auth-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="auth-label">Confirm Password</label>
                <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password">
                @error('password_confirmation')
                    <div class="auth-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-footer">
                <a class="auth-link" href="{{ route('login') }}">
                    Already registered?
                </a>

                <button type="submit" class="auth-button">
                    Register
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
