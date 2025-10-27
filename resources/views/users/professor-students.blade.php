@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">

<div class="grades-container">
    <div class="grades-header">
        <div>
            <h1 class="grades-title">Estudiantes del Profesor: {{ $professor->name }}</h1>
        </div>
        <div class="grades-info">
            <div><span>Total Estudiantes:</span> {{ $students->count() }}</div>
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

    <!-- Formulario para asignar estudiante -->
    <div class="form-container">
        <div class="form-header">
            <h2 class="form-title">Asignar Nuevo Estudiante</h2>
        </div>
        <div class="form-content">
            <form action="{{ route('professors.assign-student', $professor) }}" method="POST" class="form-grid">
                @csrf
                <div class="form-group">
                    <label class="form-label">Seleccionar Estudiante</label>
                    <select name="student_id" class="form-select" required>
                        <option value="">Seleccionar Estudiante...</option>
                        @foreach($availableStudents as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} - {{ $student->email }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary">
                        👥 Asignar Estudiante
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de estudiantes -->
    <div class="grades-table-container">
        <div class="grades-table-header">
            <h3 class="grades-table-title">Lista de Estudiantes Asignados</h3>
            <div class="grades-table-actions">
                <span class="subject-info">{{ $students->count() }} estudiante(s) asignado(s)</span>
            </div>
        </div>
        
        <div class="grades-table-wrapper">
            <table class="grades-table">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Email</th>
                        <th>Materias</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>
                                <div class="student-info">
                                    <div class="student-avatar">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    <div class="student-details">
                                        <h4>{{ $student->name }}</h4>
                                        <p>ID: {{ $student->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="student-email">{{ $student->email }}</span>
                            </td>
                            <td>
                                <span class="subject-count">
                                    {{ $student->subjectsAsStudent->count() }} materia(s)
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-approved">Activo</span>
                            </td>
                            <td>
                                <div class="student-actions">
                                    <a href="{{ route('students.subjects', $student) }}" class="btn btn-secondary btn-small">
                                        📚 Ver Materias
                                    </a>
                                    <form action="{{ route('professors.remove-student', ['professor' => $professor->id, 'student' => $student->id]) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('¿Estás seguro de que deseas remover este estudiante?')">
                                            🗑️ Remover
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                <div class="empty-state">
                                    <div class="empty-icon">👥</div>
                                    <h3 class="empty-title">No hay estudiantes asignados</h3>
                                    <p class="empty-description">Este profesor no tiene estudiantes asignados actualmente.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-center mt-20">
        <a href="{{ route('professors.subjects', $professor) }}" class="btn btn-primary">
            📚 Ver Materias Asignadas
        </a>
    </div>
</div>
@endsection