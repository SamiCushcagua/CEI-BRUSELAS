@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">
<script src="{{ asset('js/profesor-calificacion.js') }}"></script>

<div class="grades-container">
    <div class="grades-header">
        <div>
            <h1 class="grades-title">Reportes de Calificaciones</h1>
        </div>
        <div class="grades-info">
            <div><span>Trimestre:</span> {{ $currentTrimester ?? 1 }}</div>
            <div><span>Año:</span> {{ date('Y') }}</div>
        </div>
        <a href="{{ route('grades.index') }}" class="btn btn-secondary">
            ← Volver a Calificaciones
        </a>
    </div>
</div>

<div class="reports-grid">
    <!-- Generar Reporte -->
    <div class="report-card">
        <h2 class="report-title">Generar Reporte</h2>

        <form id="reportForm">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tipo de Reporte</label>
                    <select name="report_type" class="form-select" required>
                        <option value="">Seleccionar tipo...</option>
                        <option value="subject">Por Materia</option>
                        <option value="student">Por Estudiante</option>
                        <option value="trimester">Por Trimestre</option>
                        <option value="year">Anual</option>
                    </select>

                    <div class="form-group">
                        <label class="form-label">Materia</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Seleccionar materia...</option>
                            @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="form-group">
                    <label class="form-label">Formato</label>
                    <select name="format" class="form-select" required>
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Trimestre</label>
                    <select name="trimester" class="form-select" required>
                        <option value="1">Trimestre 1</option>
                        <option value="2">Trimestre 2</option>
                        <option value="3">Trimestre 3</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Año</label>
                    <input type="number"
                        name="year"
                        value="{{ date('Y') }}"
                        min="2020" max="2030"
                        class="form-input" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full">
                📊 Generar Reporte
            </button>
        </form>
    </div>



</div>
@endsection