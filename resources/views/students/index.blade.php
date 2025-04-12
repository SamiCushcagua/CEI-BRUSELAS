@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="title">Lista de Estudiantes</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid">
        @forelse($students as $student)
            <div class="card">
                <h2 class="card-title">{{ $student->name }}</h2>
                <p class="card-text">{{ $student->email }}</p>
                <div class="card-actions">
                    <a href="{{ route('students.subjects', $student) }}" class="btn btn-primary">
                        Ver Materias
                    </a>
                    <a href="{{ route('students.professors', $student) }}" class="btn btn-primary">
                        Ver Profesores
                    </a>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <p>No hay estudiantes registrados.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection 