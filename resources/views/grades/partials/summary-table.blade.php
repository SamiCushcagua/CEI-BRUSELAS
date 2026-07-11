<div class="grades-table-container">
    <div class="grades-table-header">
        <h3>📊 Resumen de Estudiantes - {{ $subject->name }}</h3>
        <div class="grades-table-actions">
            <span class="subject-info">{{ $subject->name }} - {{ $currentYear }} - Trimestre {{ $currentTrimester }}</span>
        </div>
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
                    <th>Versiculos</th>
                    <th>Otro</th>
                    <th>Promedio</th>
                    <th>Aprobó (trim.)</th>
                    <th>Diploma</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    @php
                        $studentGrade = $grades->where('student_id', $student->id)->first();
                        $diplomaOk = (bool) ($student->pivot->diploma_delivered ?? false);
                    @endphp
                    <tr>
                        <td class="sticky-col">
                            <div class="student-info">
                                <div class="student-avatar">
                                    {{ substr($student->name, 0, 1) }}
                                </div>
                                <div class="student-details">
                                    <h4>{{ $student->name }}</h4>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->task_score ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->task_score ? number_format($studentGrade->task_score, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->exam_score1 ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->exam_score1 ? number_format($studentGrade->exam_score1, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->exam_score2 ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->exam_score2 ? number_format($studentGrade->exam_score2, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->participation_score ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->participation_score ? number_format($studentGrade->participation_score, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->bible_score ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->bible_score ? number_format($studentGrade->bible_score, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->text_score ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->text_score ? number_format($studentGrade->text_score, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="grade-display {{ $studentGrade && $studentGrade->other_score ? 'has-grade' : 'no-grade' }}">
                                {{ $studentGrade && $studentGrade->other_score ? number_format($studentGrade->other_score, 2) : '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="average-score">
                                {{ $studentGrade ? number_format($studentGrade->average_score, 2) : '0.00' }}
                            </span>
                        </td>
                        <td>{{ $studentGrade && $studentGrade->passed ? 'Sí' : 'No' }}</td>
                        <td>{{ $diplomaOk ? 'Sí' : 'No' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center">No hay estudiantes inscritos en esta materia.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
