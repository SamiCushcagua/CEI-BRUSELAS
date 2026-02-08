@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">

<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>
    <div class="grades-header">
        <div>
            <h1 class="grades-title">Mis Profesores: {{ $student->name }}</h1>
        </div>
        <div class="grades-info">
            <div><span>Total Profesores:</span> {{ $professors->count() }}</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabla de profesores -->
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h3 class="grades-table-title">Lista de Profesores</h3>
            <div class="grades-table-actions">
                <span class="subject-info">{{ $professors->count() }} profesor(es)</span>
            </div>
        </div>

        <div class="grades-table-wrapper">
            <table class="grades-table">
                <thead>
                    <tr>
                        <th>Profesor</th>
                        <th>Email</th>
                        <th>Materias</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($professors as $professor)
                        <tr>
                            <td>
                                <div class="student-info">
                                    <div class="student-avatar">
                                        {{ substr($professor->name, 0, 1) }}
                                    </div>
                                    <div class="student-details">
                                        <h4>{{ $professor->name }}</h4>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="student-email">{{ $professor->email }}</span>
                            </td>
                            <td>
                                <span class="subject-count">
                                    {{ $professor->subjects->count() }} materia(s)
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">
                                <div class="empty-state">
                                    <div class="empty-icon">👨‍🏫</div>
                                    <h3 class="empty-title">No tienes profesores asignados</h3>
                                    <p class="empty-description">Contacta al administrador para inscribirte en materias.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
