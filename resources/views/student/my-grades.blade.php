@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}?v={{ filemtime(public_path('css/styles_PROFESOR.css')) }}">

<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
        <a href="{{ route('students.subjects', auth()->user()) }}" class="btn btn-secondary">📚 Mi curso</a>
    </div>

    <div class="grades-header">
        <div>
            <h1 class="grades-title">Mis calificaciones</h1>
            <p class="grades-subtitle">Puntajes del periodo vigente (solo lectura)</p>
            @if($period ?? null)
            <p class="grades-subtitle" style="margin-top: 0.5rem;">
                <strong>Periodo actual:</strong>
                {{ $period->name }} — {{ $period->year }}, trimestre {{ $period->trimester }}
                <span class="status-badge status-approved" style="margin-left: 0.35rem;">Vigente</span>
            </p>
            @endif
        </div>
        @isset($period)
        <div class="grades-info">
            <div><span>Periodo:</span> {{ $period->year }} — Trimestre {{ $period->trimester }}</div>
        </div>
        @endisset
    </div>

    @if($noPeriodConfigured ?? false)
    <div class="empty-state">
        <div class="empty-icon">📅</div>
        <h3 class="empty-title">No hay periodos configurados</h3>
        <p class="empty-description">Cuando el administrador active un periodo, podrás ver aquí tus calificaciones.</p>
    </div>
    @else

    @if($period->start_date && $period->end_date)
    <div class="form-container" style="margin-top: 0.5rem;">
        <div class="form-content" style="line-height: 1.6;">
            <p style="margin: 0; opacity: 0.95;">
                📅 Del {{ \Carbon\Carbon::parse($period->start_date)->format('d/m/Y') }}
                al {{ \Carbon\Carbon::parse($period->end_date)->format('d/m/Y') }}
            </p>
            <p style="margin: 0.5rem 0 0;">
                Para cursos de trimestres anteriores, ve a
                <a href="{{ route('students.subjects', auth()->user()) }}">Mi curso → Historial</a>.
            </p>
        </div>
    </div>
    @endif

    @if($rows->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">📚</div>
        <h3 class="empty-title">Sin materias en el periodo actual</h3>
        <p class="empty-description">No estás inscrito en ninguna materia del periodo vigente.</p>
    </div>
    @else
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h3 class="grades-table-title">Calificaciones del trimestre actual</h3>
        </div>
        <div class="grades-table-wrapper" style="overflow-x: auto;">
            <table class="grades-table">
                <thead>
                    <tr>
                        <th class="sticky-col">Materia</th>
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
                    @foreach($rows as $row)
                    @php
                        $g = $row->grade;
                    @endphp
                    <tr>
                        <td class="sticky-col">
                            <strong>{{ $row->subject->name }}</strong>
                            @if(!empty($row->subject->Nivel))
                            <div style="font-size: 0.88rem; opacity: 0.9; font-weight: normal; margin-top: 0.2rem;">{{ $row->subject->Nivel }}</div>
                            @endif
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
                                {{ number_format($g->average_score, 2) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-center">
                            @if($g && $g->passed)
                                <span class="status-badge status-approved" title="Aprobado">✅</span>
                            @elseif($g)
                                <span class="status-badge stat-red" title="No aprobado">—</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row->diploma_delivered)
                                <span class="status-badge stat-green" title="Entregado">✅</span>
                            @else
                                <span class="status-badge stat-red" title="Pendiente">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endif
</div>
@endsection
