@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">
<script src="{{ asset('js/profesor-calificacion.js') }}"></script>

<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>

    <div class="grades-header">
        <div>
            <h1 class="grades-title">Admin - Asistencia y Calificaciones</h1>
            <p class="grades-subtitle">Control por Periodo, Materia y Profesor (modificable)</p>
        </div>
        <div class="grades-info">
            <div><span>Periodo:</span> {{ $period->year }} - Trimestre {{ $period->trimester }}</div>
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

    <div class="form-container">
        <div class="form-header">
            <h2 class="form-title">Seleccionar Contexto</h2>
        </div>

        <div class="form-content">
            <form method="GET" action="{{ route('admin.period-subject-dashboard') }}" class="form-grid">
                <div class="form-group">
                    <label class="form-label">Periodo</label>
                    <select name="period_id" class="form-select" required>
                        @foreach($periods as $p)
                        <option value="{{ $p->id }}" {{ (string)$p->id === (string)$period->id ? 'selected' : '' }}>
                            {{ $p->year }} - Trimestre {{ $p->trimester }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Materia</label>
                    <select name="subject_id" class="form-select" {{ isset($selectedSubject) && $selectedSubject ? '' : 'disabled' }}>
                        <option value="">Seleccionar materia...</option>
                        @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ isset($selectedSubject) && $selectedSubject && (string)$s->id === (string)$selectedSubject->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Profesor</label>
                    <select name="professor_id" class="form-select" {{ isset($selectedProfessor) && $selectedProfessor ? '' : 'disabled' }}>
                        <option value="">Seleccionar profesor...</option>
                        @foreach($professors as $prof)
                        <option value="{{ $prof->id }}" {{ isset($selectedProfessor) && $selectedProfessor && (string)$prof->id === (string)$selectedProfessor->id ? 'selected' : '' }}>
                            {{ $prof->name ?? $prof->email }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Domingo (Fecha de clase)</label>
                    <select name="class_date" class="form-select" {{ !($sundays && count($sundays)) ? 'disabled' : '' }}>
                        @foreach($sundays as $sunday)
                        <option value="{{ $sunday }}" {{ (string)$sunday === (string)$selectedDate ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($sunday)->format('d/m/Y') }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group form-actions">
                    <button type="submit" class="btn btn-success w-full">Aplicar</button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedSubject && $selectedDate)

    <!-- Asistencia editable -->
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h3 class="grades-table-title">
                Asistencia - {{ $selectedSubject->name }} @if($selectedProfessor) - {{ $selectedProfessor->name ?? $selectedProfessor->email }} @endif - {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
            </h3>
        </div>

        @if($students->count() > 0)
        <form method="POST" action="{{ route('admin.period-subject-dashboard.attendance.save') }}">
            @csrf
            <input type="hidden" name="period_id" value="{{ $period->id }}">
            <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
            <input type="hidden" name="class_date" value="{{ $selectedDate }}">

            <div class="grades-table-wrapper">
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th class="sticky-col">Estudiante</th>
                            <th>Asistencia/Puntualidad</th>
                            <th>Versículo Bíblico</th>
                            <th>Notas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php
                            $attendance = $attendanceRecords->get($student->id);
                        @endphp
                        <tr>
                            <td class="sticky-col">
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
                                <select name="attendance[{{ $student->id }}][attendance_status]" class="form-select">
                                    <option value="present" {{ ($attendance && $attendance->attendance_status == 'present') ? 'selected' : '' }}>
                                        ✅ Presente
                                    </option>
                                    <option value="absent" {{ ($attendance && $attendance->attendance_status == 'absent') ? 'selected' : '' }}>
                                        ❌ Ausente
                                    </option>
                                    <option value="late" {{ ($attendance && $attendance->attendance_status == 'late') ? 'selected' : '' }}>
                                        ⏰ Tarde
                                    </option>
                                </select>
                            </td>

                            <td>
                                <input
                                    type="checkbox"
                                    name="attendance[{{ $student->id }}][bible_verse_delivered]"
                                    value="1"
                                    {{ ($attendance && $attendance->bible_verse_delivered) ? 'checked' : '' }}
                                    class="form-checkbox">
                                <label>Entregado</label>
                            </td>

                            <td>
                                <input
                                    type="text"
                                    name="attendance[{{ $student->id }}][notes]"
                                    value="{{ $attendance->notes ?? '' }}"
                                    placeholder="Notas adicionales..."
                                    class="form-input">
                            </td>
                        </tr>

                        <input type="hidden" name="attendance[{{ $student->id }}][student_id]" value="{{ $student->id }}">
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="form-content">
                <button type="submit" class="btn btn-success w-full">💾 Guardar Asistencia</button>
            </div>
        </form>
        @else
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <h3 class="empty-title">No hay estudiantes inscritos</h3>
            <p class="empty-description">Para esta materia/profesor/periodo no hay estudiantes.</p>
        </div>
        @endif
    </div>

    <!-- Resumen trimestral -->
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h3 class="grades-table-title">
                📊 Resumen del Trimestre - {{ $selectedSubject->name }}
            </h3>
        </div>

        <div class="grades-table-wrapper">
            <table class="grades-table">
                <thead>
                    <tr>
                        <th class="sticky-col">Estudiante</th>
                        @foreach($sundays as $sunday)
                        <th class="text-center">
                            {{ \Carbon\Carbon::parse($sunday)->format('d/m') }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    @php
                        $absentCount = 0;
                        $studentRecords = $attendanceData[$student->id] ?? [];
                        foreach ($studentRecords as $record) {
                            if ($record && $record->attendance_status === 'absent') {
                                $absentCount++;
                            }
                        }
                    @endphp
                    <tr class="{{ $absentCount > 3 ? 'row-high-absent' : '' }}">
                        <td class="sticky-col">
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

                        @foreach($sundays as $sunday)
                        <td class="text-center">
                            @php
                                $record = $attendanceData[$student->id][$sunday] ?? null;
                            @endphp

                            @if($record)
                            <div class="attendance-summary">
                                @if($record->attendance_status == 'present')
                                <span class="status-badge status-approved">✅</span>
                                @elseif($record->attendance_status == 'late')
                                <span class="status-badge stat-yellow">⏰</span>
                                @else
                                <span class="status-badge stat-red">❌</span>
                                @endif

                                @if($record->bible_verse_delivered)
                                <span class="status-badge stat-green">📖</span>
                                @else
                                <span class="status-badge stat-red">📖</span>
                                @endif
                            </div>
                            @else
                            <span class="status-badge stat-red">-</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Calificaciones (editable) -->
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h2 class="grades-table-title">Calificaciones del Trimestre - {{ $selectedSubject->name }}</h2>
            <div class="grades-table-actions">
                <span class="subject-info">{{ $period->year }} - Trimestre {{ $period->trimester }}</span>
            </div>
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
                        <th>Texto</th>
                        <th>Otro</th>
                        <th>Promedio</th>
                        <th>Aprobó trim.</th>
                        <th>Diploma entregado</th>
                        <th>Acciones</th>
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
                                    <p>{{ $student->email }}</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <input type="number" name="task_score" value="{{ $studentGrade->task_score ?? '' }}" min="0" max="100" step="0.01" class="grade-input" data-student-id="{{ $student->id }}" data-field="task_score">
                        </td>
                        <td>
                            <input type="number" name="exam_score1" value="{{ $studentGrade->exam_score1 ?? '' }}" min="0" max="100" step="0.01" class="grade-input" data-student-id="{{ $student->id }}" data-field="exam_score1">
                        </td>
                        <td>
                            <input type="number" name="exam_score2" value="{{ $studentGrade->exam_score2 ?? '' }}" min="0" max="100" step="0.01" class="grade-input" data-student-id="{{ $student->id }}" data-field="exam_score2">
                        </td>
                        <td>
                            <input type="number" name="participation_score" value="{{ $studentGrade->participation_score ?? '' }}" min="0" max="100" step="0.01" class="grade-input" data-student-id="{{ $student->id }}" data-field="participation_score">
                        </td>
                        <td>
                            <input type="number" name="bible_score" value="{{ $studentGrade->bible_score ?? '' }}" min="0" max="100" step="0.01" class="grade-input" data-student-id="{{ $student->id }}" data-field="bible_score">
                        </td>
                        <td>
                            <input type="number" name="text_score" value="{{ $studentGrade->text_score ?? '' }}" min="0" max="100" step="0.01" class="grade-input" data-student-id="{{ $student->id }}" data-field="text_score">
                        </td>
                        <td>
                            <input type="number" name="other_score" value="{{ $studentGrade->other_score ?? '' }}" min="0" max="100" step="0.01" class="grade-input" data-student-id="{{ $student->id }}" data-field="other_score">
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

                        <td>
                            <button
                                onclick="saveStudentGrade(this.dataset.studentId)"
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
            <p class="empty-description">Para esta materia/profesor/periodo no hay estudiantes.</p>
        </div>
        @endif
    </div>

    <!-- Datos para JavaScript (POST /grades) -->
    <div style="display: none;" data-subject-id="{{ $selectedSubject->id }}" data-trimester="{{ $currentTrimester }}" data-year="{{ $currentYear }}" data-period-id="{{ $period->id }}" id="grade-data"></div>

    @endif

    @if(!$selectedSubject || !$selectedDate)
    <div class="empty-state">
        <div class="empty-icon">🧭</div>
        <h3 class="empty-title">Selecciona un periodo</h3>
        <p class="empty-description">Elige Periodo, Materia y fecha de clase (domingo) para ver y modificar asistencia y calificaciones. La asistencia es compartida entre los profesores de la materia.</p>
    </div>
    @endif
</div>
@endsection

