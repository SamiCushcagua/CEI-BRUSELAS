@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">

<div class="grades-container">
    <div class="grades-header">
        <div>
            <h1 class="grades-title">Materias del Estudiante: {{ $student->name }}</h1>
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

    <!-- Tabla de materias -->
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h3 class="grades-table-title">Lista de Materias Inscritas</h3>
            <div class="grades-table-actions">
                <span class="subject-info">{{ $subjects->count() }} materia(s) inscrita(s)</span>
            </div>
        </div>
        
        <div class="grades-table-wrapper">
            <table class="grades-table">
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Descripción</th>
                        <th>Nivel</th>
                        <th>Profesores</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                        <tr>
                            <td>
                                <div class="student-info">
                                    <div class="student-avatar">
                                        {{ substr($subject->name, 0, 1) }}
                                    </div>
                                    <div class="student-details">
                                        <h4>{{ $subject->name }}</h4>
                                        <p>ID: {{ $subject->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="subject-description">{{ $subject->description }}</span>
                            </td>
                            <td>
                                <span class="subject-badge">{{ $subject->Nivel }}</span>
                            </td>
                            <td>
                                <span class="professor-count">
                                    {{ $subject->professors->count() }} profesor(es)
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-approved">Inscrito</span>
                            </td>
                            <td>
                                <div class="student-actions">
                                    @if($subject->imagen)
                                        <a href="{{ asset('storage/' . $subject->imagen) }}" target="_blank" class="btn btn-secondary btn-small">
                                            🖼️ Ver Imagen
                                        </a>
                                    @endif
                                    @if($subject->Archivo)
                                        <a href="{{ asset('storage/' . $subject->Archivo) }}" target="_blank" class="btn btn-secondary btn-small">
                                            📄 Ver Documento
                                        </a>
                                    @endif
                                    <form action="{{ route('subjects.remove-student', ['subject' => $subject->id, 'student' => $student->id]) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('¿Estás seguro de que deseas remover esta materia?')">
                                            🗑️ Remover
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="empty-state">
                                    <div class="empty-icon">📚</div>
                                    <h3 class="empty-title">No hay materias inscritas</h3>
                                    <p class="empty-description">Este estudiante no tiene materias inscritas actualmente.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <td>
    
<!--   <div class="text-center mt-20">
        <a href="{{ route('students.professors', $student) }}" class="btn btn-primary">
            👨‍🏫 Ver Profesores Asignados
        </a>
    </div>-->
</div>
@endsection