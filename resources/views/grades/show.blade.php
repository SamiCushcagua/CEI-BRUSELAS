@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">
<script src="{{ asset('js/profesor-calificacion.js') }}"></script>

<div class="grades-container">
    <div class="grades-header">
        <div>
            <h1 class="grades-title">{{ $subject->name }}</h1>
            <p class="grades-subtitle">{{ $subject->description }}</p>
        </div>
        <div class="grades-info">
            <div><span>Trimestre:</span> {{ $currentTrimester }}</div>
            <div><span>Año:</span> {{ $currentYear }}</div>
            <div><span>Estudiantes:</span> {{ $students->count() }}</div>
        </div>
    </div>

    <!-- Resumen de Estudiantes -->
   <!-- Resumen de Estudiantes -->
<div class="grades-table-container">
    <div class="grades-table-header">
        <h3>📊 Resumen de Estudiantes - {{ $subject->name }}</h3>
        <div class="grades-table-actions">
            <span class="subject-info">{{ $subject->name }} - {{ $currentYear }} - Trimestre {{ $currentTrimester }}</span>
        </div>
    </div>
    <div class="grades-table-wrapper" style="overflow-x: auto;">
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
                @foreach($students as $student)
                    @php
                        $studentGrade = $grades->where('student_id', $student->id)->first();
                    @endphp
                    <tr>
                        <td>
                            <div class="student-info">
                                <div class="student-avatar">
                                    {{ substr($student->name, 0, 1) }}
                                </div>
                                <div class="student-details">
                                    <h4>{{ $student->name }}</h4>
                                    <p>{{ $student->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->task_score ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->task_score ? number_format($studentGrade->task_score, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->exam_score1 ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->exam_score1 ? number_format($studentGrade->exam_score1, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->exam_score2 ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->exam_score2 ? number_format($studentGrade->exam_score2, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->participation_score ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->participation_score ? number_format($studentGrade->participation_score, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->bible_score ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->bible_score ? number_format($studentGrade->bible_score, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->text_score ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->text_score ? number_format($studentGrade->text_score, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->other_score ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->other_score ? number_format($studentGrade->other_score, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="average-score">
                                {{ $studentGrade ? number_format($studentGrade->average_score, 2) : '0.00' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h2 class="grades-table-title">Calificaciones del Trimestre</h2>
            <div class="grades-table-actions">
                <button onclick="exportToPDF()" class="btn btn-danger btn-small">
                    📄 Exportar PDF
                </button>
                <a href="{{ route('grade-settings.index', $subject) }}" class="btn btn-secondary btn-small">
                    ⚙️ Configuración
                </a>
            </div>
        </div>

        @if($students->count() > 0)
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
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    @php
                    $studentGrade = $grades->where('student_id', $student->id)->first();
                    @endphp
                    <tr>
                        <td>
                            <div class="student-info">
                                <div class="student-avatar">
                                    {{ substr($student->name, 0, 1) }}
                                </div>
                                <div class="student-details">
                                    <h4>{{ $student->name }}</h4>
                                    <p>{{ $student->email }}</p>
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
                        <td>
                            <button onclick="saveStudentGrade(this.dataset.studentId)"
                                class="save-btn"
                                data-student-id="{{ $student->id }}">
                                💾 Guardar
                            </button>
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

    <div class="mt-20">
        <a href="{{ route('grades.index') }}" class="btn btn-secondary">
            ← Volver a Materias
        </a>
    </div>
</div>



<!-- Datos para JavaScript -->
<div style="display: none;"
    data-subject-id="{{ $subject->id }}"
    data-trimester="{{ $currentTrimester }}"
    data-year="{{ $currentYear }}"
    id="grade-data">
</div>
@endsection