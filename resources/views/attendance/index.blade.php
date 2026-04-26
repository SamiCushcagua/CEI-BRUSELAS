@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">



<!-- aqui se introduce la asistencia de los estudiantes y se ven los resultados :) -->
<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>
    <div class="grades-header">
        <div>
            @if($isStudentView ?? false)
            <h1 class="grades-title">Mi asistencia</h1>
            <p class="grades-subtitle">Consulta tu registro de asistencia por materia (solo lectura)</p>
            @else
            <h1 class="grades-title">Control de Asistencia</h1>
            <p class="grades-subtitle">Registra la asistencia semanal de tus estudiantes</p>
            @endif
            @isset($period)
            <p class="grades-subtitle" style="margin-top: 0.35rem; opacity: 0.95;">
                📅 <strong>Periodo vigente:</strong> {{ $period->name }} — {{ $period->year }} · Trimestre {{ $period->trimester }}
            </p>
            @endisset
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

    @if($isStudentView ?? false)
    @if($subjects->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">📚</div>
        <h3 class="empty-title">Sin materias en este periodo</h3>
        <p class="empty-description">No estás inscrito en ninguna materia para el periodo activo.</p>
    </div>
    @else
    <div class="form-container">
        <div class="form-header">
            <h2 class="form-title">Elegir materia</h2>
        </div>
        <div class="form-content">
            <form method="GET" action="{{ route('attendance.index') }}" class="form-grid">
                <div class="form-group">
                    <label class="form-label">Materia</label>
                    <select name="subject_id" class="form-select" required onchange="this.form.submit()">
                        <option value="">Seleccionar materia...</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ (string) request('subject_id') === (string) $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary">📋 Ver tabla de fechas</button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedSubject && count($sundays) > 0)
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h3 class="grades-table-title">{{ $selectedSubject->name }} — todas las clases (domingos) del periodo</h3>
        </div>
        <div class="grades-table-wrapper" style="overflow-x: auto;">
            <table class="grades-table">
                <thead>
                    <tr>
                        <th class="sticky-col">Concepto</th>
                        @foreach($sundays as $sunday)
                        <th class="text-center">{{ \Carbon\Carbon::parse($sunday)->format('d/m/Y') }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th class="sticky-col" scope="row">Asistencia</th>
                        @foreach($sundays as $sunday)
                        @php $rec = $studentAttendanceByDate[$sunday] ?? null; @endphp
                        <td class="text-center">
                            @if($rec)
                                @if($rec->attendance_status == 'present')
                                <span class="status-badge status-approved" title="Presente">✅</span>
                                @elseif($rec->attendance_status == 'late')
                                <span class="status-badge stat-yellow" title="Tarde">⏰</span>
                                @else
                                <span class="status-badge stat-red" title="Ausente">❌</span>
                                @endif
                            @else
                                <span class="status-badge stat-red" title="Sin registro">—</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    <tr>
                        <th class="sticky-col" scope="row">Versículo</th>
                        @foreach($sundays as $sunday)
                        @php $rec = $studentAttendanceByDate[$sunday] ?? null; @endphp
                        <td class="text-center">
                            @if($rec)
                                @if($rec->bible_verse_delivered)
                                <span class="status-badge stat-green" title="Entregado">📖</span>
                                @else
                                <span class="status-badge stat-red" title="No entregado">📖</span>
                                @endif
                            @else
                                <span class="status-badge stat-red">—</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @elseif($selectedSubject && count($sundays) === 0)
    <div class="empty-state">
        <p>No hay fechas de clase (domingos) definidas para este periodo.</p>
    </div>
    @endif
    @endif

    @else

    @if($subjects->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">📚</div>
        <h3 class="empty-title">Sin materias asignadas</h3>
        <p class="empty-description">No tienes materias asignadas en el periodo activo.</p>
    </div>
    @else
    <!-- Una materia: sin desplegable; varias: elegir materia. La fecha recarga la página sola. -->
    <div class="form-container">
        <div class="form-header">
            <h2 class="form-title">Seleccionar clase</h2>
            @if($subjects->count() === 1)
            <p class="grades-subtitle" style="margin: 0.35rem 0 0;">Materia: <strong>{{ $subjects->first()->name }}</strong></p>
            @endif
        </div>
        <div class="form-content">
            <form method="GET" action="{{ route('attendance.index') }}" class="form-grid" id="attendance-filter-form">
                @if($subjects->count() > 1)
                <div class="form-group">
                    <label class="form-label">Materia</label>
                    <select name="subject_id" class="form-select" required onchange="this.form.submit()">
                        <option value="">Seleccionar materia...</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}"
                            {{ (string) request('subject_id') === (string) $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label">Fecha de la clase (trimestre {{ $currentTrimester }})</label>
                    @if(count($sundays) > 0)
                    <select name="class_date" class="form-select" required onchange="this.form.submit()">
                        @foreach($sundays as $sunday)
                        <option value="{{ $sunday }}"
                            {{ $selectedDate == $sunday ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($sunday)->format('d/m/Y') }} (domingo)
                        </option>
                        @endforeach
                    </select>
                    <p class="grades-subtitle" style="margin: 0.5rem 0 0; opacity: 0.9;">Al cambiar la fecha se cargan los estudiantes automáticamente.</p>
                    @else
                    <p class="grades-subtitle" style="margin: 0;">No hay domingos de clase definidos para este periodo.</p>
                    @endif
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Lista de estudiantes (solo si se seleccionó materia y fecha): vista por defecto = solo lectura; edición con la barra -->
    @if($selectedSubject && $selectedDate && $students->count() > 0)
    <div class="grades-show-toolbar">
        <button type="button" class="btn btn-primary" id="btn-attendance-edit-open">Modificar asistencia</button>
        <button type="button" class="btn btn-secondary" id="btn-attendance-edit-cancel" hidden>Cancelar</button>
        <button type="submit" form="attendance-day-form" class="btn btn-success" id="btn-attendance-save-all" hidden>Guardar cambios</button>
    </div>

    <div id="attendance-summary-panel">
        <div class="grades-table-container">
            <div class="grades-table-header">
                <h3 class="grades-table-title">
                    Asistencia del día (solo lectura) — {{ $selectedSubject->name }} — {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
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
                                    <!--    <p>{{ $student->email }}</p> -->
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
            <button type="button" class="btn btn-secondary" id="btn-quarter-summary-show">📊 Mostrar tablero de resumen del trimestre</button>
            <button type="button" class="btn btn-secondary" id="btn-quarter-summary-hide" hidden>Ocultar resumen del trimestre</button>
        </div>

        <div id="quarter-summary-panel" hidden>
            <div class="grades-table-container">
                <div class="grades-table-header">
                    <h3 class="grades-table-title">
                        📊 Resumen del Trimestre - {{ $selectedSubject->name }}
                    </h3>
                </div>

                <div class="grades-table-wrapper" style="overflow-x: auto;">
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
                                foreach ($studentRecords as $sunday => $record) {
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
                                            <p>{{ $student->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                @foreach($sundays as $sunday)
                                <td class="text-center">
                                    @php $record = $attendanceData[$student->id][$sunday] ?? null; @endphp
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
        </div>
    </div>


    <div id="attendance-edit-panel" hidden>
        <div class="grades-table-container">
            <div class="grades-table-header">
                <h3 class="grades-table-title">
                    Edición — {{ $selectedSubject->name }} — {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                </h3>
                <p class="grades-subtitle" style="margin: 0;">Ajusta los valores y pulsa <strong>Guardar cambios</strong> arriba.</p>
            </div>

            <form id="attendance-day-form" method="POST" action="{{ route('attendance.store') }}">
                @csrf
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
                                            <p>{{ $student->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <select name="attendance[{{ $student->id }}][attendance_status]" class="form-select">
                                        <option value="none" {{ ! $attendance ? 'selected' : '' }}>
                                            — Sin registro
                                        </option>
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
                                    <input type="checkbox"
                                        name="attendance[{{ $student->id }}][bible_verse_delivered]"
                                        value="1"
                                        {{ ($attendance && $attendance->bible_verse_delivered) ? 'checked' : '' }}
                                        class="form-checkbox">
                                    <label>Entregado</label>
                                </td>

                                <td>
                                    <input type="text"
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

    <script>
    (function () {
        const summary = document.getElementById('attendance-summary-panel');
        const edit = document.getElementById('attendance-edit-panel');
        const form = document.getElementById('attendance-day-form');
        const btnOpen = document.getElementById('btn-attendance-edit-open');
        const btnCancel = document.getElementById('btn-attendance-edit-cancel');
        const btnSave = document.getElementById('btn-attendance-save-all');
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
        const panel = document.getElementById('quarter-summary-panel');
        const btnShow = document.getElementById('btn-quarter-summary-show');
        const btnHide = document.getElementById('btn-quarter-summary-hide');
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

    @if($selectedSubject && $selectedDate && $students->count() == 0)
    <div class="empty-state">
        <div class="empty-icon">👥</div>
        <h3 class="empty-title">No hay estudiantes inscritos</h3>
        <p class="empty-description">Esta materia no tiene estudiantes inscritos.</p>
    </div>
    @endif

    @endif
</div>
@endsection