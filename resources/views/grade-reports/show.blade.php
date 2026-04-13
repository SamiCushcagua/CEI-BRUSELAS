@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">
<script src="{{ asset('js/profesor-calificacion.js') }}?v={{ filemtime(public_path('js/profesor-calificacion.js')) }}"></script>

<div class="grades-container">
    <div class="grades-header">
        <div>
            <h1 class="grades-title">Reporte de {{ $subject->name }}</h1>
            <p class="grades-subtitle">Trimestre {{ $trimester }} — {{ $year }}</p>
        </div>
        <a href="{{ route('grade-reports.index') }}" class="btn btn-secondary">
            ← Volver a Reportes
        </a>
    </div>

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
                    <span class="stat-value">{{ number_format((float) $statistics['average_score'], 2) }}</span>
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

    @if($students->count() > 0)
    <div class="grades-show-toolbar">
        <button type="button" class="btn btn-primary" id="btn-grade-edit-open">Modificar resultados</button>
        <button type="button" class="btn btn-secondary" id="btn-grade-edit-cancel" hidden>Cancelar</button>
        <button type="button" class="btn btn-success" id="btn-grade-save-all" hidden>Guardar cambios</button>
    </div>

    <div id="grades-summary-panel">
        <div class="grades-table-container">
            <div class="grades-table-header">
                <h2 class="grades-table-title">Calificaciones del reporte</h2>
            </div>

            <div class="grades-table-wrapper" style="overflow-x: auto;">
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th class="sticky-col">Estudiante</th>
                            <th>Tareas</th>
                            <th>Examen 1</th>
                            <th>Examen 2</th>
                            <th>Participación</th>
                            <th>Biblia</th>
                            <th>Texto</th>
                            <th>Otro</th>
                            <th>Promedio</th>
                            <th>Aprobó trim.</th>
                            <th>Diploma</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php
                            $studentGrade = $grades->firstWhere('student_id', $student->id);
                            $diplomaOk = (bool) ($student->pivot?->diploma_delivered ?? false);
                        @endphp
                        <tr>
                            <td class="sticky-col">
                                <div class="student-info">
                                    <div class="student-avatar">{{ substr($student->name, 0, 1) }}</div>
                                    <div class="student-details">
                                        <h4>{{ $student->name }}</h4>
                                        <p>{{ $student->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $studentGrade && $studentGrade->task_score !== null ? number_format((float) $studentGrade->task_score, 2) : '—' }}</td>
                            <td>{{ $studentGrade && $studentGrade->exam_score1 !== null ? number_format((float) $studentGrade->exam_score1, 2) : '—' }}</td>
                            <td>{{ $studentGrade && $studentGrade->exam_score2 !== null ? number_format((float) $studentGrade->exam_score2, 2) : '—' }}</td>
                            <td>{{ $studentGrade && $studentGrade->participation_score !== null ? number_format((float) $studentGrade->participation_score, 2) : '—' }}</td>
                            <td>{{ $studentGrade && $studentGrade->bible_score !== null ? number_format((float) $studentGrade->bible_score, 2) : '—' }}</td>
                            <td>{{ $studentGrade && $studentGrade->text_score !== null ? number_format((float) $studentGrade->text_score, 2) : '—' }}</td>
                            <td>{{ $studentGrade && $studentGrade->other_score !== null ? number_format((float) $studentGrade->other_score, 2) : '—' }}</td>
                            <td>
                                <span class="average-score">
                                    {{ $studentGrade ? number_format($studentGrade->average_score, 2) : '0.00' }}
                                </span>
                            </td>
                            <td>{{ $studentGrade && $studentGrade->passed ? 'Sí' : 'No' }}</td>
                            <td>{{ $diplomaOk ? 'Sí' : 'No' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="grades-edit-panel" hidden>
        <div class="grades-table-container">
            <div class="grades-table-header">
                <h2 class="grades-table-title">Edición — Reporte</h2>
                <p class="grades-subtitle" style="margin: 0;">Modifica los valores y pulsa <strong>Guardar cambios</strong> arriba.</p>
            </div>

            <div style="overflow-x: auto;">
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th class="sticky-col">Estudiante</th>
                            <th>Tareas</th>
                            <th>Examen 1</th>
                            <th>Examen 2</th>
                            <th>Participación</th>
                            <th>Biblia</th>
                            <th>Texto</th>
                            <th>Otro</th>
                            <th>Promedio</th>
                            <th>Aprobó trim.</th>
                            <th>Diploma entregado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php
                            $studentGrade = $grades->firstWhere('student_id', $student->id);
                            $diplomaOk = (bool) ($student->pivot?->diploma_delivered ?? false);
                        @endphp
                        <tr>
                            <td class="sticky-col">
                                <div class="student-info">
                                    <div class="student-avatar">{{ substr($student->name, 0, 1) }}</div>
                                    <div class="student-details">
                                        <h4>{{ $student->name }}</h4>
                                        <p>{{ $student->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="number" class="grade-input" min="0" max="100" step="0.01"
                                    data-student-id="{{ $student->id }}" data-field="task_score"
                                    value="{{ $studentGrade?->task_score ?? '' }}">
                            </td>
                            <td>
                                <input type="number" class="grade-input" min="0" max="100" step="0.01"
                                    data-student-id="{{ $student->id }}" data-field="exam_score1"
                                    value="{{ $studentGrade?->exam_score1 ?? '' }}">
                            </td>
                            <td>
                                <input type="number" class="grade-input" min="0" max="100" step="0.01"
                                    data-student-id="{{ $student->id }}" data-field="exam_score2"
                                    value="{{ $studentGrade?->exam_score2 ?? '' }}">
                            </td>
                            <td>
                                <input type="number" class="grade-input" min="0" max="100" step="0.01"
                                    data-student-id="{{ $student->id }}" data-field="participation_score"
                                    value="{{ $studentGrade?->participation_score ?? '' }}">
                            </td>
                            <td>
                                <input type="number" class="grade-input" min="0" max="100" step="0.01"
                                    data-student-id="{{ $student->id }}" data-field="bible_score"
                                    value="{{ $studentGrade?->bible_score ?? '' }}">
                            </td>
                            <td>
                                <input type="number" class="grade-input" min="0" max="100" step="0.01"
                                    data-student-id="{{ $student->id }}" data-field="text_score"
                                    value="{{ $studentGrade?->text_score ?? '' }}">
                            </td>
                            <td>
                                <input type="number" class="grade-input" min="0" max="100" step="0.01"
                                    data-student-id="{{ $student->id }}" data-field="other_score"
                                    value="{{ $studentGrade?->other_score ?? '' }}">
                            </td>
                            <td>
                                <span class="average-score" data-student-id="{{ $student->id }}">
                                    {{ $studentGrade ? number_format($studentGrade->average_score, 2) : '0.00' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="grade-checkbox"
                                    data-student-id="{{ $student->id }}" data-field="passed"
                                    @checked($studentGrade && $studentGrade->passed)>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="grade-checkbox"
                                    data-student-id="{{ $student->id }}" data-field="diploma_delivered"
                                    @checked($diplomaOk)>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">📊</div>
        <h3 class="empty-title">No hay calificaciones</h3>
        <p class="empty-description">No se encontraron calificaciones para este período o no hay estudiantes inscritos.</p>
    </div>
    @endif
</div>

@if($students->count() > 0)
<div style="display: none;"
    id="grade-data"
    data-subject-id="{{ $subject->id }}"
    data-trimester="{{ $trimester }}"
    data-year="{{ $year }}"
    data-period-id="{{ $period?->id }}"
    data-bulk-url="{{ route('grades.bulk') }}">
</div>
@endif
@endsection
