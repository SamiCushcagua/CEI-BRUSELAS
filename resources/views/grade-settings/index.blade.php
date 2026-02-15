@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}">
<script src="{{ asset('js/profesor-calificacion.js') }}"></script>
<h1>test</h1>
<div class="grades-container">
    <div class="grades-header">
        <div>
            <h1 class="grades-title">Configuración de Calificaciones</h1>
            <p class="grades-subtitle">{{ $subject->name }}</p>
        </div>
        <a href="{{ route('grades.show', $subject) }}" class="btn btn-secondary">
            ← Volver a Calificaciones
        </a>
    </div>

    <div class="settings-container">
        <div class="settings-header">
            <h2 class="settings-title">Tipos de Evaluación</h2>
            <p class="settings-description">Configura los pesos y puntuaciones para cada tipo de evaluación</p>
        </div>

        <div class="settings-content">
            @if($gradeSettings->count() > 0)
            <div class="setting-fields">
                @foreach($gradeSettings as $setting)
                <div class="setting-item">
                    <div class="setting-item-header">
                        <h3 class="setting-name">{{ $setting->evaluationType->name }}</h3>
                        <button onclick="deleteSetting(this.dataset.settingId)" data-setting-id="{{ $setting->id }}"
                            class="btn btn-danger btn-small">
                            🗑️ Eliminar
                        </button>
                    </div>
                    <p class="setting-description">{{ $setting->evaluationType->description }}</p>

                    <div class="setting-fields">
                        <div class="field-group">
                            <label class="field-label">Peso (%)</label>
                            <input type="number"
                                value="{{ $setting->weight }}"
                                min="0" max="100" step="0.01"
                                class="field-input setting-input"
                                data-setting-id="{{ $setting->id }}"
                                data-field="weight">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Puntuación Máxima</label>
                            <input type="number"
                                value="{{ $setting->max_score }}"
                                min="0" max="100" step="0.01"
                                class="field-input setting-input"
                                data-setting-id="{{ $setting->id }}"
                                data-field="max_score">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Puntuación Mínima</label>
                            <input type="number"
                                value="{{ $setting->passing_score }}"
                                min="0" max="100" step="0.01"
                                class="field-input setting-input"
                                data-setting-id="{{ $setting->id }}"
                                data-field="passing_score">
                        </div>
                    </div>

                    <div class="mt-20">
                        <button onclick="updateSetting(this.dataset.settingId)" data-setting-id="{{ $setting->id }}"
                            class="btn btn-primary btn-small">
                            💾 Actualizar
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-state">
                <div class="empty-icon">⚙️</div>
                <h3 class="empty-title">No hay configuraciones</h3>
                <p class="empty-description">Agrega tipos de evaluación para configurar las calificaciones.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Agregar nueva configuración -->
    <div class="form-container">
        <div class="form-header">
            <h2 class="form-title">Agregar Tipo de Evaluación</h2>
        </div>

        <div class="form-content">
            <form id="addSettingForm">
                @csrf
                <input type="hidden" name="subject_id" value="{{ $subject->id }}">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Tipo de Evaluación</label>
                        <select name="evaluation_type_id" class="form-select" required>
                            <option value="">Seleccionar tipo...</option>
                            @foreach($evaluationTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Peso (%)</label>
                        <input type="number"
                            name="weight"
                            min="0" max="100" step="0.01"
                            class="form-input" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Puntuación Máxima</label>
                        <input type="number"
                            name="max_score"
                            min="0" max="100" step="0.01"
                            class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Puntuación Mínima</label>
                        <input type="number"
                            name="passing_score"
                            min="0" max="100" step="0.01"
                            class="form-input" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    ➕ Agregar Configuración
                </button>
            </form>
        </div>
    </div>
</div>
@endsection