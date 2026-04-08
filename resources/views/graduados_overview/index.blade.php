@extends('layouts.app')

@section('content')
<div class="grad-overview-page">
    <div class="grad-overview-top">
        <a href="{{ route('welcome') }}" class="grad-overview-back">← Página principal</a>
        <h1 class="grad-overview-title">Resumen de aprobación por materia</h1>
        <p class="grad-overview-desc">
            Cada casilla refleja el <strong>último estado</strong> registrado en calificaciones (aprobó / no aprobó).
            Solo administradores pueden modificarlas.
        </p>
    </div>

    <div class="grad-overview-toolbar">
        <label for="grad-overview-search" class="grad-overview-search-label">Buscar alumno</label>
        <input type="search" id="grad-overview-search" class="grad-overview-search-input"
            placeholder="Nombre o correo…" autocomplete="off" aria-label="Filtrar por nombre o correo">
        <span class="grad-overview-search-hint" id="grad-overview-count"></span>
    </div>

    @if($subjects->isEmpty())
    <p class="grad-overview-empty">No hay materias registradas.</p>
    @elseif($students->isEmpty())
    <p class="grad-overview-empty">No hay estudiantes registrados.</p>
    @else
    <div class="grad-overview-scroll" role="region" aria-label="Tabla de aprobación por materia">
        <table class="grad-overview-table">
            <thead>
                <tr>
                    <th class="grad-overview-th-sticky">Estudiante</th>
                    @foreach($subjects as $subject)
                    <th class="grad-overview-th-subject" title="{{ $subject->name }}">
                        <span class="grad-overview-th-text">{{ $subject->name }}</span>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                @php
                    $searchBlob = strtolower($student->name.' '.$student->email);
                @endphp
                <tr class="grad-overview-row" data-grad-search="{{ e($searchBlob) }}">
                    <td class="grad-overview-td-sticky">
                        <span class="grad-overview-student-name">{{ $student->name }}</span>
                        <span class="grad-overview-student-email">{{ $student->email }}</span>
                    </td>
                    @foreach($subjects as $subject)
                    @php
                        $key = (int) $student->id.'-'.(int) $subject->id;
                        $g = $latestGrades[$key] ?? null;
                        $passed = $g ? (bool) $g->passed : false;
                        $title = $g
                            ? 'Último registro: año '.$g->year.', trimestre '.$g->trimester
                            : 'Sin registro de calificación aún';
                    @endphp
                    <td class="grad-overview-td-cell">
                        <input type="checkbox"
                            class="grad-overview-checkbox"
                            data-student-id="{{ $student->id }}"
                            data-subject-id="{{ $subject->id }}"
                            @checked($passed)
                            title="{{ e($title) }}">
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@unless($subjects->isEmpty() || $students->isEmpty())
<script>
(function () {
    const input = document.getElementById('grad-overview-search');
    const rows = document.querySelectorAll('.grad-overview-row');
    const countEl = document.getElementById('grad-overview-count');
    const total = rows.length;

    function filter() {
        const q = (input.value || '').trim().toLowerCase();
        let visible = 0;
        rows.forEach(function (row) {
            const hay = row.getAttribute('data-grad-search') || '';
            const show = !q || hay.indexOf(q) !== -1;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (countEl) {
            countEl.textContent = q ? visible + ' / ' + total + ' visibles' : total + ' estudiantes';
        }
    }

    if (input) {
        input.addEventListener('input', filter);
        filter();
    }

    document.querySelectorAll('.grad-overview-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            const studentId = cb.dataset.studentId;
            const subjectId = cb.dataset.subjectId;
            const passed = cb.checked;
            const token = document.querySelector('meta[name="csrf-token"]');
            if (!token) return;

            cb.disabled = true;
            fetch("{{ route('admin.graduates-overview.update-pass') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token.getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    student_id: parseInt(studentId, 10),
                    subject_id: parseInt(subjectId, 10),
                    passed: passed
                })
            })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (res) {
                if (!res.ok || !res.data.success) {
                    cb.checked = !passed;
                    alert(res.data.message || 'No se pudo guardar.');
                }
            })
            .catch(function () {
                cb.checked = !passed;
                alert('Error de red al guardar.');
            })
            .finally(function () {
                cb.disabled = false;
            });
        });
    });
})();
</script>
@endunless
@endsection
