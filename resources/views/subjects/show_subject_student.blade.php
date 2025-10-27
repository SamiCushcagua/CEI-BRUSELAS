@extends('layouts.app')

@section('content')

<div class="container">
    <div class="subject-header">
        <h1 class="page-title">{{ $subject->name }}</h1>
        <p class="subject-description">{{ $subject->description }}</p>
        <div class="subject-info">
            <span class="badge">Nivel: {{ $subject->Nivel }}</span>
            <span class="badge">Estudiantes inscritos: {{ $enrolledStudents->count() }}</span>
        </div>
    </div>

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

    <!-- Formulario para agregar estudiante -->
    <div class="add-student-section">
        <h3><i class="fas fa-user-plus"></i> Agregar Estudiante</h3>
        
        @if($availableStudents->count() > 0)
            <form action="{{ route('subjects.enroll-student', $subject) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="student_id">Seleccionar Estudiante:</label>
                    <select name="student_id" id="student_id" class="form-control" required>
                        <option value="">Seleccionar estudiante...</option>
                        @foreach($availableStudents as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Inscribir Estudiante
                </button>
            </form>
        @else
            <div class="no-students-message">
                <p><i class="fas fa-info-circle"></i> No hay estudiantes disponibles para inscribir en esta materia.</p>
            </div>
        @endif
    </div>

    <!-- Lista de estudiantes inscritos -->
    <div class="enrolled-students-section">
        <h3><i class="fas fa-users"></i> Estudiantes Inscritos ({{ $enrolledStudents->count() }})</h3>
        
        @if($enrolledStudents->count() > 0)
            <div class="students-grid">
                @foreach($enrolledStudents as $student)
                    <div class="student-card">
                        <div class="student-info">
                            <div class="student-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="student-details">
                                <h4>{{ $student->name }}</h4>
                                <p>{{ $student->email }}</p>
                                <small>Inscrito: {{ $student->pivot->created_at ? $student->pivot->created_at->format('d/m/Y') : 'Reciente' }}</small>
                            </div>
                        </div>
                        <div class="student-actions">
                            <form action="{{ route('subjects.remove-student', [$subject, $student]) }}" 
                                  method="POST" 
                                  style="display: inline-block;"
                                  onsubmit="return confirm('¿Estás seguro de que quieres eliminar a {{ $student->name }} de esta materia?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-enrolled-message">
                <p><i class="fas fa-users"></i> No hay estudiantes inscritos en esta materia.</p>
            </div>
        @endif
    </div>

    <!-- Botón de regreso -->
    <div class="back-section">
        <a href="{{ route('dashboard_cursos') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
</div>

<style>
.subject-header {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
    text-align: center;
}

.page-title {
    color: #2c3e50;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.subject-description {
    color: #6c757d;
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
}

.subject-info {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 500;
}

.add-student-section {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.add-student-section h3 {
    color: #28a745;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #28a745, #20c997);
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #218838, #1ea085);
    transform: translateY(-2px);
}

.no-students-message {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
    color: #6c757d;
}

.enrolled-students-section {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.enrolled-students-section h3 {
    color: #007bff;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.students-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.student-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
}

.student-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.student-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.student-avatar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.student-details h4 {
    margin: 0 0 0.25rem 0;
    color: #2c3e50;
    font-size: 1.1rem;
}

.student-details p {
    margin: 0 0 0.25rem 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.student-details small {
    color: #adb5bd;
    font-size: 0.8rem;
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    border: none;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #c82333, #a71e2a);
    transform: translateY(-1px);
}

.no-enrolled-message {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 8px;
    text-align: center;
    color: #6c757d;
}

.back-section {
    text-align: center;
    margin-top: 2rem;
}

.btn-secondary {
    background: #6c757d;
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
    text-decoration: none;
    color: white;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .students-grid {
        grid-template-columns: 1fr;
    }
    
    .student-card {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .student-info {
        flex-direction: column;
        text-align: center;
    }
}
</style>
@endsection
