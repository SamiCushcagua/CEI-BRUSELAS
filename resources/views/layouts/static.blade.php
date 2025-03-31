<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CP Atelier') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/logoCP.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    </head>
    <body>
        <div class="page-container">
            <!-- Navigation -->
            <nav class="nav-menu">
                <div class="nav-left">
                    <a href="{{ route('welcome') }}" class="logo-link">
                        <img src="{{ asset('images/logoCP.svg') }}" alt="Logo" class="application-logo">
                    </a>
                </div>
                <div class="nav-center">
                    <a href="{{ route('welcome') }}">Home</a>
                    <a href="{{ route('about') }}">About Us</a>
                    <a href="{{ route('Contact') }}">Contact</a>
                    <a href="{{ route('FAQ') }}">FAQ</a>
                </div>
                <div class="nav-right">
                    @auth
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: #2563eb; text-decoration: none; cursor: pointer;">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}">Login</a>
                        <a href="{{ route('register') }}">Register</a>
                    @endauth
                </div>
            </nav>

            <!-- Page Content -->
            <main class="main-content">
                @yield('content')
            </main>

            <!-- Footer -->
            <x-footer />
        </div>
    </body>
</html> 