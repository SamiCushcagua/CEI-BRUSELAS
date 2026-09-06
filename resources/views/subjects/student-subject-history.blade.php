@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}?v={{ filemtime(public_path('css/styles_PROFESOR.css')) }}">

<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
        <a href="{{ route('students.subjects', $student) }}" class="btn btn-secondary">← Mis cursos</a>
    </div>

    <div class="grades-header">
        <div>
            <h1 class="grades-title">{{ $subject->name }}</h1>
            <p class="grades-subtitle">
                Historial — {{ $period->name }}
                ({{ $period->year }}, trim. {{ $period->trimester }})
            </p>
            <p class="grades-subtitle">Alumno: {{ $student->name }}</p>
        </div>
    </div>

    <section class="history-detail-section">
        <h2 class="history-detail-title">Maestros de ese periodo</h2>
        @if($professors->isEmpty())
            <p class="history-detail-empty">Sin profesores registrados para este periodo.</p>
        @else
            <ul class="history-professors-list">
                @foreach($professors as $professor)
                    <li>{{ $professor->name }}@if($professor->email) <span>({{ $professor->email }})</span>@endif</li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="history-detail-section">
        <h2 class="history-detail-title">Calificaciones a detalle</h2>
        @if(! $grade)
            <p class="history-detail-empty">No hay calificaciones registradas para este curso en ese periodo.</p>
        @else
            <div class="grades-table-wrapper" style="overflow-x: auto;">
                <table class="grades-table history-detail-grades">
                    <thead>
                        <tr>
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
                        <tr>
                            <td class="text-center">{{ $grade->task_score !== null ? number_format((float) $grade->task_score, 2) : '—' }}</td>
                            <td class="text-center">{{ $grade->exam_score1 !== null ? number_format((float) $grade->exam_score1, 2) : '—' }}</td>
                            <td class="text-center">{{ $grade->exam_score2 !== null ? number_format((float) $grade->exam_score2, 2) : '—' }}</td>
                            <td class="text-center">{{ $grade->participation_score !== null ? number_format((float) $grade->participation_score, 2) : '—' }}</td>
                            <td class="text-center">{{ $grade->bible_score !== null ? number_format((float) $grade->bible_score, 2) : '—' }}</td>
                            <td class="text-center">{{ $grade->text_score !== null ? number_format((float) $grade->text_score, 2) : '—' }}</td>
                            <td class="text-center">{{ $grade->other_score !== null ? number_format((float) $grade->other_score, 2) : '—' }}</td>
                            <td class="text-center"><strong>{{ number_format($grade->average_score, 2) }}</strong></td>
                            <td class="text-center">
                                @if($grade->passed)
                                    <span class="status-badge status-approved">Sí</span>
                                @else
                                    <span class="status-badge stat-red">No</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @if($grade->notes)
                <p style="margin-top: 0.75rem; color: #4b5563;"><strong>Notas:</strong> {{ $grade->notes }}</p>
            @endif
        @endif
    </section>

    <section class="history-detail-section">
        <h2 class="history-detail-title">Asistencias a detalle</h2>
        @if(empty($sundays))
            <p class="history-detail-empty">No hay fechas de clase definidas para ese periodo.</p>
        @else
            @php
                $present = collect($attendanceByDate)->filter(fn ($r) => $r && $r->attendance_status === 'present')->count();
                $absent = collect($attendanceByDate)->filter(fn ($r) => $r && $r->attendance_status === 'absent')->count();
                $late = collect($attendanceByDate)->filter(fn ($r) => $r && $r->attendance_status === 'late')->count();
            @endphp
            <p class="history-attendance-summary">
                Presente: <strong>{{ $present }}</strong>
                · Ausente: <strong>{{ $absent }}</strong>
                · Tarde: <strong>{{ $late }}</strong>
                · Clases: <strong>{{ count($sundays) }}</strong>
            </p>
            <div class="grades-table-wrapper" style="overflow-x: auto;">
                <table class="grades-table history-detail-grades">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Versículo</th>
                            <th>Notas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sundays as $date)
                            @php $rec = $attendanceByDate[$date] ?? null; @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                                <td>
                                    @if(! $rec || $rec->attendance_status === 'none' || $rec->attendance_status === null)
                                        —
                                    @elseif($rec->attendance_status === 'present')
                                        Presente
                                    @elseif($rec->attendance_status === 'absent')
                                        Ausente
                                    @elseif($rec->attendance_status === 'late')
                                        Tarde
                                    @else
                                        {{ $rec->attendance_status }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($rec && $rec->bible_verse_delivered)
                                        ✅
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $rec->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>

<style>
.history-detail-section {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.history-detail-title {
    margin: 0 0 0.85rem;
    font-size: 1.1rem;
    color: #1e3a5f;
    border-bottom: 2px solid #d4a017;
    padding-bottom: 0.35rem;
}
.history-detail-empty {
    color: #6b7280;
    margin: 0;
}
.history-professors-list {
    margin: 0;
    padding-left: 1.25rem;
}
.history-professors-list li {
    margin-bottom: 0.35rem;
}
.history-professors-list span {
    color: #6b7280;
    font-size: 0.9rem;
}
.history-attendance-summary {
    margin: 0 0 0.85rem;
    color: #374151;
}
.history-detail-grades th,
.history-detail-grades td {
    padding: 0.55rem 0.65rem;
}
</style>
@endsection
