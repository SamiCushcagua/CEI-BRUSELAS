@php
    $student = $row->student;
    $passMap = $passByStudent[$student->id] ?? [];
    $historyId = $historyPrefix.'-history-'.$student->id;
    $selectedSubjectId = $row->assignedSubjectId ?? null;
@endphp
<tr class="pending-student-row" data-student-id="{{ $student->id }}">
    <td>
        <button type="button"
            class="pending-student-toggle"
            aria-expanded="false"
            data-toggle-history="{{ $historyId }}">
            <span class="pending-toggle-icon" aria-hidden="true">▸</span>
            {{ $student->name }}
        </button>
        <div class="pending-student-email">{{ $student->email }}</div>
    </td>
    <td>{{ $row->materia }}</td>
    <td>{{ $row->paso }}</td>
    <td>
        <form method="POST"
              action="{{ route('students.assign-subject', $student) }}"
              class="pending-assign-form">
            @csrf
            @if($filterSubjectId)
                <input type="hidden" name="filter_subject_id" value="{{ $filterSubjectId }}">
            @endif
            <select name="subject_id"
                    class="pending-assign-select"
                    onchange="this.form.submit()"
                    aria-label="Asignar {{ $student->name }} a materia">
                <option value="">No asignado</option>
                @foreach($assignableSubjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string) $selectedSubjectId === (string) $subject->id)>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </td>
</tr>
<tr class="pending-history-row" id="{{ $historyId }}" hidden>
    <td colspan="4">
        <div class="pending-history-panel">
            <div class="pending-history-title">Historial de aprobación — {{ $student->name }}</div>
            <div class="pending-history-grid">
                @foreach($assignableSubjects as $subject)
                    @php
                        $passed = $passMap[$subject->id] ?? null;
                        if ($passed === true) {
                            $cls = 'is-pass';
                            $label = 'Sí';
                            $titleLabel = 'Aprobó';
                        } elseif ($passed === false) {
                            $cls = 'is-fail';
                            $label = 'No';
                            $titleLabel = 'No aprobó';
                        } else {
                            $cls = 'is-empty';
                            $label = '—';
                            $titleLabel = 'Sin registro';
                        }
                    @endphp
                    <div class="pending-history-chip {{ $cls }}" title="{{ $subject->name }}: {{ $titleLabel }}">
                        <span class="pending-history-chip-name">{{ $subject->name }}</span>
                        <span class="pending-history-chip-status">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </td>
</tr>
