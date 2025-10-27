@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">
<script src="{{ asset('js/profesor-calificacion.js') }}"></script>

<div class="grades-container">
    <div class="grades-header">
        <div>
            <h1 class="grades-title">Sistema de Calificaciones</h1>
        </div>
        <div class="grades-info">
            <div><span>Trimestre:</span> {{ $currentTrimester ?? 1 }}</div>
            <div><span>Año:</span> {{ date('Y') }}</div>
            <a href="{{ route('grade-reports.index') }}" class="btn btn-primary">
                📊 Ver Reportes
            </a>
        </div>
    </div>

    @if($subjects->count() > 0)
    <div class="subjects-grid">
        @foreach($subjects as $subject)
        <div class="subject-card">
            <div class="subject-card-header">
                <h3 class="subject-name">{{ $subject->name }}</h3>
                <span class="subject-badge">{{ $subject->students->count() }} estudiantes</span>
            </div>

            <p class="subject-description">{{ $subject->description }}</p>

            <div class="progress-container">
                <div class="progress-label">
                    <span>Calificaciones registradas:</span>
                    <span>{{ $subject->grades->count() }}</span>
                </div>
                <div class="progress-fill" style="width: 50%"></div>
            </div>

            <div class="subject-actions">
                <a href="{{ route('grades.show', $subject) }}"
                    class="btn btn-primary btn-flex">
                    Ver Calificaciones
                </a>
                <a href="{{ route('grade-settings.index', $subject) }}"
                    class="btn btn-secondary">
                    ⚙️
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">📚</div>
        <h3 class="empty-title">No tienes materias asignadas</h3>
        <p class="empty-description">Contacta al administrador para que te asignen materias como profesor.</p>
        <a href="{{ route('subjects.index') }}" class="btn btn-primary">
            Ver Todas las Materias
        </a>
    </div>
    @endif
</div>
@endsection