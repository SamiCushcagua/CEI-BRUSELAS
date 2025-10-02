<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CEI-Bruselas') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-cei.svg') }}">

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
                        <img src="{{ asset('images/logo-cei.svg') }}" alt="Logo" class="application-logo">
                    </a>
                </div>
                
                <div class="nav-center" id="nav-links">
                    <a href="{{ route('welcome') }}" class="nav-link {{ request()->routeIs('welcome') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">Sobre nosotros</a>
                 <!--   <a href="{{ route('Contact') }}" class="{{ request()->routeIs('Contact') ? 'active' : '' }}">Contact</a>--> <!-- CAhun no esta funcionando bien en el contacto -->
                    <a href="{{ route('usersAllShow') }}" class="{{ request()->routeIs('usersAllShow') ? 'active' : '' }}">Todos los usuarios</a>
                    <a href="{{ route('FAQ') }}" class="{{ request()->routeIs('FAQ') ? 'active' : '' }}">FAQ</a>
                    <a href="{{ route('bible.index') }}" class="{{ request()->routeIs('bible.*') ? 'active' : '' }}">Biblia</a>
                    @auth
                        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}">Perfil</a>
                    
                        @if (Auth::user()->is_admin)
                      
                            <a href="{{ route('contact-forum') }}" class="{{ request()->routeIs('contact-forum') ? 'active' : '' }}">Contacto</a>
                            <a href="{{ route('subjects.create') }}" class="{{ request()->routeIs('subjects.create') ? 'active' : '' }}">Materias</a>
                            <a href="{{ route('dashboard_cursos') }}" class="{{ request()->routeIs('dashboard_cursos') ? 'active' : '' }}">Dashboard Cursos</a>
                            <a href="{{ route('professors.index') }}" class="nav-link {{ request()->routeIs('professors.index') ? 'active' : '' }}">Profesores</a>
                            <a href="{{ route('students.index') }}" class="nav-link {{ request()->routeIs('students.index') ? 'active' : '' }}">Estudiantes</a>
                            <a href="{{ route('calificaciones.index') }}" class="nav-link {{ request()->routeIs('calificaciones.index') ? 'active' : '' }}">Calificaciones</a>

                            @endif
                        @if (Auth::user()->is_professor)
                            <a href="{{ route('professors.subjects', Auth::user()) }}" class="{{ request()->routeIs('professors.subjects') ? 'active' : '' }}">Mi materia</a>
                            <a href="{{ route('professors.students', Auth::user()) }}" class="{{ request()->routeIs('professors.students') ? 'active' : '' }}">Mis estudiantes</a>
                        @endif
                        @if (Auth::user()->is_student)
                            <a href="{{ route('students.subjects', Auth::user()) }}" class="{{ request()->routeIs('students.subjects') ? 'active' : '' }}">Mi curso</a>
                            <a href="{{ route('students.professors', Auth::user()) }}" class="{{ request()->routeIs('students.professors') ? 'active' : '' }}">Mis profesores</a>
                        @endif
                    @endauth
                </div>
                <div class="nav-right">
                    @auth
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: #2563eb; text-decoration: none; cursor: pointer;">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">Login</a>
                        <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'active' : '' }}">Register</a>
                    @endauth
                    
                    <!-- Mobile menu toggle button -->
                    <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Abrir menú">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="main-content">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>

            <!-- Footer -->
            <x-footer />
        </div>
        <script src="{{ asset('js/mobile-menu.js') }}"></script>
        </body>
</html>
