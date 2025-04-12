@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="title">Materias del Estudiante: {{ $student->name }}</h1>

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

    <div class="grid">
        @forelse($subjects as $subject)
            <div class="card">
                <h2 class="card-title">{{ $subject->name }}</h2>
                <p class="card-text">{{ $subject->description }}</p>
                <div class="card-actions">
                    <span class="badge">
                        {{ $subject->Nivel }}
                    </span>
                    <form action="{{ route('subjects.remove-student', ['subject' => $subject->id, 'student' => $student->id]) }}" method="POST" class="inline-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas remover esta materia?')">
                            Remover
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <p>No hay materias inscritas.</p>
            </div>
        @endforelse
    </div>

    <div class="actions">
        <a href="{{ route('students.professors', $student) }}" class="btn btn-primary">
            Ver Profesores Asignados
        </a>
    </div>
</div>
@endsection 