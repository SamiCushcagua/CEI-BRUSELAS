@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="title">Alumnos sin asignar (periodo actual)</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
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
        <div class="grad-overview-scroll">
            <table class="grad-overview-table">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Materia semestre anterior</th>
                        <th>Paso (sí / no)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row->student->name }}</td>
                            <td>{{ $row->materia }}</td>
                            <td>{{ $row->paso }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #6b7280;">
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
    @endif
</div>
@endsection
