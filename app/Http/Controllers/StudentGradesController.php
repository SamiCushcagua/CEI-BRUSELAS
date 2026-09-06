<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Period;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class StudentGradesController extends Controller
{
    /**
     * Solo estudiantes: calificaciones del periodo vigente (solo lectura).
     */
    public function index()
    {
        $user = User::query()->findOrFail(Auth::id());

        if (! $user->isStudent()) {
            abort(403, 'Solo los estudiantes pueden ver esta página.');
        }

        $period = Period::currentAcademic() ?? Period::active()->first();

        if (! $period) {
            return view('student.my-grades', [
                'period' => null,
                'rows' => collect(),
                'noPeriodConfigured' => true,
            ]);
        }

        $subjectsQuery = $user->subjectsAsStudent();

        $pivotCols = array_values(array_filter(
            ['period_id', 'diploma_delivered'],
            fn ($c) => Schema::hasColumn('subject_student', $c)
        ));
        if ($pivotCols !== []) {
            $subjectsQuery->withPivot(...$pivotCols);
        }
        if (Schema::hasColumn('subject_student', 'period_id')) {
            $subjectsQuery->wherePivot('period_id', $period->id);
        }

        $subjects = $subjectsQuery->orderBy('subjects.name')->get();

        $subjectIds = $subjects->pluck('id')->map(fn ($id) => (int) $id)->all();

        $gradesBySubject = collect();
        if ($subjectIds !== []) {
            $gradesBySubject = Grade::query()
                ->where('student_id', $user->id)
                ->where('year', (int) $period->year)
                ->where('trimester', (int) $period->trimester)
                ->whereIn('subject_id', $subjectIds)
                ->get()
                ->keyBy(fn (Grade $g) => (int) $g->subject_id);
        }

        $rows = $subjects->map(function ($subject) use ($gradesBySubject) {
            $grade = $gradesBySubject->get((int) $subject->id);
            $diplomaOk = false;
            if (Schema::hasColumn('subject_student', 'diploma_delivered') && $subject->pivot) {
                $diplomaOk = (bool) ($subject->pivot->diploma_delivered ?? false);
            }

            return (object) [
                'subject' => $subject,
                'grade' => $grade,
                'diploma_delivered' => $diplomaOk,
            ];
        });

        return view('student.my-grades', [
            'period' => $period,
            'rows' => $rows,
            'noPeriodConfigured' => false,
        ]);
    }
}
