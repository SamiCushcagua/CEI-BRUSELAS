@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="title">Lista de Profesores</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid">
        @forelse($professors as $professor)
            <div class="card">
                <h2 class="card-title">{{ $professor->name }}</h2>
                <p class="card-text">{{ $professor->email }}</p>
                <div class="card-actions">
                    <a href="{{ route('professors.subjects', $professor) }}" class="btn btn-primary">
                        Ver Materias
                    </a>
                    <a href="{{ route('professors.students', $professor) }}" class="btn btn-primary">
                        Ver Estudiantes
                    </a>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <p>No hay profesores registrados.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection 