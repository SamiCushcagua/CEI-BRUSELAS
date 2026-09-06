@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}?v={{ filemtime(public_path('css/styles_PROFESOR.css')) }}">

<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>

    <div class="grades-header">
        <div>
            <h1 class="grades-title">Historial por trimestre</h1>
            <p class="grades-subtitle">Consulta calificaciones y asistencias de un periodo (solo lectura)</p>
        </div>
    </div>

    @if($noPeriods ?? false)
        <div class="empty-state">
            <div class="empty-icon">📅</div>
            <h3 class="empty-title">No hay periodos configurados</h3>
        </div>
    @else
        <div class="form-container">
            <div class="form-header">
                <h2 class="form-title">Seleccionar trimestre</h2>
            </div>
            <div class="form-content">
                <form method="GET" action="{{ route('admin.period-history') }}" class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="period_id">Periodo</label>
                        <select name="period_id" id="period_id" class="form-select" onchange="this.form.submit()">
                            @foreach($periods as $p)
                                <option value="{{ $p->id }}" @selected((string) $period->id === (string) $p->id)>
                                    {{ $p->name }} — {{ $p->year }}, trim. {{ $p->trimester }}
                                    @if($p->is_active) (vigente) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group form-actions">
                        <button type="submit" class="btn btn-success w-full">Ver historial</button>
                    </div>
                </form>
            </div>
        </div>

        <p class="grades-subtitle" style="margin: 1rem 0;">
            Mostrando: <strong>{{ $period->name }}</strong>
            ({{ $period->year }} · Trimestre {{ $period->trimester }})
            @if($period->start_date && $period->end_date)
                — {{ $period->start_date->format('d/m/Y') }} a {{ $period->end_date->format('d/m/Y') }}
            @endif
        </p>

        @if($courseBlocks->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3 class="empty-title">Sin cursos con maestro y alumnos</h3>
                <p class="empty-description">En este periodo no hay materias con profesor asignado y estudiantes inscritos.</p>
            </div>
        @else
            @foreach($courseBlocks as $block)
                @php
                    $subject = $block['subject'];
                    $professors = $block['professors'];
                    $students = $block['students'];
                    $gradesByStudent = $block['gradesByStudent'];
                    $attendanceData = $block['attendanceData'];
                @endphp

                <div class="grades-table-container history-course-block">
                    <div class="grades-table-header">
                        <h3 class="grades-table-title">
                            @if($subject->Nivel)
                                Curso {{ $subject->Nivel }} —
                            @endif
                            {{ $subject->name }}
                        </h3>
                        <div class="grades-table-actions">
                            <span class="subject-info">
                                Maestro(s):
                                @if($professors->isEmpty())
                                    Sin asignar
                                @else
                                    {{ $professors->pluck('name')->implode(', ') }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="grades-table-header" style="border-top: 1px solid #e5e7eb;">
                        <h3 class="grades-table-title" style="font-size: 1rem;">📝 Resumen de calificaciones</h3>
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
                                    <th>Versículos</th>
                                    <th>Otro</th>
                                    <th>Promedio</th>
                                    <th>Aprobó</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                    @php $g = $gradesByStudent->get((int) $student->id); @endphp
                                    <tr>
                                        <td class="sticky-col">
                                            <div class="student-info">
                                                <div class="student-avatar">{{ substr($student->name, 0, 1) }}</div>
                                                <div class="student-details">
                                                    <h4>{{ $student->name }}</h4>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">{{ $g && $g->task_score !== null ? number_format((float) $g->task_score, 2) : '—' }}</td>
                                        <td class="text-center">{{ $g && $g->exam_score1 !== null ? number_format((float) $g->exam_score1, 2) : '—' }}</td>
                                        <td class="text-center">{{ $g && $g->exam_score2 !== null ? number_format((float) $g->exam_score2, 2) : '—' }}</td>
                                        <td class="text-center">{{ $g && $g->participation_score !== null ? number_format((float) $g->participation_score, 2) : '—' }}</td>
                                        <td class="text-center">{{ $g && $g->bible_score !== null ? number_format((float) $g->bible_score, 2) : '—' }}</td>
                                        <td class="text-center">{{ $g && $g->text_score !== null ? number_format((float) $g->text_score, 2) : '—' }}</td>
                                        <td class="text-center">{{ $g && $g->other_score !== null ? number_format((float) $g->other_score, 2) : '—' }}</td>
                                        <td class="text-center">
                                            @if($g)
                                                <strong>{{ number_format($g->average_score, 2) }}</strong>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($g && $g->passed)
                                                <span class="status-badge status-approved">Sí</span>
                                            @elseif($g)
                                                <span class="status-badge stat-red">No</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" style="text-align: center; color: #6b7280;">Sin alumnos</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="grades-table-header" style="border-top: 1px solid #e5e7eb;">
                        <h3 class="grades-table-title" style="font-size: 1rem;">📅 Resumen de asistencias del trimestre</h3>
                    </div>
                    @if(count($sundays) === 0)
                        <p class="grades-subtitle" style="padding: 1rem;">No hay fechas de clase definidas para este periodo.</p>
                    @else
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
                    @endif
                </div>
            @endforeach
        @endif
    @endif
</div>

<style>
.history-course-block {
    margin-bottom: 2rem;
}
.attendance-summary {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.2rem;
}
</style>
@endsection
