@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="title">Estudiantes del Profesor: {{ $professor->name }}</h1>

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

    <div class="form-group">
        <form action="{{ route('professors.assign-student', $professor) }}" method="POST" class="form-inline">
            @csrf
            <select name="student_id" class="form-control">
                <option value="">Seleccionar Estudiante</option>
                @foreach($availableStudents as $student)
                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">
                Asignar Estudiante
            </button>
        </form>
    </div>

    <div class="grid">
        @forelse($students as $student)
            <div class="card">
                <h2 class="card-title">{{ $student->name }}</h2>
                <p class="card-text">{{ $student->email }}</p>
                <div class="card-actions">
                    <a href="{{ route('students.subjects', $student) }}" class="btn btn-link">
                        Ver Materias
                    </a>
                    <form action="{{ route('professors.remove-student', ['professor' => $professor->id, 'student' => $student->id]) }}" method="POST" class="inline-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas remover este estudiante?')">
                            Remover
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <p>No hay estudiantes asignados.</p>
            </div>
        @endforelse
    </div>

    <div class="actions">
        <a href="{{ route('professors.subjects', $professor) }}" class="btn btn-primary">
            Ver Materias Asignadas
        </a>
    </div>
</div>
@endsection 