<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Period;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StudentTimelineController extends Controller
{
    /**
     * Timeline del recorrido académico del alumno autenticado.
     */
    public function index(): View
    {
        $user = Auth::user();
        if (! $user || ! $user->isStudent()) {
            abort(403, 'Solo los estudiantes pueden ver su recorrido.');
        }

        $subjects = Subject::query()
            ->orderByRaw('CASE WHEN Nivel IS NULL THEN 1 ELSE 0 END')
            ->orderBy('Nivel')
            ->orderBy('name')
            ->get();

        $currentPeriod = Period::currentAcademic() ?? Period::active()->first();

        $currentSubjectIds = [];
        if ($currentPeriod && Schema::hasColumn('subject_student', 'period_id')) {
            $currentSubjectIds = $user->subjectsAsStudent()
                ->wherePivot('period_id', $currentPeriod->id)
                ->pluck('subjects.id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $passedSubjectIds = [];
        $gradesOrdered = Grade::query()
            ->where('student_id', $user->id)
            ->orderByDesc('year')
            ->orderByDesc('trimester')
            ->orderByDesc('id')
            ->get();

        $seen = [];
        foreach ($gradesOrdered as $g) {
            $sid = (int) $g->subject_id;
            if (isset($seen[$sid])) {
                continue;
            }
            $seen[$sid] = true;
            if ($g->passed) {
                $passedSubjectIds[$sid] = true;
            }
        }

        $nodes = $subjects->map(function (Subject $subject) use ($currentSubjectIds, $passedSubjectIds) {
            $id = (int) $subject->id;

            if (in_array($id, $currentSubjectIds, true)) {
                $status = 'current';
            } elseif (isset($passedSubjectIds[$id])) {
                $status = 'past';
            } else {
                $status = 'future';
            }

            return [
                'id' => $id,
                'name' => $subject->name,
                'nivel' => $subject->Nivel,
                'description' => $subject->description ?: 'Sin descripción disponible para este curso.',
                'status' => $status,
            ];
        });

        return view('students.timeline', [
            'student' => $user,
            'currentPeriod' => $currentPeriod,
            'nodes' => $nodes,
        ]);
    }
}
