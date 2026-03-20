@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">

<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>
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

    <!-- Dashboard de materias (estilo igual a dashboard_cursos) -->
    <div class="products-grid">
        @forelse($subjects as $subject)
            <div class="product-card">
                @if($subject->imagen)
                    <div class="product-image">
                        <a href="{{ asset('storage/' . $subject->imagen) }}" target="_blank">
                            <img src="{{ asset('storage/' . $subject->imagen) }}" alt="{{ $subject->name }}">
                        </a>
                    </div>
                @else
                    <div class="product-image">
                        <div class="placeholder-content">
                            <i class="fas fa-graduation-cap"></i>
                            <span>{{ $subject->name }}</span>
                        </div>
                    </div>
                @endif

                <div class="product-info">
                    <h2 class="product-title">{{ $subject->name }}</h2>
                    <p class="product-description">{{ $subject->description }}</p>

                    <div class="subject-details">
                        <div class="detail-item">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Curso {{ $subject->Nivel }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <span>{{ $subject->professors->count() }} profesor(es)</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-check-circle"></i>
                            <span class="status-badge status-approved">Inscrito</span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        @if($subject->Archivo)
                            <a href="{{ asset('storage/' . $subject->Archivo) }}" target="_blank" class="btn btn-secondary btn-small">
                                📄 Ver Documento
                            </a>
                        @endif
                        @if($subject->imagen)
                            <a href="{{ asset('storage/' . $subject->imagen) }}" target="_blank" class="btn btn-secondary btn-small">
                                🖼️ Ver Imagen
                            </a>
                        @endif
 <!-- <form action="{{ route('subjects.remove-student', ['subject' => $subject->id, 'student' => $student->id]) }}" method="POST" class="inline-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('¿Estás seguro de que deseas remover esta materia?')">
                                🗑️ Remover
                            </button>
                        </form>-->
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3 class="empty-title">No hay materias inscritas</h3>
                <p class="empty-description">Este estudiante no tiene materias inscritas actualmente.</p>
            </div>
        @endforelse
    </div>

<!--   <div class="text-center mt-20">
        <a href="{{ route('students.professors', $student) }}" class="btn btn-primary">
            👨‍🏫 Ver Profesores Asignados
        </a>
    </div>-->
</div>

<style>
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.product-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.product-image {
    height: 200px;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-image a {
    display: block;
    width: 100%;
    height: 100%;
    text-decoration: none;
}

.placeholder-content {
    text-align: center;
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.placeholder-content i {
    font-size: 2rem;
    opacity: 0.9;
}

.product-info {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-title {
    margin: 0;
    color: #2c3e50;
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.product-description {
    color: #6c757d;
    font-size: 0.95rem;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.subject-details {
    margin-bottom: 1rem;
}

.detail-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    color: #495057;
    font-size: 0.9rem;
}

.detail-item i {
    margin-right: 0.5rem;
    color: #007bff;
    width: 16px;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.action-buttons .btn {
    font-size: 0.85rem;
    padding: 0.375rem 0.5rem;
    border-radius: 6px;
    font-weight: 500;
}

@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .action-buttons {
        flex-direction: column;
    }
}
</style>
@endsection