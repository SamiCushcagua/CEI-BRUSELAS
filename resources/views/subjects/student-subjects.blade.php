@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}?v={{ filemtime(public_path('css/styles_PROFESOR.css')) }}">

<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>
    <div class="grades-header">
        <div>
            <h1 class="grades-title">Mis cursos: {{ $student->name }}</h1>
            @if($currentPeriod)
                <p class="grades-subtitle">Periodo vigente: {{ $currentPeriod->name }}</p>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <h2 class="student-section-title">Mi curso actual</h2>
    <div class="products-grid">
        @forelse($currentSubjects as $subject)
            <div class="product-card product-card--current">
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
                            <i class="fas fa-star"></i>
                            <span class="status-badge status-approved">Curso vigente</span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="{{ route('course-plans.show', $subject) }}" class="btn btn-primary btn-small">
                            📋 Ver plan de curso
                        </a>
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
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-icon">📚</div>
                <h3 class="empty-title">Sin curso actual</h3>
                <p class="empty-description">
                    @if($currentPeriod)
                        No tienes materia asignada en el periodo vigente ({{ $currentPeriod->name }}).
                    @else
                        No hay un periodo activo configurado.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    <h2 class="student-section-title student-section-title--history">Historial de cursos pasados</h2>
    <div class="history-table-scroll">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Materia</th>
                    <th>Nivel</th>
                    <th>Periodo</th>
                    <th>Año / Trim.</th>
                    <th>Aprobó</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($historyRows as $row)
                    <tr>
                        <td><strong>{{ $row->subject_name }}</strong></td>
                        <td>{{ $row->subject_nivel ?: '—' }}</td>
                        <td>{{ $row->period_name }}</td>
                        <td>{{ $row->period_year }} · T{{ $row->period_trimester }}</td>
                        <td>{{ $row->paso }}</td>
                        <td>
                            <a href="{{ route('students.subject-history', ['student' => $student, 'subject' => $row->subject_id, 'period_id' => $row->period_id]) }}"
                               class="btn btn-primary btn-small">
                                Ver detalle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6b7280; padding: 1.5rem;">
                            Aún no hay cursos de periodos anteriores en tu historial.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.student-section-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e3a5f;
    margin: 1.5rem 0 0.75rem;
}
.student-section-title--history {
    margin-top: 2.5rem;
    color: #374151;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
    margin-top: 0.5rem;
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

.product-card--current {
    border: 3px solid #d4a017;
    box-shadow: 0 0 0 4px rgba(212, 160, 23, 0.25), 0 8px 24px rgba(212, 160, 23, 0.2);
    border-radius: 14px;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.product-card--current:hover {
    box-shadow: 0 0 0 4px rgba(212, 160, 23, 0.35), 0 10px 28px rgba(212, 160, 23, 0.28);
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
    margin: 0 0 0.5rem;
    color: #2c3e50;
    font-size: 1.4rem;
    font-weight: 600;
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
    color: #d4a017;
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

.history-table-scroll {
    overflow-x: auto;
    max-height: 420px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.history-table th,
.history-table td {
    border-bottom: 1px solid #e5e7eb;
    padding: 0.75rem 0.85rem;
    text-align: left;
    vertical-align: middle;
}

.history-table thead th {
    position: sticky;
    top: 0;
    background: #f3f4f6;
    z-index: 1;
    font-weight: 600;
    color: #1f2937;
}

.history-table tbody tr:hover {
    background: #f9fafb;
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
