@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">

<div class="grades-container">
    <div class="grades-header">
        <div>
            <h1 class="grades-title">Inscripciones: aprobación y diplomas</h1>
            <p class="grades-subtitle">Alumnos inscritos por materia y periodo (trimestre). Marca aprobación del trimestre y entrega de diploma; pulsa <strong>Guardar</strong> por fila.</p>
        </div>
        <a href="{{ route('welcome') }}" class="btn btn-secondary">← Inicio</a>
    </div>

    @if($periods->isEmpty())
        <div class="empty-state">
            <p>No hay periodos definidos. Crea uno en <a href="{{ route('periods.index') }}">Periodos</a>.</p>
        </div>
    @else
        @if(session('success'))
            <div class="settings-container" style="margin-bottom: 1rem; border-left: 4px solid #10b981;">
                <div class="settings-content"><p style="margin:0;">{{ session('success') }}</p></div>
            </div>
        @endif
        @if(session('error'))
            <div class="settings-container" style="margin-bottom: 1rem; border-left: 4px solid #ef4444;">
                <div class="settings-content"><p style="margin:0;">{{ session('error') }}</p></div>
            </div>
        @endif

        <div class="settings-container" style="margin-bottom: 1.5rem;">
            <div class="settings-header">
                <h2 class="settings-title">Filtros</h2>
            </div>
            <div class="settings-content">
                <form method="get" action="{{ route('admin.subject-enrollment-outcomes') }}" class="form-grid" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
                    <div class="form-group">
                        <label class="form-label" for="period_id">Periodo (año · trimestre)</label>
                        <select name="period_id" id="period_id" class="form-select" required onchange="this.form.submit()">
                            @foreach($periods as $p)
                                <option value="{{ $p->id }}" @selected($period && $period->id === $p->id)>
                                    {{ $p->name }} — {{ $p->year }} · T{{ $p->trimester }}
                                    @if($p->is_active) (activo) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="subject_id">Materia</label>
                        <select name="subject_id" id="subject_id" class="form-select" required onchange="this.form.submit()">
                            @forelse($subjects as $s)
                                <option value="{{ $s->id }}" @selected($subject && $subject->id === $s->id)>{{ $s->name }}</option>
                            @empty
                                <option value="">Sin inscripciones en este periodo</option>
                            @endforelse
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </form>
            </div>
        </div>

        @if($subject && $rows->isNotEmpty())
            <div class="grades-table-container">
                <div class="grades-table-header">
                    <h2 class="grades-table-title">{{ $subject->name }}</h2>
                    <span class="subject-info">{{ $period->year }} · Trimestre {{ $period->trimester }} — {{ $rows->count() }} estudiante(s)</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="grades-table">
                        <thead>
                            <tr>
                                <th class="sticky-col">Estudiante</th>
                                <th>Email</th>
                                <th>Aprobación y diploma</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                @php $sid = $row['student']->id; @endphp
                                <tr>
                                    <td class="sticky-col">
                                        <div class="student-info">
                                            <div class="student-avatar">{{ substr($row['student']->name, 0, 1) }}</div>
                                            <div class="student-details">
                                                <h4>{{ $row['student']->name }}</h4>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $row['student']->email }}</td>
                                    <td>
                                        <form method="post" action="{{ route('admin.subject-enrollment-outcomes.update') }}" class="enrollment-outcome-form" style="display: flex; flex-wrap: wrap; align-items: center; gap: 1rem;">
                                            @csrf
                                            <input type="hidden" name="period_id" value="{{ $period->id }}">
                                            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                                            <input type="hidden" name="student_id" value="{{ $sid }}">
                                            <input type="hidden" name="passed" value="0">
                                            <label style="display: inline-flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                                                <input type="checkbox" name="passed" value="1" @checked($row['passed'])>
                                                <span>Aprobó (trimestre)</span>
                                            </label>
                                            <input type="hidden" name="diploma_delivered" value="0">
                                            <label style="display: inline-flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                                                <input type="checkbox" name="diploma_delivered" value="1" @checked($row['diploma_delivered'])>
                                                <span>Diploma entregado</span>
                                            </label>
                                            <button type="submit" class="btn btn-primary btn-small">Guardar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($subject)
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <h3 class="empty-title">No hay estudiantes inscritos</h3>
                <p class="empty-description">No hay alumnos en esta materia para el periodo seleccionado.</p>
            </div>
        @elseif($subjects->isEmpty())
            <div class="empty-state">
                <p>No hay materias con inscripciones en este periodo.</p>
            </div>
        @endif
    @endif
</div>
@endsection
