@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">
<script src="{{ asset('js/profesor-calificacion.js') }}"></script>

<div class="grades-container">
    <div class="grades-header">
        <div>
            <h1 class="grades-title">Reporte de {{ $subject->name }}</h1>
            <p class="grades-subtitle">Trimestre {{ $trimester }} - {{ $year }}</p>
        </div>
        <a href="{{ route('grade-reports.index') }}" class="btn btn-secondary">
            ← Volver a Reportes
        </a>
    </div>

    <!-- Estadísticas del Reporte -->
    <div class="settings-container">
        <div class="settings-header">
            <h2 class="settings-title">Estadísticas del Reporte</h2>
        </div>

        <div class="settings-content">
            <div class="stats-grid">
                <div class="stat-item stat-blue">
                    <span class="stat-label">Total de Estudiantes</span>
                    <span class="stat-value">{{ $statistics['total_students'] }}</span>
                </div>

                <div class="stat-item stat-green">
                    <span class="stat-label">Promedio General</span>
                    <span class="stat-value">{{ number_format($statistics['average_score'], 2) }}</span>
                </div>

                <div class="stat-item stat-yellow">
                    <span class="stat-label">Estudiantes Aprobados</span>
                    <span class="stat-value">{{ $statistics['passing_students'] }}</span>
                </div>

                <div class="stat-item stat-red">
                    <span class="stat-label">Estudiantes Reprobados</span>
                    <span class="stat-value">{{ $statistics['failing_students'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Calificaciones -->
    @if($grades->count() > 0)
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h2 class="grades-table-title">Calificaciones del Reporte</h2>
        </div>

        <div style="overflow-x: auto;">
            <table class="grades-table">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Tareas</th>
                        <th>Examen 1</th>
                        <th>Examen 2</th>
                        <th>Participación</th>
                        <th>Biblia</th>
                        <th>Texto</th>
                        <th>Otro</th>
                        <th>Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grades as $grade)
                    <tr>
                        <td>
                            <div class="student-info">
                                <div class="student-avatar">
                                    {{ substr($grade->student->name, 0, 1) }}
                                </div>
                                <div class="student-details">
                                    <h4>{{ $grade->student->name }}</h4>
                                    <p>{{ $grade->student->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>{{ $grade->task_score ?? '-' }}</td>
                        <td>{{ $grade->exam_score1 ?? '-' }}</td>
                        <td>{{ $grade->exam_score2 ?? '-' }}</td>
                        <td>{{ $grade->participation_score ?? '-' }}</td>
                        <td>{{ $grade->bible_score ?? '-' }}</td>
                        <td>{{ $grade->text_score ?? '-' }}</td>
                        <td>{{ $grade->other_score ?? '-' }}</td>
                        <td>
                            <span class="average-score">
                                {{ number_format($grade->average_score, 2) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">📊</div>
        <h3 class="empty-title">No hay calificaciones</h3>
        <p class="empty-description">No se encontraron calificaciones para este período.</p>
    </div>
    @endif
</div>
@endsection