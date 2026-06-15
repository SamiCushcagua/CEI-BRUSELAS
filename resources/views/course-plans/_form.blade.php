@php
    $padList = function (?array $items, int $count) {
        $items = $items ?? [];
        $padded = array_pad($items, $count, '');
        return array_slice($padded, 0, $count);
    };
@endphp

<style>
    .course-plan-section {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .course-plan-section h2 {
        margin: 0 0 1rem;
        font-size: 1.15rem;
        color: #2c3e50;
        border-bottom: 2px solid #667eea;
        padding-bottom: 0.4rem;
    }

    .course-plan-section h3 {
        margin: 0.75rem 0 0.5rem;
        font-size: 0.95rem;
        color: #495057;
    }

    .course-plan-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }

    .course-plan-meta-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.75rem 1rem;
    }

    .course-plan-meta-item strong {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .course-plan-input,
    .course-plan-textarea {
        width: 100%;
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 0.5rem 0.65rem;
        font-size: 0.92rem;
    }

    .course-plan-textarea {
        min-height: 70px;
        resize: vertical;
    }

    .course-plan-list-item {
        margin-bottom: 0.5rem;
    }

    .course-plan-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .course-plan-table th,
    .course-plan-table td {
        border: 1px solid #dee2e6;
        padding: 0.55rem;
        vertical-align: top;
    }

    .course-plan-table th {
        background: #f1f3f5;
        text-align: left;
    }

    .course-plan-readonly {
        color: #495057;
        line-height: 1.5;
    }

    .course-plan-status {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .course-plan-status-draft {
        background: #fff3cd;
        color: #856404;
    }

    .course-plan-status-published {
        background: #d4edda;
        color: #155724;
    }

    .course-plan-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }

    .course-plan-bullet-list {
        margin: 0;
        padding-left: 1.2rem;
    }

    .course-plan-bullet-list li {
        margin-bottom: 0.35rem;
    }
</style>

<div class="course-plan-meta">
    <div class="course-plan-meta-item">
        <strong>Clase / Título</strong>
        <span>{{ $auto['title'] }}</span>
    </div>
    <div class="course-plan-meta-item">
        <strong>Maestro(s)</strong>
        <span>{{ $auto['teachers'] ?: 'Sin asignar' }}</span>
    </div>
    <div class="course-plan-meta-item">
        <strong>Periodo</strong>
        <span>{{ $period->name }}</span>
    </div>
    <div class="course-plan-meta-item">
        <strong>Duración de la materia</strong>
        <span>{{ $auto['duration'] }}</span>
    </div>
</div>

@if($editable ?? false)
<form action="{{ route('course-plans.update', $subject) }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="period_id" value="{{ $period->id }}">
@endif

<section class="course-plan-section">
    <h2>Libro texto</h2>
    @if($editable ?? false)
        <input type="text" name="textbook" class="course-plan-input"
            value="{{ old('textbook', $plan->textbook) }}"
            placeholder="Nombre del libro texto">
    @else
        <p class="course-plan-readonly">{{ $plan->textbook ?: '—' }}</p>
    @endif
</section>

<section class="course-plan-section">
    <h2>I. Objetivos</h2>

    @foreach([
        'cognitive_objectives' => 'A. Cognoscitivos (conocimiento)',
        'affective_objectives' => 'B. Afectivos (actitudes)',
        'psychomotor_objectives' => 'C. Psicomotores (habilidades)',
    ] as $field => $label)
        <h3>{{ $label }}</h3>
        @php $items = $padList(old($field, $plan->{$field}), 4); @endphp
        @for($i = 0; $i < 4; $i++)
            <div class="course-plan-list-item">
                @if($editable ?? false)
                    <input type="text" name="{{ $field }}[]" class="course-plan-input"
                        value="{{ $items[$i] }}"
                        placeholder="{{ $i + 1 }}. Objetivo">
                @else
                    <p class="course-plan-readonly">{{ $items[$i] ? ($i + 1).'. '.$items[$i] : '—' }}</p>
                @endif
            </div>
        @endfor
    @endforeach
</section>

<section class="course-plan-section">
    <h2>II. Duración de la materia</h2>
    <p class="course-plan-readonly">{{ $auto['duration'] }} ({{ $period->name }})</p>
</section>

<section class="course-plan-section">
    <h2>III. Requisitos de la materia</h2>
    @php $requirements = $padList(old('requirements', $plan->requirements), 4); @endphp
    @for($i = 0; $i < 4; $i++)
        <div class="course-plan-list-item">
            @if($editable ?? false)
                <input type="text" name="requirements[]" class="course-plan-input"
                    value="{{ $requirements[$i] }}"
                    placeholder="Requisito {{ $i + 1 }}">
            @else
                <p class="course-plan-readonly">{{ $requirements[$i] ? '• '.$requirements[$i] : '—' }}</p>
            @endif
        </div>
    @endfor
</section>

<section class="course-plan-section">
    <h2>IV. Sistema de calificación</h2>
    <table class="course-plan-table">
        <thead>
            <tr>
                <th>Criterio</th>
                <th>Ponderación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($auto['grading_system'] as $criterion => $weight)
                <tr>
                    <td>{{ $criterion }}</td>
                    <td>{{ $weight }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="course-plan-readonly" style="margin-top: 0.75rem;">
        El promedio trimestral es la media de los criterios registrados en el sistema de calificaciones.
    </p>
</section>

<section class="course-plan-section">
    <h2>V. Parcelación de la materia</h2>
    <div style="overflow-x: auto;">
        <table class="course-plan-table">
            <thead>
                <tr>
                    <th>Clase</th>
                    <th>Fecha</th>
                    <th>Tema</th>
                    <th>Asignaciones</th>
                    @if($editable ?? false)
                        <th>Tarea</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($plan->lessons as $index => $lesson)
                    <tr>
                        <td>{{ $lesson->class_number }}</td>
                        <td>{{ $lesson->class_date->format('d/m/Y') }}</td>
                        @if($editable ?? false)
                            <td>
                                <input type="hidden" name="lessons[{{ $index }}][id]" value="{{ $lesson->id }}">
                                <input type="text" name="lessons[{{ $index }}][topic]" class="course-plan-input"
                                    value="{{ old('lessons.'.$index.'.topic', $lesson->topic) }}"
                                    placeholder="Tema de la clase">
                            </td>
                            <td>
                                <textarea name="lessons[{{ $index }}][assignment]" class="course-plan-textarea"
                                    placeholder="Asignación">{{ old('lessons.'.$index.'.assignment', $lesson->assignment) }}</textarea>
                            </td>
                            <td style="text-align: center;">
                                <input type="checkbox" name="lessons[{{ $index }}][has_homework]" value="1"
                                    @checked(old('lessons.'.$index.'.has_homework', $lesson->has_homework))>
                            </td>
                        @else
                            <td>{{ $lesson->topic ?: '—' }}</td>
                            <td>{{ $lesson->assignment ?: '—' }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="course-plan-section">
    <h2>Datos complementarios</h2>
    @if($editable ?? false)
        <textarea name="complementary_data" class="course-plan-textarea"
            placeholder="Teléfono, e-mail del maestro, etc.">{{ old('complementary_data', $plan->complementary_data) }}</textarea>
    @else
        <p class="course-plan-readonly">{!! nl2br(e($plan->complementary_data ?: '—')) !!}</p>
    @endif
</section>

<section class="course-plan-section">
    <h2>Descripción de las tareas</h2>
    @php $tasks = $padList(old('task_descriptions', $plan->task_descriptions), 4); @endphp
    @for($i = 0; $i < 4; $i++)
        <div class="course-plan-list-item">
            @if($editable ?? false)
                <textarea name="task_descriptions[]" class="course-plan-textarea"
                    placeholder="Tarea {{ $i + 1 }}">{{ $tasks[$i] }}</textarea>
            @else
                <p class="course-plan-readonly">{{ $tasks[$i] ? ($i + 1).'. '.$tasks[$i] : '—' }}</p>
            @endif
        </div>
    @endfor
</section>

<section class="course-plan-section">
    <h2>Notas</h2>
    @php $notes = $padList(old('general_notes', $plan->general_notes), 5); @endphp
    @for($i = 0; $i < 5; $i++)
        <div class="course-plan-list-item">
            @if($editable ?? false)
                <input type="text" name="general_notes[]" class="course-plan-input"
                    value="{{ $notes[$i] }}"
                    placeholder="Nota {{ $i + 1 }}">
            @else
                <p class="course-plan-readonly">{{ $notes[$i] ? '• '.$notes[$i] : '—' }}</p>
            @endif
        </div>
    @endfor
</section>

<section class="course-plan-section">
    <h2>VI. Bibliografía</h2>
    @php $bibliography = $padList(old('bibliography', $plan->bibliography), 8); @endphp
    @for($i = 0; $i < 8; $i++)
        <div class="course-plan-list-item">
            @if($editable ?? false)
                <textarea name="bibliography[]" class="course-plan-textarea"
                    placeholder="Referencia bibliográfica {{ $i + 1 }}">{{ $bibliography[$i] }}</textarea>
            @else
                <p class="course-plan-readonly">{{ $bibliography[$i] ? '• '.$bibliography[$i] : '—' }}</p>
            @endif
        </div>
    @endfor
</section>

@if($editable ?? false)
    <div class="course-plan-actions">
        <button type="submit" name="status" value="draft" class="btn btn-secondary">
            Guardar borrador
        </button>
        <button type="submit" name="status" value="published" class="btn btn-primary">
            Publicar plan
        </button>
    </div>
</form>
@endif
