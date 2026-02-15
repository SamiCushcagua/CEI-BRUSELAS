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
            <h1 class="grades-title">Control de Asistencia</h1>
            <p class="grades-subtitle">Registra la asistencia semanal de tus estudiantes</p>
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

    <!-- Formulario de selección -->
    <div class="form-container">
        <div class="form-header">
            <h2 class="form-title">Seleccionar Clase</h2>
        </div>
        <div class="form-content">
            <form method="GET" action="{{ route('attendance.index') }}" class="form-grid">
                <div class="form-group">
                    <label class="form-label">Materia</label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">Seleccionar materia...</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}"
                            {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha de la Clase (Trimestre {{ $currentTrimester }})</label>
                    <select name="class_date" class="form-select" required>
                        <option value="">Seleccionar domingo...</option>
                        @foreach($sundays as $sunday)
                        <option value="{{ $sunday }}"
                            {{ $selectedDate == $sunday ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($sunday)->format('d/m/Y') }} (Domingo)
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary">
                        📅 Cargar Estudiantes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de estudiantes (solo si se seleccionó materia y fecha) -->
    @if($selectedSubject && $selectedDate && $students->count() > 0)
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h3 class="grades-table-title">
                Asistencia - {{ $selectedSubject->name }} - {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
            </h3>
        </div>

        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
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

            <div class="form-content">
                <button type="submit" class="btn btn-success w-full">
                    💾 Guardar Asistencia
                </button>
            </div>
        </form>
    </div>

    <!-- Resumen del Trimestre -->
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
                        foreach ($studentRecords as $sunday => $record) {
                            if ($record && $record->attendance_status === 'absent') {
                                $absentCount++;
                            }
                        }
                    @endphp
                    <tr class="{{ $absentCount >= 3 ? 'row-high-absent' : '' }}">
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
                                <!-- Asistencia -->
                                @if($record->attendance_status == 'present')
                                <span class="status-badge status-approved">✅</span>
                                @elseif($record->attendance_status == 'late')
                                <span class="status-badge stat-yellow">⏰</span>
                                @else
                                <span class="status-badge stat-red">❌</span>
                                @endif

                                <!-- Versículo bíblico -->
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
    @endif

    @if($selectedSubject && $selectedDate && $students->count() == 0)
    <div class="empty-state">
        <div class="empty-icon">👥</div>
        <h3 class="empty-title">No hay estudiantes inscritos</h3>
        <p class="empty-description">Esta materia no tiene estudiantes inscritos.</p>
    </div>
    @endif
</div>
@endsection