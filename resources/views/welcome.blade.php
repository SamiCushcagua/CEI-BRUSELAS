@extends('layouts.app')

@section('content')
<style>
    .edit-button {
        background-color: #3b82f6;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-weight: bold;
        width: 50%;
    }
    .edit-button:hover {
        background-color: #2563eb;
    }
    .delete-button {
        background-color: #ef4444;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-weight: bold;
        width: 50%;
    }
    .delete-button:hover {
        background-color: #dc2626;
    }
    .button-container {
        margin-top: 16px;
        display: flex;
        width: 100%;
    }
    /* Botones de acceso rápido - responsive para móvil */
    .welcome-quick-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
        margin: 24px 0;
        padding: 0 4px;
    }
    .welcome-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px 20px;
        background: #3b82f6;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        transition: background 0.2s;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
    }
    .welcome-btn:hover {
        background: #2563eb;
        color: white;
    }
    @media (max-width: 480px) {
        .welcome-quick-buttons {
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 20px 0;
        }
        .welcome-btn {
            padding: 12px 16px;
            font-size: 13px;
        }
    }
</style>



<div class="container">
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

        <!-- Accesos rápidos para profesores -->
        @auth
        @if (Auth::user()->isProfessor())
        <div class="welcome-quick-buttons">
            <a href="{{ route('professors.subjects', Auth::user()) }}" class="welcome-btn">
                📚 Mi materia
            </a>
            <a href="{{ route('professors.students', Auth::user()) }}" class="welcome-btn">
                👥 Mis estudiantes
            </a>
            <a href="{{ route('grades.index') }}" class="welcome-btn">
                📝 Calificaciones
            </a>
            <a href="{{ route('grade-reports.index') }}" class="welcome-btn">
                📊 Reportes
            </a>
        </div>
        @endif
        @endauth

        <!-- Products Section -->
        <div class="products-grid">


        
            
        </div>
    </div>
</div>
@endsection