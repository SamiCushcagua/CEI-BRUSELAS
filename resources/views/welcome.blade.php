@extends('layouts.app')

@section('content')

<div class="container welcome-page">
    <!-- Main Content -->
    <div class="main-container">
        <!-- Authentication Status -->
        @auth
            <div class="auth-message success">
                <p>Welcome, {{ auth()->user()->name }}</p>
            </div>
        @else
            <div class="auth-message warning">
                <p>Please log in to see all content.</p>
            </div>
        @endauth

        @auth
        {{-- Todos los usuarios con sesión: enlaces comunes --}}
        <div class="welcome-quick-section">
            <h2 class="welcome-quick-section-title">Accesos generales</h2>
            <div class="welcome-quick-buttons">
                <a href="{{ route('profile.edit') }}" class="welcome-btn">👤 Mi perfil</a>
                <a href="{{ route('bible.index') }}" class="welcome-btn">📖 Biblia</a>
        <!--        <a href="{{ route('FAQ') }}" class="welcome-btn">❓ FAQ</a>-->
               <!-- <a href="{{ route('about') }}" class="welcome-btn">ℹ️ Sobre nosotros</a>-->
               <a href="{{ route('usersAllShow') }}" class="welcome-btn">Todos los usuarios</a>
            </div>
        </div>

        @if (Auth::user()->is_admin)
        <div class="welcome-quick-section">
            <h2 class="welcome-quick-section-title">Administración</h2>
            <div class="welcome-quick-buttons">
                <!--<a href="{{ route('contact-forum') }}" class="welcome-btn">✉️ Contacto</a>-->
                <a href="{{ route('subjects.create') }}" class="welcome-btn">📖 Materias</a>
                <a href="{{ route('dashboard_cursos') }}" class="welcome-btn">🎓 Todos los Cursos</a>
                <a href="{{ route('periods.index') }}" class="welcome-btn">📆 Periodos</a>
                <a href="{{ route('admin.period-subject-dashboard') }}" class="welcome-btn">🗂️ Tablero Admin</a>
                <a href="{{ route('admin.subject-enrollment-outcomes') }}" class="welcome-btn">✅ Aprobados y diplomas</a>
            <!--    <a href="{{ route('professors.index') }}" class="welcome-btn">👨‍🏫 Profesores</a>
                <a href="{{ route('students.index') }}" class="welcome-btn">👨‍🎓 Estudiantes</a>-->
               <!-- <a href="{{ route('grades.index') }}" class="welcome-btn">📝 Calificaciones</a> -->
                <a href="{{ route('admin.graduates-overview') }}" class="welcome-btn">📋 Resumen aprobación</a>
                <a href="{{ route('students.index') }}" class="welcome-btn">Alumnos sin asignar</a>
            </div>
        </div>
        @endif

        @if (Auth::user()->isProfessor())
        <div class="welcome-quick-section">
            <h2 class="welcome-quick-section-title">Profesor</h2>
            <div class="welcome-quick-buttons">
                <a href="{{ route('professors.subjects', Auth::user()) }}" class="welcome-btn">📚 Mi materia</a>
                <a href="{{ route('professors.students', Auth::user()) }}" class="welcome-btn">👥 Mis estudiantes</a>
                <a href="{{ route('grades.index') }}" class="welcome-btn">📝 Calificaciones</a>
         <!--       <a href="{{ route('grade-reports.index') }}" class="welcome-btn">📊 Reportes</a>-->
                <a href="{{ route('attendance.index') }}" class="welcome-btn">📅 Asistencia</a>


            </div>
        </div>
        @endif

        @if (Auth::user()->isStudent())
        <div class="welcome-quick-section">
            <h2 class="welcome-quick-section-title">Estudiante</h2>
            <div class="welcome-quick-buttons">
                <a href="{{ route('students.subjects', Auth::user()) }}" class="welcome-btn">📚 Mi curso</a>
                <a href="{{ route('students.professors', Auth::user()) }}" class="welcome-btn">👥 Mis profesores</a>
                <a href="{{ route('attendance.index') }}" class="welcome-btn">📅 Asistencia</a>
                <a href="{{ route('student.grades') }}" class="welcome-btn">📝 Mis calificaciones</a>
            </div>
        </div>
        @endif
        @endauth
        <!-- Products Section -->
        <div class="products-grid">


        
            
        </div>
    </div>
</div>
@endsection