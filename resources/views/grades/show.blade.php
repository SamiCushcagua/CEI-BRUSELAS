@extends('layouts.app')

@section('content')

<!-- aqui se introduce los puntos de los estudiantes y ademas se ven los resultados :) -->
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}?v={{ filemtime(public_path('css/styles_PROFESOR.css')) }}">
<script src="{{ asset('js/profesor-calificacion.js') }}?v={{ filemtime(public_path('js/profesor-calificacion.js')) }}"></script>

<div class="page-main-btn-wrapper">
    <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    @if($subject ?? false)
        <a href="{{ route('course-plans.show', $subject) }}" class="btn btn-secondary">📋 Plan de curso</a>
    @endif
</div>

<div class="grades-container">
    <div class="grades-header">
        <div>
            <h1 class="grades-title">
                @if($subject)
                    {{ $subject->name }}
                @else
                    Sistema de Calificaciones
                @endif
            </h1>
            @if($subject && $subject->description)
            <p class="grades-subtitle">{{ $subject->description }}</p>
            @elseif($isAdminView ?? false)
            <p class="grades-subtitle">Todas las materias del periodo activo — puedes ver y modificar calificaciones de cualquier clase</p>
            @else
            <p class="grades-subtitle">Registra y consulta las calificaciones de tus estudiantes</p>
            @endif
            @isset($period)
            <p class="grades-subtitle" style="margin-top: 0.35rem; opacity: 0.95;">
                📅 <strong>Periodo vigente:</strong> {{ $period->name }} — {{ $period->year }} · Trimestre {{ $period->trimester }}
            </p>
            @endisset
        </div>
        <div class="grades-info">
            <div><span>Trimestre:</span> {{ $currentTrimester }}</div>
            <div><span>Año:</span> {{ $currentYear }}</div>
            @if($subject)
            <div><span>Estudiantes:</span> {{ $students->count() }}</div>
            @endif
        </div>
    </div>

    @if($subjects->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">📚</div>
        @if($isAdminView ?? false)
        <h3 class="empty-title">No hay materias con profesor asignado</h3>
        <p class="empty-description">No hay clases con profesor asignado en el periodo activo ({{ $currentYear }} — Trimestre {{ $currentTrimester }}).</p>
        <a href="{{ route('admin.period-subject-dashboard') }}" class="btn btn-primary">
            Ir al Tablero Admin
        </a>
        @else
        <h3 class="empty-title">No tienes materias asignadas</h3>
        <p class="empty-description">Contacta al administrador para que te asignen materias como profesor.</p>
        @endif
    </div>
    @else
    <div class="form-container">
        <div class="form-header">
            <h2 class="form-title">Seleccionar materia</h2>
            @if($subjects->count() === 1)
            <p class="grades-subtitle" style="margin: 0.35rem 0 0;">Materia: <strong>{{ $subjects->first()->name }}</strong></p>
            @endif
        </div>
        <div class="form-content">
            <form method="GET" action="{{ route('grades.index') }}" class="form-grid">
                @if($subjects->count() > 1)
                <div class="form-group">
                    <label class="form-label">Materia</label>
                    <select name="subject_id" class="form-select" required onchange="this.form.submit()">
                        <option value="">Seleccionar materia...</option>
                        @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ $subject && (string) $subject->id === (string) $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if($isAdminView ?? false)
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <p class="grades-subtitle" style="margin: 0;">
                        @if($subject)
                        Profesor(es):
                        {{ $subject->professorsForPeriod($period)->orderBy('name')->pluck('name')->filter()->implode(', ') ?: 'Sin asignar' }}
                        @else
                        Elige una materia para ver sus calificaciones.
                        @endif
                    </p>
                </div>
                @endif
            </form>
        </div>
    </div>

  

    @if($subject && $students->count() > 0)
    <div class="grades-show-toolbar" id="grades-edit-toolbar">
        <button type="button" class="btn btn-primary" id="btn-grade-edit-open">Modificar resultados</button>
        <button type="button" class="btn btn-secondary" id="btn-grade-edit-cancel" hidden>Cancelar</button>
        <button type="button" class="btn btn-success" id="btn-grade-save-all" hidden>Guardar cambios</button>
    </div>
    @endif

    <div id="grades-single-subject-panel">
    @if($subject)
    <div id="grades-summary-panel">
        @include('grades.partials.summary-table', [
            'subject' => $subject,
            'students' => $students,
            'grades' => $grades,
            'currentYear' => $currentYear,
            'currentTrimester' => $currentTrimester,
        ])
    </div>

    <div id="grades-edit-panel" hidden>
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h2 class="grades-table-title">Calificaciones del Trimestre — edición</h2>
            <p class="grades-subtitle" style="margin: 0;">Modifica los valores y pulsa <strong>Guardar cambios</strong> arriba.</p>
        </div>

        @if($students->count() > 0)
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
                        <th>Versiculos</th>
                        <th>Otro</th>
                        <th>Promedio</th>
                        <th>Aprobó trim.</th>
                        <th>Diploma entregado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    @php
                    $studentGrade = $grades->where('student_id', $student->id)->first();
                    $diplomaOk = (bool) ($student->pivot->diploma_delivered ?? false);
                    @endphp
                    <tr>
                        <td class="sticky-col">
                            <div class="student-info">
                                <div class="student-avatar">
                                    {{ substr($student->name, 0, 1) }}
                                </div>
                                <div class="student-details">
                                    <h4>{{ $student->name }}</h4>
                                </div>
                            </div>
                        </td>
                        <td>
                            <input type="number"
                                name="task_score"
                                value="{{ $studentGrade->task_score ?? '' }}"
                                min="0" max="100" step="0.01"
                                class="grade-input"
                                data-student-id="{{ $student->id }}"
                                data-field="task_score">
                        </td>
                        <td>
                            <input type="number"
                                name="exam_score1"
                                value="{{ $studentGrade->exam_score1 ?? '' }}"
                                min="0" max="100" step="0.01"
                                class="grade-input"
                                data-student-id="{{ $student->id }}"
                                data-field="exam_score1">
                        </td>
                        <td>
                            <input type="number"
                                name="exam_score2"
                                value="{{ $studentGrade->exam_score2 ?? '' }}"
                                min="0" max="100" step="0.01"
                                class="grade-input"
                                data-student-id="{{ $student->id }}"
                                data-field="exam_score2">
                        </td>
                        <td>
                            <input type="number"
                                name="participation_score"
                                value="{{ $studentGrade->participation_score ?? '' }}"
                                min="0" max="100" step="0.01"
                                class="grade-input"
                                data-student-id="{{ $student->id }}"
                                data-field="participation_score">
                        </td>
                        <td>
                            <input type="number"
                                name="bible_score"
                                value="{{ $studentGrade->bible_score ?? '' }}"
                                min="0" max="100" step="0.01"
                                class="grade-input"
                                data-student-id="{{ $student->id }}"
                                data-field="bible_score">
                        </td>
                        <td>
                            <input type="number"
                                name="text_score"
                                value="{{ $studentGrade->text_score ?? '' }}"
                                min="0" max="100" step="0.01"
                                class="grade-input"
                                data-student-id="{{ $student->id }}"
                                data-field="text_score">
                        </td>
                        <td>
                            <input type="number"
                                name="other_score"
                                value="{{ $studentGrade->other_score ?? '' }}"
                                min="0" max="100" step="0.01"
                                class="grade-input"
                                data-student-id="{{ $student->id }}"
                                data-field="other_score">
                        </td>
                        <td>
                            <span class="average-score" data-student-id="{{ $student->id }}">
                                {{ $studentGrade ? number_format($studentGrade->average_score, 2) : '0.00' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <input type="checkbox"
                                class="grade-checkbox"
                                data-student-id="{{ $student->id }}"
                                data-field="passed"
                                @checked($studentGrade && $studentGrade->passed)>
                        </td>
                        <td class="text-center">
                            <input type="checkbox"
                                class="grade-checkbox"
                                data-student-id="{{ $student->id }}"
                                data-field="diploma_delivered"
                                @checked($diplomaOk)>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <h3 class="empty-title">No hay estudiantes inscritos</h3>
            <p class="empty-description">Esta materia no tiene estudiantes inscritos aún.</p>
        </div>
        @endif
    </div>
    </div>

    <div style="display: none;"
        data-subject-id="{{ $subject->id }}"
        data-trimester="{{ $currentTrimester }}"
        data-year="{{ $currentYear }}"
        data-period-id="{{ $period->id }}"
        data-bulk-url="{{ route('grades.bulk', [], false) }}"
        id="grade-data">
    </div>
    @elseif($subjects->count() > 1)
    <div class="empty-state" id="grades-select-subject-empty">
        <div class="empty-icon">📋</div>
        <h3 class="empty-title">Selecciona una materia</h3>
        <p class="empty-description">Elige la materia en el desplegable de arriba para ver y modificar las calificaciones.</p>
    </div>
    @endif
    </div>

    @if($subjects->count() > 1)
    <div class="grades-show-toolbar grades-all-toggle-toolbar">
        <button type="button" class="btn btn-secondary" id="btn-grades-view-all">
            📚 Ver todas las materias
        </button>
        <button type="button" class="btn btn-secondary" id="btn-grades-view-single" hidden>
            📋 Volver a una materia
        </button>
    </div>
    @endif

    @if($subjects->count() > 1)
    <div id="grades-all-subjects-panel" hidden>
        <div class="grades-all-subjects-header">
            <h2 class="form-title">Todas las materias — solo consulta</h2>
            <p class="grades-subtitle" style="margin: 0.35rem 0 0;">
                Resultados del trimestre {{ $currentTrimester }} · {{ $currentYear }}.
            </p>
        </div>
        @foreach($allSubjectsData as $subjectData)
        <section class="grades-all-subject-block">
            <div class="grades-all-subject-heading">
                <h3>{{ $subjectData['subject']->name }}</h3>
                <span class="grades-all-subject-meta">{{ $subjectData['students']->count() }} estudiante(s)</span>
            </div>
            @include('grades.partials.summary-table', [
                'subject' => $subjectData['subject'],
                'students' => $subjectData['students'],
                'grades' => $subjectData['grades'],
                'currentYear' => $currentYear,
                'currentTrimester' => $currentTrimester,
            ])
        </section>
        @endforeach
    </div>
    @endif
    @endif
</div>
@endsection
