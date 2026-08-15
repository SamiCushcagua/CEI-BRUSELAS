@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="title">Alumnos sin asignar (periodo actual)</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>
    @if($currentPeriod)
        <p class="card-text" style="margin-bottom: 1rem;">
            <strong>Periodo actual:</strong> {{ $currentPeriod->name }}
            @if($previousPeriod)
                <span style="margin-left: 1rem;"><strong>Periodo anterior:</strong> {{ $previousPeriod->name }}</span>
            @else
                <span style="margin-left: 1rem; color: #6b7280;">No hay periodo anterior registrado (primer periodo académico).</span>
            @endif
        </p>
        <p class="card-text" style="margin-bottom: 1rem; color: #4b5563; font-size: 0.95rem;">
            Haz clic en el <strong>nombre</strong> del alumno para ver su historial de aprobación.
            En la columna <strong>Asignar a materia</strong> elige el curso del periodo actual.
            Los ya asignados quedan listados abajo; puedes cambiar la materia o volver a “No asignado”.
        </p>

        @if($filterSubjects->isNotEmpty())
            <form method="get" action="{{ route('students.index') }}" class="students-filter-form" style="margin-bottom: 1rem; display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                <label for="subject_id" style="font-weight: 500;">Filtrar por materia (periodo anterior):</label>
                <select name="subject_id" id="subject_id" onchange="this.form.submit()" style="min-width: 220px; padding: 0.35rem 0.5rem;">
                    <option value="">Todas</option>
                    @foreach($filterSubjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string) $filterSubjectId === (string) $subject->id)>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
    @else
        <div class="alert alert-success" style="background: #fef3c7; border-color: #fcd34d; color: #92400e;">
            No hay ningún periodo marcado como <strong>activo</strong>. Activa un periodo en la administración para ver la lista de pendientes.
        </div>
    @endif

    @if($currentPeriod)
        <h2 class="pending-section-title">Pendientes de asignar ({{ $rows->count() }})</h2>
        <div class="grad-overview-scroll">
            <table class="grad-overview-table pending-assign-table">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Materia semestre anterior</th>
                        <th>Paso (sí / no)</th>
                        <th>Asignar a materia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @include('students._assignment_rows', [
                            'row' => $row,
                            'historyPrefix' => 'pending',
                            'assignableSubjects' => $assignableSubjects,
                            'passByStudent' => $passByStudent,
                            'filterSubjectId' => $filterSubjectId,
                        ])
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #6b7280;">
                                @if($filterSubjectId)
                                    Ningún alumno pendiente coincide con la materia seleccionada.
                                @else
                                    No quedan alumnos por asignar a una materia en este periodo.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h2 class="pending-section-title pending-section-title--assigned">
            Ya asignados este periodo ({{ $assignedRows->count() }})
        </h2>
        <div class="grad-overview-scroll">
            <table class="grad-overview-table pending-assign-table">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Materia semestre anterior</th>
                        <th>Paso (sí / no)</th>
                        <th>Asignar a materia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignedRows as $row)
                        @include('students._assignment_rows', [
                            'row' => $row,
                            'historyPrefix' => 'assigned',
                            'assignableSubjects' => $assignableSubjects,
                            'passByStudent' => $passByStudent,
                            'filterSubjectId' => $filterSubjectId,
                        ])
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #6b7280;">
                                Todavía no hay alumnos asignados en este periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>

<style>
.pending-section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e3a5f;
    margin: 1.25rem 0 0.6rem;
}
.pending-section-title--assigned {
    margin-top: 2rem;
    color: #166534;
}
.pending-student-toggle {
    background: none;
    border: none;
    padding: 0;
    margin: 0;
    color: #2563eb;
    font-weight: 600;
    font-size: inherit;
    cursor: pointer;
    text-align: left;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.pending-student-toggle:hover {
    text-decoration: underline;
}
.pending-toggle-icon {
    display: inline-block;
    width: 0.9em;
    color: #6b7280;
    transition: transform 0.15s ease;
}
.pending-student-toggle[aria-expanded="true"] .pending-toggle-icon {
    transform: rotate(90deg);
}
.pending-student-email {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.15rem;
}
.pending-assign-select {
    min-width: 200px;
    max-width: 100%;
    padding: 0.4rem 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
}
.pending-history-row td {
    background: #f8fafc;
}
.pending-history-panel {
    padding: 0.35rem 0.15rem 0.5rem;
}
.pending-history-title {
    font-weight: 600;
    font-size: 0.8rem;
    color: #1f2937;
    margin-bottom: 0.4rem;
}
.pending-history-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
}
.pending-history-chip {
    display: inline-flex;
    flex-direction: row;
    align-items: center;
    gap: 0.3rem;
    min-width: 0;
    max-width: none;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    border: 1px solid #e5e7eb;
    background: #fff;
    font-size: 0.7rem;
    line-height: 1.2;
}
.pending-history-chip-name {
    font-weight: 600;
    color: #111827;
    line-height: 1.2;
    white-space: nowrap;
}
.pending-history-chip-status {
    color: #6b7280;
    white-space: nowrap;
}
.pending-history-chip.is-pass {
    border-color: #86efac;
    background: #f0fdf4;
}
.pending-history-chip.is-pass .pending-history-chip-status {
    color: #15803d;
    font-weight: 600;
}
.pending-history-chip.is-fail {
    border-color: #fca5a5;
    background: #fef2f2;
}
.pending-history-chip.is-fail .pending-history-chip-status {
    color: #b91c1c;
    font-weight: 600;
}
.pending-history-chip.is-empty {
    opacity: 0.75;
}
.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-toggle-history]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-toggle-history');
            var detail = document.getElementById(id);
            if (!detail) return;

            var open = detail.hasAttribute('hidden');
            detail.toggleAttribute('hidden', !open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });
});
</script>
@endsection
