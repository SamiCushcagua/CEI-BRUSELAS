@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">

<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>

    <div class="grades-header">
        <div>
            <h1 class="grades-title">Mis calificaciones</h1>
            <p class="grades-subtitle">Consulta tus puntajes por materia y periodo (solo lectura)</p>
            @if(isset($period))
            @php $viewingActive = $activePeriod && (string) $period->id === (string) $activePeriod->id; @endphp
            <p class="grades-subtitle" style="margin-top: 0.5rem;">
                @if($activePeriod)
                    <strong>Periodo vigente (actual):</strong>
                    {{ $activePeriod->name }} — {{ $activePeriod->year }}, trimestre {{ $activePeriod->trimester }}
                    @if($viewingActive)
                        <span class="status-badge status-approved" style="margin-left: 0.35rem;">Estás viendo este periodo</span>
                    @endif
                @else
                    <span style="opacity: 0.9;">No hay ningún periodo marcado como vigente por administración.</span>
                @endif
            </p>
            @endif
        </div>
        @isset($period)
        <div class="grades-info">
            <div><span>Consultando:</span> {{ $period->year }} — Trimestre {{ $period->trimester }}</div>
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

    <div class="form-container">
        <div class="form-header">
            <h2 class="form-title">Elegir periodo</h2>
        </div>
        <div class="form-content">
            <form method="GET" action="{{ route('student.grades') }}" class="form-grid">
                <div class="form-group">
                    <label class="form-label">Periodo</label>
                    <select name="period_id" class="form-select" required onchange="this.form.submit()">
                        @foreach($periods as $p)
                        @php
                            $isVigente = $activePeriod && (string) $p->id === (string) $activePeriod->id;
                        @endphp
                        <option value="{{ $p->id }}" {{ (string) $p->id === (string) $period->id ? 'selected' : '' }}>
                            {{ $p->name }} — {{ $p->year }}, trim. {{ $p->trimester }}{{ $isVigente ? ' (vigente / actual)' : '' }}
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

    @php
        $isSelectedActive = $activePeriod && (string) $period->id === (string) $activePeriod->id;
    @endphp

    <div class="form-container" style="margin-top: 1rem;">
        <div class="form-header">
            <h2 class="form-title">Periodo que estás consultando</h2>
        </div>
        <div class="form-content" style="line-height: 1.6;">
            <p style="margin: 0 0 0.5rem 0;"><strong>{{ $period->name }}</strong></p>
            <p style="margin: 0 0 0.5rem 0;">
                Año académico <strong>{{ $period->year }}</strong> · Trimestre <strong>{{ $period->trimester }}</strong>
            </p>
            @if($period->start_date && $period->end_date)
            <p style="margin: 0 0 0.75rem 0; opacity: 0.95;">
                📅 Del {{ \Carbon\Carbon::parse($period->start_date)->format('d/m/Y') }}
                al {{ \Carbon\Carbon::parse($period->end_date)->format('d/m/Y') }}
            </p>
            @endif
            @if($isSelectedActive)
            <p style="margin: 0;">
                <span class="status-badge status-approved">Este es el periodo vigente (el de ahora)</span>
            </p>
            @elseif($activePeriod)
            <p style="margin: 0.5rem 0 0 0;">
                <span class="status-badge stat-yellow" title="Estás viendo notas de otro trimestre">Periodo histórico</span>
                <span style="margin-left: 0.5rem;">El periodo actual es: <strong>{{ $activePeriod->name }}</strong>
                ({{ $activePeriod->year }}, trim. {{ $activePeriod->trimester }})</span>
            </p>
            @else
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">No hay periodo vigente definido; comparar con lo que indique tu centro.</p>
            @endif
        </div>
    </div>

    @if($rows->isNotEmpty())
    <div class="form-container" style="margin-top: 1rem;">
        <div class="form-header">
            <h2 class="form-title">Tus materias (clases) en este periodo</h2>
        </div>
        <div class="form-content">
            <p style="margin: 0 0 0.75rem 0; opacity: 0.95;">Estas son las asignaturas en las que estabas inscrito durante este periodo:</p>
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($rows as $row)
                <li style="margin-bottom: 0.35rem;">
                    <strong>{{ $row->subject->name }}</strong>
                    @if(!empty($row->subject->Nivel))
                    <span style="opacity: 0.9;"> — {{ $row->subject->Nivel }}</span>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    @if($rows->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">📚</div>
        <h3 class="empty-title">Sin materias en este periodo</h3>
        <p class="empty-description">No apareces inscrito en ninguna materia para el periodo seleccionado.</p>
    </div>
    @else
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h3 class="grades-table-title">Calificaciones del trimestre</h3>
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
                        <th>Texto</th>
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
