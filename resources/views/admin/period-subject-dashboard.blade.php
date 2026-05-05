@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}?v={{ filemtime(public_path('css/styles_PROFESOR.css')) }}">

<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>

    <div class="grades-header">
        <div>
            <h1 class="grades-title">Admin - Asistencia y Calificaciones</h1>
            <p class="grades-subtitle">Control por Periodo y Materia (modificable)</p>
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

    @if($students->count() > 0)
    <div class="grades-show-toolbar">
        <button type="button" class="btn btn-primary" id="btn-admin-attendance-edit-open">Modificar asistencia</button>
        <button type="button" class="btn btn-secondary" id="btn-admin-attendance-edit-cancel" hidden>Cancelar</button>
        <button type="submit" form="admin-attendance-day-form" class="btn btn-success" id="btn-admin-attendance-save-all" hidden>Guardar cambios</button>
    </div>

    <div id="admin-attendance-summary-panel">
        <div class="grades-table-container">
            <div class="grades-table-header">
                <h3 class="grades-table-title">
                    Asistencia del día (solo lectura) - {{ $selectedSubject->name }} - {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                </h3>
            </div>

            <div class="grades-table-wrapper" style="overflow-x: auto;">
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th class="sticky-col">Estudiante</th>
                            <th>Asistencia / puntualidad</th>
                            <th>Versículo</th>
                            <th>Notas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php $attendance = $attendanceRecords->get($student->id); @endphp
                        <tr>
                            <td class="sticky-col">
                                <div class="student-info">
                                    <div class="student-avatar">{{ substr($student->name, 0, 1) }}</div>
                                    <div class="student-details">
                                        <h4>{{ $student->name }}</h4>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($attendance)
                                    @if($attendance->attendance_status == 'present')
                                    <span class="status-badge status-approved">✅ Presente</span>
                                    @elseif($attendance->attendance_status == 'late')
                                    <span class="status-badge stat-yellow">⏰ Tarde</span>
                                    @else
                                    <span class="status-badge stat-red">❌ Ausente</span>
                                    @endif
                                @else
                                    <span class="status-badge stat-red">Sin registro</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($attendance && $attendance->bible_verse_delivered)
                                <span class="status-badge stat-green">📖 Entregado</span>
                                @else
                                <span class="status-badge stat-red">📖 No entregado</span>
                                @endif
                            </td>
                            <td>{{ $attendance && $attendance->notes ? $attendance->notes : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grades-show-toolbar quarter-summary-toolbar">
            <button type="button" class="btn btn-secondary" id="btn-admin-quarter-summary-show">📊 Mostrar tablero de resumen del trimestre</button>
            <button type="button" class="btn btn-secondary" id="btn-admin-quarter-summary-hide" hidden>Ocultar resumen del trimestre</button>
        </div>
    </div>

    <div id="admin-attendance-edit-panel" hidden>
        <div class="grades-table-container">
            <div class="grades-table-header">
                <h3 class="grades-table-title">
                    Edición - {{ $selectedSubject->name }} - {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                </h3>
                <p class="grades-subtitle" style="margin: 0;">Ajusta los valores y pulsa <strong>Guardar cambios</strong> arriba.</p>
            </div>

            <form id="admin-attendance-day-form" method="POST" action="{{ route('admin.period-subject-dashboard.attendance.save') }}">
                @csrf
                <input type="hidden" name="period_id" value="{{ $period->id }}">
                <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                <input type="hidden" name="class_date" value="{{ $selectedDate }}">

                <div class="grades-table-wrapper" style="overflow-x: auto;">
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
                            @php $attendance = $attendanceRecords->get($student->id); @endphp
                            <tr>
                                <td class="sticky-col">
                                    <div class="student-info">
                                        <div class="student-avatar">{{ substr($student->name, 0, 1) }}</div>
                                        <div class="student-details">
                                            <h4>{{ $student->name }}</h4>
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
            </form>
        </div>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">👥</div>
        <h3 class="empty-title">No hay estudiantes inscritos</h3>
        <p class="empty-description">Para esta materia/profesor/periodo no hay estudiantes.</p>
    </div>
    @endif

    <!-- Resumen trimestral -->
    <div id="admin-quarter-summary-panel" @if($students->count() > 0)hidden @endif>
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
                            {{ \Carbon\Carbon::parse($sunday)->format('d/m/Y') }}
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
                                 <!--   <p>{{ $student->email }}</p> -->
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

    <!-- Resumen global trimestral (todas las materias con profesor asignado) -->
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h3 class="grades-table-title">
                📚 Resumen global del trimestre (todas las materias)
            </h3>
        </div>

        @if($allSubjectSummaries->isEmpty())
        <div class="empty-state">
            <p>No hay materias con profesor asignado para este periodo.</p>
        </div>
        @else
            @foreach($allSubjectSummaries as $summary)
            <div class="grades-table-header" style="border-top: 1px solid #e5e7eb;">
                <h3 class="grades-table-title">
                    {{ $summary['subject']->Nivel ? 'Curso ' . $summary['subject']->Nivel . ' - ' : '' }}{{ $summary['subject']->name }}
                </h3>
                <div class="grades-table-actions">
                    <span class="subject-info">
                        Profesor(es):
                        @if($summary['professors']->isEmpty())
                            Sin asignar
                        @else
                            {{ $summary['professors']->map(fn($p) => $p->name ?? $p->email)->implode(', ') }}
                        @endif
                    </span>
                </div>
            </div>
            <div class="grades-table-wrapper" style="overflow-x: auto;">
                <table class="grades-table">
                    <thead>
                        <tr>
                            <th class="sticky-col">Estudiante</th>
                            @foreach($sundays as $sunday)
                            <th class="text-center">
                                {{ \Carbon\Carbon::parse($sunday)->format('d/m/Y') }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary['students'] as $student)
                        @php
                            $absentCount = 0;
                            $studentRecords = $summary['attendanceData'][$student->id] ?? [];
                            foreach ($studentRecords as $record) {
                                if ($record && $record->attendance_status === 'absent') {
                                    $absentCount++;
                                }
                            }
                        @endphp
                        <tr class="{{ $absentCount >= 3 ? 'row-high-absent' : '' }}">
                            <td class="sticky-col">
                                <div class="student-info">
                                    <div class="student-avatar">{{ substr($student->name, 0, 1) }}</div>
                                    <div class="student-details">
                                        <h4>{{ $student->name }}</h4>
                                    </div>
                                </div>
                            </td>
                            @foreach($sundays as $sunday)
                            <td class="text-center">
                                @php $record = $summary['attendanceData'][$student->id][$sunday] ?? null; @endphp
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
                        @empty
                        <tr>
                            <td class="sticky-col">Sin estudiantes inscritos</td>
                            @foreach($sundays as $sunday)
                            <td class="text-center">—</td>
                            @endforeach
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endforeach
        @endif
    </div>
    </div>

    <script>
    (function () {
        const summary = document.getElementById('admin-attendance-summary-panel');
        const edit = document.getElementById('admin-attendance-edit-panel');
        const form = document.getElementById('admin-attendance-day-form');
        const btnOpen = document.getElementById('btn-admin-attendance-edit-open');
        const btnCancel = document.getElementById('btn-admin-attendance-edit-cancel');
        const btnSave = document.getElementById('btn-admin-attendance-save-all');
        if (!summary || !edit || !btnOpen || !form) return;

        function enterEditMode() {
            summary.hidden = true;
            edit.hidden = false;
            btnOpen.hidden = true;
            if (btnCancel) btnCancel.hidden = false;
            if (btnSave) btnSave.hidden = false;
        }

        function leaveEditMode() {
            form.reset();
            summary.hidden = false;
            edit.hidden = true;
            btnOpen.hidden = false;
            if (btnCancel) btnCancel.hidden = true;
            if (btnSave) btnSave.hidden = true;
        }

        btnOpen.addEventListener('click', enterEditMode);
        if (btnCancel) btnCancel.addEventListener('click', leaveEditMode);
    })();

    (function () {
        const panel = document.getElementById('admin-quarter-summary-panel');
        const btnShow = document.getElementById('btn-admin-quarter-summary-show');
        const btnHide = document.getElementById('btn-admin-quarter-summary-hide');
        if (!panel || !btnShow || !btnHide) return;

        btnShow.addEventListener('click', function () {
            panel.hidden = false;
            btnShow.hidden = true;
            btnHide.hidden = false;
        });

        btnHide.addEventListener('click', function () {
            panel.hidden = true;
            btnShow.hidden = false;
            btnHide.hidden = true;
        });
    })();
    </script>

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

