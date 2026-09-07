@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}?v={{ filemtime(public_path('css/styles_PROFESOR.css')) }}">

<div class="grades-container"
     data-update-url="{{ route('admin.period-history.update', [], false) }}"
     id="period-history-root">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>

    <div class="grades-header">
        <div>
            <h1 class="grades-title">Historial por trimestre</h1>
            <p class="grades-subtitle">Consulta y modifica calificaciones y asistencias de un periodo</p>
        </div>
    </div>

    <div id="period-history-flash" class="history-flash" hidden></div>

    @if($noPeriods ?? false)
        <div class="empty-state">
            <div class="empty-icon">📅</div>
            <h3 class="empty-title">No hay periodos configurados</h3>
        </div>
    @else
        <div class="form-container">
            <div class="form-header">
                <h2 class="form-title">Seleccionar trimestre</h2>
            </div>
            <div class="form-content">
                <form method="GET" action="{{ route('admin.period-history') }}" class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="period_id">Periodo</label>
                        <select name="period_id" id="period_id" class="form-select" onchange="this.form.submit()">
                            @foreach($periods as $p)
                                <option value="{{ $p->id }}" @selected((string) $period->id === (string) $p->id)>
                                    {{ $p->name }} — {{ $p->year }}, trim. {{ $p->trimester }}
                                    @if($p->is_active) (vigente) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group form-actions">
                        <button type="submit" class="btn btn-success w-full">Ver historial</button>
                    </div>
                </form>
            </div>
        </div>

        <p class="grades-subtitle" style="margin: 1rem 0;">
            Mostrando: <strong>{{ $period->name }}</strong>
            ({{ $period->year }} · Trimestre {{ $period->trimester }})
            @if($period->start_date && $period->end_date)
                — {{ $period->start_date->format('d/m/Y') }} a {{ $period->end_date->format('d/m/Y') }}
            @endif
        </p>

        @if($courseBlocks->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3 class="empty-title">Sin cursos con maestro y alumnos</h3>
                <p class="empty-description">En este periodo no hay materias con profesor asignado y estudiantes inscritos.</p>
            </div>
        @else
            @foreach($courseBlocks as $block)
                @php
                    $subject = $block['subject'];
                    $professors = $block['professors'];
                    $students = $block['students'];
                    $gradesByStudent = $block['gradesByStudent'];
                    $attendanceData = $block['attendanceData'];
                @endphp

                <div class="grades-table-container history-course-block"
                     data-subject-id="{{ $subject->id }}"
                     data-period-id="{{ $period->id }}">
                    <div class="grades-table-header">
                        <h3 class="grades-table-title">
                            @if($subject->Nivel)
                                Curso {{ $subject->Nivel }} —
                            @endif
                            {{ $subject->name }}
                        </h3>
                        <div class="grades-table-actions history-block-toolbar">
                            <span class="subject-info">
                                Maestro(s):
                                @if($professors->isEmpty())
                                    Sin asignar
                                @else
                                    {{ $professors->pluck('name')->implode(', ') }}
                                @endif
                            </span>
                            @if($students->isNotEmpty())
                                <button type="button" class="btn btn-primary btn-sm history-btn-edit">Modificar</button>
                                <button type="button" class="btn btn-secondary btn-sm history-btn-cancel" hidden>Cancelar</button>
                                <button type="button" class="btn btn-success btn-sm history-btn-save" hidden>Guardar cambios</button>
                            @endif
                        </div>
                    </div>

                    {{-- Vista lectura --}}
                    <div class="history-view-mode">
                        <div class="grades-table-header" style="border-top: 1px solid #e5e7eb;">
                            <h3 class="grades-table-title" style="font-size: 1rem;">📝 Resumen de calificaciones</h3>
                        </div>
                        <div class="grades-table-wrapper" style="overflow-x: auto;">
                            <table class="grades-table">
                                <thead>
                                    <tr>
                                        <th class="sticky-col">Estudiante</th>
                                        <th>Tareas</th>
                                        <th>Examen 1</th>
                                        <th>Examen 2</th>
                                        <th>Participación</th>
                                        <th>Biblia</th>
                                        <th>Versículos</th>
                                        <th>Otro</th>
                                        <th>Promedio</th>
                                        <th>Aprobó</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $student)
                                        @php $g = $gradesByStudent->get((int) $student->id); @endphp
                                        <tr>
                                            <td class="sticky-col">
                                                <div class="student-info">
                                                    <div class="student-avatar">{{ substr($student->name, 0, 1) }}</div>
                                                    <div class="student-details">
                                                        <h4>{{ $student->name }}</h4>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $g && $g->task_score !== null ? number_format((float) $g->task_score, 2) : '—' }}</td>
                                            <td class="text-center">{{ $g && $g->exam_score1 !== null ? number_format((float) $g->exam_score1, 2) : '—' }}</td>
                                            <td class="text-center">{{ $g && $g->exam_score2 !== null ? number_format((float) $g->exam_score2, 2) : '—' }}</td>
                                            <td class="text-center">{{ $g && $g->participation_score !== null ? number_format((float) $g->participation_score, 2) : '—' }}</td>
                                            <td class="text-center">{{ $g && $g->bible_score !== null ? number_format((float) $g->bible_score, 2) : '—' }}</td>
                                            <td class="text-center">{{ $g && $g->text_score !== null ? number_format((float) $g->text_score, 2) : '—' }}</td>
                                            <td class="text-center">{{ $g && $g->other_score !== null ? number_format((float) $g->other_score, 2) : '—' }}</td>
                                            <td class="text-center">
                                                @if($g)
                                                    <strong>{{ number_format($g->average_score, 2) }}</strong>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($g && $g->passed)
                                                    <span class="status-badge status-approved">Sí</span>
                                                @elseif($g)
                                                    <span class="status-badge stat-red">No</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" style="text-align: center; color: #6b7280;">Sin alumnos</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="grades-table-header" style="border-top: 1px solid #e5e7eb;">
                            <h3 class="grades-table-title" style="font-size: 1rem;">📅 Resumen de asistencias del trimestre</h3>
                        </div>
                        @if(count($sundays) === 0)
                            <p class="grades-subtitle" style="padding: 1rem;">No hay fechas de clase definidas para este periodo.</p>
                        @else
                            <div class="grades-table-wrapper" style="overflow-x: auto;">
                                <table class="grades-table">
                                    <thead>
                                        <tr>
                                            <th class="sticky-col">Estudiante</th>
                                            @foreach($sundays as $sunday)
                                                <th class="text-center">
                                                    {{ \Carbon\Carbon::parse($sunday)->format('d/m/Y') }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                            @php
                                                $absentCount = 0;
                                                $studentRecords = $attendanceData[$student->id] ?? [];
                                                foreach ($studentRecords as $record) {
                                                    if ($record && $record->attendance_status === 'absent') {
                                                        $absentCount++;
                                                    }
                                                }
                                            @endphp
                                            <tr class="{{ $absentCount >= 3 ? 'row-high-absent' : '' }}">
                                                <td class="sticky-col">
                                                    <div class="student-info">
                                                        <div class="student-avatar">{{ substr($student->name, 0, 1) }}</div>
                                                        <div class="student-details">
                                                            <h4>{{ $student->name }}</h4>
                                                        </div>
                                                    </div>
                                                </td>
                                                @foreach($sundays as $sunday)
                                                    <td class="text-center">
                                                        @php $record = $attendanceData[$student->id][$sunday] ?? null; @endphp
                                                        @if($record)
                                                            <div class="attendance-summary">
                                                                @if($record->attendance_status == 'present')
                                                                    <span class="status-badge status-approved">✅</span>
                                                                @elseif($record->attendance_status == 'late')
                                                                    <span class="status-badge stat-yellow">⏰</span>
                                                                @else
                                                                    <span class="status-badge stat-red">❌</span>
                                                                @endif
                                                                @if($record->bible_verse_delivered)
                                                                    <span class="status-badge stat-green">📖</span>
                                                                @else
                                                                    <span class="status-badge stat-red">📖</span>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="status-badge stat-red">-</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- Vista edición --}}
                    @if($students->isNotEmpty())
                    <div class="history-edit-mode" hidden>
                        <div class="grades-table-header" style="border-top: 1px solid #e5e7eb;">
                            <h3 class="grades-table-title" style="font-size: 1rem;">📝 Editar calificaciones</h3>
                            <p class="grades-subtitle" style="margin: 0;">Modifica los valores y pulsa <strong>Guardar cambios</strong>.</p>
                        </div>
                        <div class="grades-table-wrapper" style="overflow-x: auto;">
                            <table class="grades-table history-grades-edit-table">
                                <thead>
                                    <tr>
                                        <th class="sticky-col">Estudiante</th>
                                        <th>Tareas</th>
                                        <th>Examen 1</th>
                                        <th>Examen 2</th>
                                        <th>Participación</th>
                                        <th>Biblia</th>
                                        <th>Versículos</th>
                                        <th>Otro</th>
                                        <th>Promedio</th>
                                        <th>Aprobó</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $student)
                                        @php $g = $gradesByStudent->get((int) $student->id); @endphp
                                        <tr data-grade-student="{{ $student->id }}">
                                            <td class="sticky-col">
                                                <div class="student-info">
                                                    <div class="student-avatar">{{ substr($student->name, 0, 1) }}</div>
                                                    <div class="student-details">
                                                        <h4>{{ $student->name }}</h4>
                                                    </div>
                                                </div>
                                            </td>
                                            @foreach(['task_score','exam_score1','exam_score2','participation_score','bible_score','text_score','other_score'] as $field)
                                                <td>
                                                    <input type="number"
                                                           class="history-grade-input"
                                                           data-student-id="{{ $student->id }}"
                                                           data-field="{{ $field }}"
                                                           value="{{ $g && $g->{$field} !== null ? $g->{$field} : '' }}"
                                                           min="0" max="100" step="0.01">
                                                </td>
                                            @endforeach
                                            <td class="text-center">
                                                <strong class="history-avg" data-student-id="{{ $student->id }}">
                                                    {{ $g ? number_format($g->average_score, 2) : '0.00' }}
                                                </strong>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox"
                                                       class="history-grade-check"
                                                       data-student-id="{{ $student->id }}"
                                                       data-field="passed"
                                                       @checked($g && $g->passed)>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="grades-table-header" style="border-top: 1px solid #e5e7eb;">
                            <h3 class="grades-table-title" style="font-size: 1rem;">📅 Editar asistencias</h3>
                            <p class="grades-subtitle" style="margin: 0;">Vacío = sin registro. Marca 📖 si entregó versículo.</p>
                        </div>
                        @if(count($sundays) === 0)
                            <p class="grades-subtitle" style="padding: 1rem;">No hay fechas de clase definidas para este periodo.</p>
                        @else
                            <div class="grades-table-wrapper" style="overflow-x: auto;">
                                <table class="grades-table history-attendance-edit-table">
                                    <thead>
                                        <tr>
                                            <th class="sticky-col">Estudiante</th>
                                            @foreach($sundays as $sunday)
                                                <th class="text-center">
                                                    {{ \Carbon\Carbon::parse($sunday)->format('d/m/Y') }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                            <tr>
                                                <td class="sticky-col">
                                                    <div class="student-info">
                                                        <div class="student-avatar">{{ substr($student->name, 0, 1) }}</div>
                                                        <div class="student-details">
                                                            <h4>{{ $student->name }}</h4>
                                                        </div>
                                                    </div>
                                                </td>
                                                @foreach($sundays as $sunday)
                                                    @php $record = $attendanceData[$student->id][$sunday] ?? null; @endphp
                                                    <td class="text-center history-att-cell">
                                                        <select class="history-att-status"
                                                                data-student-id="{{ $student->id }}"
                                                                data-class-date="{{ $sunday }}">
                                                            <option value="" @selected(!$record)>—</option>
                                                            <option value="present" @selected($record && $record->attendance_status === 'present')>Presente</option>
                                                            <option value="late" @selected($record && $record->attendance_status === 'late')>Tarde</option>
                                                            <option value="absent" @selected($record && $record->attendance_status === 'absent')>Ausente</option>
                                                        </select>
                                                        <label class="history-bible-label" title="Versículo">
                                                            <input type="checkbox"
                                                                   class="history-att-bible"
                                                                   data-student-id="{{ $student->id }}"
                                                                   data-class-date="{{ $sunday }}"
                                                                   @checked($record && $record->bible_verse_delivered)>
                                                            📖
                                                        </label>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
            @endforeach
        @endif
    @endif
</div>

<style>
.history-course-block {
    margin-bottom: 2rem;
}
.attendance-summary {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.2rem;
}
.history-block-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
}
.history-grade-input {
    width: 4.5rem;
    padding: 0.25rem 0.35rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 0.85rem;
    text-align: center;
}
.history-att-cell {
    vertical-align: middle;
    min-width: 7rem;
}
.history-att-status {
    display: block;
    width: 100%;
    max-width: 6.5rem;
    margin: 0 auto 0.25rem;
    padding: 0.2rem 0.25rem;
    font-size: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
}
.history-bible-label {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    font-size: 0.8rem;
    cursor: pointer;
    user-select: none;
}
.history-flash {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-weight: 500;
}
.history-flash.is-ok {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
}
.history-flash.is-err {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var root = document.getElementById('period-history-root');
    if (!root) return;

    var updateUrl = root.getAttribute('data-update-url') || '';
    try {
        updateUrl = new URL(updateUrl, window.location.href).pathname;
    } catch (e) { /* keep as-is */ }

    var flash = document.getElementById('period-history-flash');

    function showFlash(msg, ok) {
        if (!flash) return;
        flash.hidden = false;
        flash.textContent = msg;
        flash.className = 'history-flash ' + (ok ? 'is-ok' : 'is-err');
        flash.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function scoreVal(v) {
        if (v === null || v === undefined || String(v).trim() === '') return null;
        var n = parseFloat(v);
        return isNaN(n) ? null : n;
    }

    function updateAvg(row) {
        var fields = ['task_score','exam_score1','exam_score2','participation_score','bible_score','text_score','other_score'];
        var scores = [];
        fields.forEach(function (f) {
            var input = row.querySelector('.history-grade-input[data-field="' + f + '"]');
            var n = scoreVal(input && input.value);
            if (n !== null) scores.push(n);
        });
        var avgEl = row.querySelector('.history-avg');
        if (!avgEl) return;
        avgEl.textContent = scores.length
            ? (scores.reduce(function (a, b) { return a + b; }, 0) / scores.length).toFixed(2)
            : '0.00';
    }

    document.querySelectorAll('.history-course-block').forEach(function (block) {
        var btnEdit = block.querySelector('.history-btn-edit');
        var btnCancel = block.querySelector('.history-btn-cancel');
        var btnSave = block.querySelector('.history-btn-save');
        var viewMode = block.querySelector('.history-view-mode');
        var editMode = block.querySelector('.history-edit-mode');
        if (!btnEdit || !viewMode || !editMode) return;

        function enterEdit() {
            viewMode.hidden = true;
            editMode.hidden = false;
            btnEdit.hidden = true;
            if (btnCancel) btnCancel.hidden = false;
            if (btnSave) btnSave.hidden = false;
        }

        function leaveEdit() {
            viewMode.hidden = false;
            editMode.hidden = true;
            btnEdit.hidden = false;
            if (btnCancel) btnCancel.hidden = true;
            if (btnSave) btnSave.hidden = true;
        }

        btnEdit.addEventListener('click', enterEdit);
        if (btnCancel) btnCancel.addEventListener('click', function () {
            leaveEdit();
            window.location.reload();
        });

        editMode.querySelectorAll('.history-grade-input').forEach(function (input) {
            input.addEventListener('input', function () {
                var row = input.closest('tr');
                if (row) updateAvg(row);
            });
        });

        if (btnSave) {
            btnSave.addEventListener('click', function () {
                var token = document.querySelector('meta[name="csrf-token"]');
                if (!token) {
                    showFlash('CSRF no encontrado. Recarga la página.', false);
                    return;
                }

                var studentIds = [];
                editMode.querySelectorAll('.history-grade-input[data-student-id]').forEach(function (el) {
                    var id = el.getAttribute('data-student-id');
                    if (studentIds.indexOf(id) === -1) studentIds.push(id);
                });

                var grades = studentIds.map(function (sid) {
                    function field(name) {
                        var el = editMode.querySelector('.history-grade-input[data-student-id="' + sid + '"][data-field="' + name + '"]');
                        return scoreVal(el && el.value);
                    }
                    var passedEl = editMode.querySelector('.history-grade-check[data-student-id="' + sid + '"][data-field="passed"]');
                    return {
                        student_id: parseInt(sid, 10),
                        task_score: field('task_score'),
                        exam_score1: field('exam_score1'),
                        exam_score2: field('exam_score2'),
                        participation_score: field('participation_score'),
                        bible_score: field('bible_score'),
                        text_score: field('text_score'),
                        other_score: field('other_score'),
                        passed: !!(passedEl && passedEl.checked),
                    };
                });

                var attendance = [];
                editMode.querySelectorAll('.history-att-status').forEach(function (sel) {
                    var sid = sel.getAttribute('data-student-id');
                    var date = sel.getAttribute('data-class-date');
                    var bible = editMode.querySelector(
                        '.history-att-bible[data-student-id="' + sid + '"][data-class-date="' + date + '"]'
                    );
                    attendance.push({
                        student_id: parseInt(sid, 10),
                        class_date: date,
                        attendance_status: sel.value || null,
                        bible_verse_delivered: !!(bible && bible.checked),
                    });
                });

                var payload = {
                    period_id: parseInt(block.getAttribute('data-period-id'), 10),
                    subject_id: parseInt(block.getAttribute('data-subject-id'), 10),
                    grades: grades,
                    attendance: attendance,
                };

                btnSave.disabled = true;
                btnSave.textContent = 'Guardando...';

                fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                })
                    .then(function (r) {
                        return r.json().then(function (data) {
                            return { ok: r.ok, data: data };
                        }).catch(function () {
                            return { ok: false, data: { message: 'Respuesta inválida del servidor' } };
                        });
                    })
                    .then(function (res) {
                        if (res.ok && res.data && res.data.success) {
                            showFlash(res.data.message || 'Guardado', true);
                            window.location.reload();
                            return;
                        }
                        showFlash((res.data && res.data.message) || 'No se pudo guardar', false);
                        btnSave.disabled = false;
                        btnSave.textContent = 'Guardar cambios';
                    })
                    .catch(function () {
                        showFlash('Error de red al guardar', false);
                        btnSave.disabled = false;
                        btnSave.textContent = 'Guardar cambios';
                    });
            });
        }
    });
});
</script>
@endsection
