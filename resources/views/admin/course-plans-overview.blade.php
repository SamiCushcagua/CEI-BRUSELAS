@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}?v={{ filemtime(public_path('css/styles_PROFESOR.css')) }}">

<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>

    <div class="grades-header">
        <div>
            <h1 class="grades-title">Admin — Planes de curso</h1>
            <p class="grades-subtitle">Consulta el plan de cada materia por periodo (incluye trimestres pasados).</p>
        </div>
        <div class="grades-info">
            <div><span>Periodo:</span> {{ $period->name }}</div>
        </div>
    </div>

    <div class="form-container">
        <div class="form-header">
            <h2 class="form-title">Seleccionar contexto</h2>
        </div>
        <div class="form-content">
            <form method="GET" action="{{ route('admin.course-plans.index') }}" class="form-grid">
                <div class="form-group">
                    <label class="form-label">Periodo</label>
                    <select name="period_id" class="form-select" required>
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" @selected($p->id === $period->id)>
                                {{ $p->name }}
                                @if($p->is_active) (vigente) @endif
                                @if($p->is_locked) (bloqueado) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Materia</label>
                    <select name="subject_id" class="form-select" {{ $subjects->isEmpty() ? 'disabled' : '' }}>
                        @if($subjects->isEmpty())
                            <option value="">Sin materias con maestros asignados</option>
                        @else
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" @selected($selectedSubject && $selectedSubject->id === $s->id)>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group form-actions">
                    <button type="submit" class="btn btn-success w-full">Aplicar</button>
                </div>
            </form>
        </div>
    </div>

    @if($summaries->isNotEmpty())
        <section class="admin-plans-summary">
            <h2 class="admin-plans-summary-title">Resumen del periodo</h2>
            <div class="admin-plans-summary-grid">
                @foreach($summaries as $row)
                    @php
                        $statusClass = match($row['status']) {
                            'published' => 'status-published',
                            'draft' => 'status-draft',
                            default => 'status-empty',
                        };
                        $statusLabel = match($row['status']) {
                            'published' => 'Publicado',
                            'draft' => 'Borrador',
                            default => 'Aún no rellenado',
                        };
                    @endphp
                    <a href="{{ route('admin.course-plans.index', ['period_id' => $period->id, 'subject_id' => $row['subject']->id]) }}"
                       class="admin-plans-summary-card {{ $selectedSubject && $selectedSubject->id === $row['subject']->id ? 'is-selected' : '' }}">
                        <div class="admin-plans-summary-card-top">
                            <strong>{{ $row['subject']->name }}</strong>
                            <span class="admin-plans-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                        <p class="admin-plans-teachers">Maestro(s): {{ $row['professors'] ?: 'Sin asignar' }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @else
        <div class="empty-state" style="margin-top: 1.5rem;">
            <div class="empty-icon">📋</div>
            <h3 class="empty-title">No hay materias con maestros asignados</h3>
            <p class="empty-description">En este periodo no hay clases con profesores asignados en el sistema.</p>
        </div>
    @endif

    @if($selectedSubject)
        <section class="admin-plans-detail" style="margin-top: 1.5rem;">
            <div class="admin-plans-detail-header">
                <div>
                    <h2>{{ $selectedSubject->name }}</h2>
                    <p class="grades-subtitle" style="margin: 0.25rem 0 0;">
                        Maestro(s): {{ $auto['teachers'] ?: 'Sin asignar' }}
                    </p>
                </div>
                <div class="admin-plans-detail-actions">
                    <a href="{{ route('course-plans.edit', ['subject' => $selectedSubject, 'period_id' => $period->id]) }}"
                       class="btn btn-primary btn-small">
                        Editar plan
                    </a>
                </div>
            </div>

            @if($selectedStatus === 'empty')
                <div class="admin-plans-empty-notice">
                    <strong>Aún no rellenado</strong>
                    <p>Los maestros asignados aún no han completado el plan de curso para esta materia en este periodo.</p>
                </div>
            @else
                @php $plan = $selectedPlan; @endphp
                @include('course-plans._form', ['editable' => false])
            @endif
        </section>
    @endif
</div>

<style>
    .admin-plans-summary-title {
        margin: 0 0 0.75rem;
        font-size: 1.1rem;
    }

    .admin-plans-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 0.75rem;
    }

    .admin-plans-summary-card {
        display: block;
        text-decoration: none;
        color: inherit;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        transition: box-shadow 0.2s, border-color 0.2s;
    }

    .admin-plans-summary-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .admin-plans-summary-card.is-selected {
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
    }

    .admin-plans-summary-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }

    .admin-plans-teachers {
        margin: 0;
        font-size: 0.85rem;
        color: #6b7280;
    }

    .admin-plans-badge {
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        white-space: nowrap;
    }

    .admin-plans-badge.status-empty {
        background: #fde8e8;
        color: #b42318;
        border: 1px solid #fecdca;
    }

    .admin-plans-badge.status-draft {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffe69c;
    }

    .admin-plans-badge.status-published {
        background: #d4edda;
        color: #155724;
        border: 1px solid #b7e4c7;
    }

    .admin-plans-detail {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.25rem;
    }

    .admin-plans-detail-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .admin-plans-detail-header h2 {
        margin: 0;
        font-size: 1.2rem;
    }

    .admin-plans-empty-notice {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 10px;
        padding: 1.25rem 1.5rem;
        color: #991b1b;
    }

    .admin-plans-empty-notice strong {
        display: block;
        font-size: 1.05rem;
        margin-bottom: 0.35rem;
    }

    .admin-plans-empty-notice p {
        margin: 0;
        color: #b42318;
        font-size: 0.92rem;
    }
</style>
@endsection
