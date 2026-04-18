@extends('layouts.app')

@section('content')
<div class="page-container" style="padding: 20px;">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>
    <div style="display:flex; align-items:center; justify-content:space-between; gap: 16px; margin-bottom: 16px;">
        <div>
            <h1 style="margin:0;">Periodos escolares</h1>
            <p style="margin:6px 0 0 0; color:#6b7280;">
                Crea los trimestres, marca el periodo vigente y bloquea los que ya terminaron.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 16px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="periods-grid-top">
        <div class="periods-card">
            <h2 style="margin-top:0;">Crear periodo</h2>
            <form action="{{ route('periods.store') }}" method="POST" class="periods-form">
                @csrf
                <div class="periods-form-row">
                    <div class="periods-form-group">
                        <label>Nombre</label>
                        <input type="text" name="name" required maxlength="100" placeholder="Ej. Trimestre 1 - 2026">
                    </div>
                    <div class="periods-form-group">
                        <label>Año</label>
                        <input type="number" name="year" required min="2020" max="2030" value="{{ date('Y') }}">
                    </div>
                </div>
                <div class="periods-form-row">
                    <div class="periods-form-group">
                        <label>Trimestre (1, 2 o 3)</label>
                        <input type="number" name="trimester" required min="1" max="3">
                    </div>
                </div>
                <div class="periods-form-row">
                    <div class="periods-form-group">
                        <label>Inicio (opcional)</label>
                        <input type="date" name="start_date">
                    </div>
                    <div class="periods-form-group">
                        <label>Fin (opcional)</label>
                        <input type="date" name="end_date">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Crear periodo</button>
            </form>
        </div>

        <div class="periods-hint-card">
            <h2 style="margin-top:0;">Cómo funciona</h2>
            <p style="margin: 8px 0; color:#374151;">
                El periodo <b>vigente</b> es el que ven profesores y alumnos.
            </p>
            <p style="margin: 8px 0; color:#374151;">
                Cuando terminas un trimestre, bloquea el periodo y marca el siguiente como vigente.
            </p>
            <div class="periods-badges-row">
                <span class="badge badge-active">Vigente</span>
                <span class="badge badge-locked">Bloqueado</span>
            </div>
        </div>
    </div>

    <div style="margin: 22px 0 10px 0;">
        <h2 style="margin:0;">Listado de periodos</h2>
        <p style="margin:6px 0 0 0; color:#6b7280;">Gestiona el estado de cada trimestre.</p>
    </div>

    <div class="periods-grid">
        @foreach($periods as $period)
            <div class="periods-item-card">
                <div class="periods-item-top">
                    <div class="periods-item-title">
                        <div class="periods-item-name">{{ $period->name }}</div>
                        <div class="periods-item-sub">
                            Año {{ $period->year }} · Trimestre {{ $period->trimester }}
                        </div>
                    </div>
                    <div class="periods-badges-row">
                        @if($period->is_active)
                            <span class="badge badge-active">Vigente</span>
                        @else
                            <span class="badge badge-neutral">No vigente</span>
                        @endif

                        @if($period->is_locked)
                            <span class="badge badge-locked">Bloqueado</span>
                        @else
                            <span class="badge badge-neutral">Editable</span>
                        @endif
                    </div>
                </div>

                <div class="periods-item-body">
                    <div class="periods-item-line">
                        <span class="label">Inicio</span>
                        <span class="value">{{ $period->start_date?->format('d/m/Y') ?? '-' }}</span>
                    </div>
                    <div class="periods-item-line">
                        <span class="label">Fin</span>
                        <span class="value">{{ $period->end_date?->format('d/m/Y') ?? '-' }}</span>
                    </div>
                </div>

                <div class="periods-item-actions">
                    @if(!$period->is_active)
                        <form action="{{ route('periods.set-active', $period) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-small">Marcar vigente</button>
                        </form>
                    @endif

                    @if(!$period->is_locked)
                        <form action="{{ route('periods.lock', $period) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-small">Bloquear</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
.periods-grid-top {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 16px;
    align-items: start;
}

.periods-card,
.periods-hint-card,
.periods-item-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.periods-card {
    padding: 16px;
}

.periods-hint-card {
    padding: 16px;
}

.periods-form {
    margin-top: 10px;
}

.periods-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
}

.periods-form-group label {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 6px;
    font-weight: 600;
}

.periods-form-group input {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    outline: none;
}

.periods-form-group input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.periods-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 16px;
}

.periods-item-card {
    padding: 16px;
}

.periods-item-top {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}

.periods-item-name {
    font-weight: 800;
    font-size: 16px;
    color: #111827;
}

.periods-item-sub {
    margin-top: 4px;
    font-size: 13px;
    color: #6b7280;
}

.periods-item-body {
    margin-bottom: 12px;
}

.periods-item-line {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 6px 0;
    border-bottom: 1px dashed #f3f4f6;
}

.periods-item-line:last-child {
    border-bottom: none;
}

.periods-item-line .label {
    color: #6b7280;
    font-size: 12px;
    font-weight: 700;
}

.periods-item-line .value {
    color: #111827;
    font-size: 13px;
    font-weight: 600;
}

.periods-item-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.periods-badges-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}

.badge {
    font-size: 12px;
    font-weight: 800;
    border-radius: 9999px;
    padding: 6px 10px;
    border: 1px solid transparent;
}

.badge-active {
    background: #dcfce7;
    color: #166534;
    border-color: #86efac;
}

.badge-locked {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fca5a5;
}

.badge-neutral {
    background: #f3f4f6;
    color: #111827;
    border-color: #e5e7eb;
}

.btn-small {
    font-size: 12px;
    padding: 8px 10px;
}

@media (max-width: 768px) {
    .periods-grid-top {
        grid-template-columns: 1fr;
    }
    .periods-form-row {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection