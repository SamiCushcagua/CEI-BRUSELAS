@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="page-title mb-4">Dashboard de Cursos</h1>

    <div class="products-grid">
        @foreach($subjects as $subject)
            <div class="product-card">
                @if($subject->imagen)
                    <div class="product-image">
                        <a href="{{ route('subjects.show', $subject) }}">
                            <img src="{{ asset('storage/' . $subject->imagen) }}" 
                                 alt="{{ $subject->name }}">
                        </a>
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
                            <span>
                                @if($subject->professors->count() > 0)
                                    @foreach($subject->professors as $professor)
                                        {{ $professor->name }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @else
                                    Sin profesores asignados
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    @if($subject->Archivo)
                        <div class="material-download-section">
                            <a href="{{ asset('storage/' . $subject->Archivo) }}" 
                               class="material-download-btn" 
                               target="_blank">
                                <div class="btn-content">
                                    <div class="btn-icon">
                                        <i class="fas fa-file-download"></i>
                                    </div>
                                    <div class="btn-text">
                                        <span class="btn-title">Ver Material</span>
                                        <span class="btn-subtitle">Descargar archivo</span>
                                    </div>
                                </div>
                                <div class="btn-arrow">
                                    <i class="fas fa-external-link-alt"></i>
                                </div>
                            </a>
                        </div>
                    @endif
                    
                    <!-- Sección de asignación de profesores -->
                    <div class="professor-assignment-section">
                        <h6 class="section-title">
                            <i class="fas fa-user-plus"></i> Asignar Profesores
                        </h6>
                        <form action="{{ route('subjects.assign-professors', $subject) }}" method="POST" class="professor-form">
                            @csrf
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Primer Profesor</label>
                                    <select name="professor1" class="form-control form-control-sm" required>
                                        <option value="">Seleccionar...</option>
                                        @foreach(App\Models\User::where('is_profesor', true)->get() as $professor)
                                            <option value="{{ $professor->id }}" 
                                                    {{ $subject->professors->contains($professor->id) ? 'selected' : '' }}>
                                                {{ $professor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Segundo Profesor</label>
                                    <select name="professor2" class="form-control form-control-sm">
                                        <option value="">Seleccionar...</option>
                                        @foreach(App\Models\User::where('is_profesor', true)->get() as $professor)
                                            <option value="{{ $professor->id }}" 
                                                    {{ $subject->professors->contains($professor->id) ? 'selected' : '' }}>
                                                {{ $professor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm btn-assign">
                                <i class="fas fa-check"></i> Asignar
                            </button>
                        </form>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="action-buttons">
                        <a href="{{ route('subjects.edit', $subject) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        
                        <form action="{{ route('subjects.destroy', $subject) }}" method="POST" 
                              style="display: inline-block;" 
                              onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta materia?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
.page-title {
    color: #2c3e50;
    font-weight: 600;
    text-align: center;
    margin-bottom: 2rem;
}

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

.product-image a:hover {
    opacity: 0.9;
    transition: opacity 0.3s ease;
}

.product-info {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-title {
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

.professor-assignment-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin: 1rem 0;
    border-left: 4px solid #28a745;
    flex-shrink: 0;
}

.section-title {
    color: #28a745;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
}

.section-title i {
    margin-right: 0.5rem;
}

.professor-form {
    margin-top: 0.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.form-group {
    margin-bottom: 0;
}

.form-label {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    display: block;
    font-weight: 500;
}

.form-control-sm {
    font-size: 0.85rem;
    padding: 0.375rem 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    width: 100%;
}

.btn-assign {
    background: linear-gradient(135deg, #28a745, #20c997);
    border: none;
    font-weight: 500;
    padding: 0.375rem 0.75rem;
    border-radius: 4px;
}

.btn-assign:hover {
    background: linear-gradient(135deg, #218838, #1ea085);
    transform: translateY(-1px);
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.action-buttons .btn {
    flex: 1;
    font-size: 0.85rem;
    padding: 0.375rem 0.5rem;
    border-radius: 6px;
    font-weight: 500;
}

.btn-warning {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    border: none;
    color: white;
}

.btn-warning:hover {
    background: linear-gradient(135deg, #e0a800, #e55a00);
    transform: translateY(-1px);
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    border: none;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #c82333, #a71e2a);
    transform: translateY(-1px);
}

/* Material Download Button Styles */
.material-download-section {
    margin: 1rem 0;
}

.material-download-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    padding: 1rem 1.25rem;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    border: none;
    font-weight: 500;
}

.material-download-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.material-download-btn:hover::before {
    left: 100%;
}

.material-download-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    text-decoration: none;
    color: white;
}

.btn-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
}

.btn-icon {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    transition: all 0.3s ease;
}

.btn-icon i {
    font-size: 1.1rem;
    color: white;
}

.btn-text {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.btn-title {
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.2;
}

.btn-subtitle {
    font-size: 0.8rem;
    opacity: 0.9;
    font-weight: 400;
}

.btn-arrow {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 6px;
    padding: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    min-width: 32px;
    height: 32px;
}

.btn-arrow i {
    font-size: 0.9rem;
    color: white;
}

.material-download-btn:hover .btn-icon {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
}

.material-download-btn:hover .btn-arrow {
    background: rgba(255, 255, 255, 0.25);
    transform: translateX(2px);
}

.material-download-btn:active {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

/* Responsive adjustments for material button */
@media (max-width: 768px) {
    .material-download-btn {
        padding: 0.875rem 1rem;
    }
    
    .btn-content {
        gap: 0.5rem;
    }
    
    .btn-icon {
        min-width: 36px;
        height: 36px;
        padding: 0.375rem;
    }
    
    .btn-icon i {
        font-size: 1rem;
    }
    
    .btn-title {
        font-size: 0.9rem;
    }
    
    .btn-subtitle {
        font-size: 0.75rem;
    }
    
    .btn-arrow {
        min-width: 28px;
        height: 28px;
        padding: 0.25rem;
    }
    
    .btn-arrow i {
        font-size: 0.8rem;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>
@endsection


