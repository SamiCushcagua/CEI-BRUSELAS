@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}?v={{ filemtime(public_path('css/styles_PROFESOR.css')) }}">

<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>
    <div class="grades-header">
        <div>
            <h1 class="grades-title">Materias del Profesor: {{ $professor->name }}</h1>
        </div>
        <div class="grades-info">
            <div><span>Total Materias:</span> {{ $subjects->count() }}</div>
        </div>
    </div>

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

    <div class="subjects-grid">
        @forelse($subjects as $subject)
            <div class="subject-card">
                <div class="subject-card-header">
                    <h3 class="subject-name">{{ $subject->name }}</h3>
                    <span class="subject-badge">{{ $subject->Nivel }}</span>
                </div>
                
                <div class="subject-description">{{ $subject->description }}</div>
                
                @if($subject->imagen)
                    <div class="subject-image-container">
                        <img src="{{ asset('storage/' . $subject->imagen) }}" alt="{{ $subject->name }}" class="subject-image">
                    </div>
                @else
                    <div class="subject-placeholder">
                        <span class="subject-placeholder-icon">📚</span>
                    </div>
                @endif
                
                @if($subject->Archivo)
                    <div class="subject-document">
                        <a href="{{ asset('storage/' . $subject->Archivo) }}" target="_blank" class="btn btn-secondary btn-small">
                            📄 Ver Documento
                        </a>
                    </div>
                @endif
                
        <!--       <div class="subject-actions">
                    <form action="{{ route('subjects.remove-professor', ['subject' => $subject->id, 'professor' => $professor->id]) }}" method="POST" class="btn-flex">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-small w-full" onclick="return confirm('¿Estás seguro de que deseas remover esta materia?')">
                            🗑️ Remover Materia
                        </button>
                    </form>
                </div>-->
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3 class="empty-title">No hay materias asignadas</h3>
                <p class="empty-description">Este profesor no tiene materias asignadas actualmente.</p>
            </div>
        @endforelse
    </div>

    <div class="text-center mt-20">
        <a href="{{ route('professors.students', $professor) }}" class="btn btn-primary">
            👥 Ver Estudiantes Asignados
        </a>
    </div>
</div>
@endsection