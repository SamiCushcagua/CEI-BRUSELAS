<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Period;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminSubjectEnrollmentOutcomeController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::check() || ! Auth::user()->is_admin) {
            abort(403);
        }

        $periods = Period::orderBy('year', 'desc')->orderBy('trimester', 'desc')->get();

        $period = $request->filled('period_id')
            ? Period::findOrFail($request->period_id)
            : (Period::active()->first() ?? $periods->first());

        if (! $period) {
            return view('admin.subject-enrollment-outcomes', [
                'periods' => collect(),
                'period' => null,
                'subjects' => collect(),
                'subject' => null,
                'rows' => collect(),
            ]);
        }

        $subjects = Subject::whereIn('id', function ($q) use ($period) {
            $q->select('subject_id')
                ->from('subject_student')
                ->where('period_id', $period->id);
        })->orderBy('name')->get();

        $subjectId = $request->query('subject_id');
        if ($subjectId && ! $subjects->pluck('id')->contains((int) $subjectId)) {
            $subjectId = null;
        }
        if (! $subjectId && $subjects->isNotEmpty()) {
            $subjectId = $subjects->first()->id;
        }

        $subject = $subjectId ? Subject::find($subjectId) : null;

        $rows = collect();
        if ($subject && $subjects->pluck('id')->contains($subject->id)) {
            $students = $subject->studentsForPeriod($period)->orderBy('name')->get();

            $grades = Grade::where('subject_id', $subject->id)
                ->where('year', $period->year)
                ->where('trimester', $period->trimester)
                ->get()
                ->keyBy('student_id');

            foreach ($students as $student) {
                $grade = $grades->get($student->id);
                $rows->push([
                    'student' => $student,
                    'passed' => $grade ? (bool) $grade->passed : false,
                    'diploma_delivered' => (bool) ($student->pivot->diploma_delivered ?? false),
                ]);
            }
        }

        return view('admin.subject-enrollment-outcomes', [
            'periods' => $periods,
            'period' => $period,
            'subjects' => $subjects,
            'subject' => $subject,
            'rows' => $rows,
        ]);
    }

    public function update(Request $request)
    {
        if (! Auth::check() || ! Auth::user()->is_admin) {
            abort(403);
        }

        $validated = $request->validate([
            'period_id' => 'required|exists:periods,id',
            'subject_id' => 'required|exists:subjects,id',
            'student_id' => 'required|exists:users,id',
            'passed' => 'nullable|in:0,1',
            'diploma_delivered' => 'nullable|in:0,1',
        ]);

        $period = Period::findOrFail($validated['period_id']);
        $subject = Subject::findOrFail($validated['subject_id']);

        $enrolled = DB::table('subject_student')
            ->where('subject_id', $subject->id)
            ->where('student_id', $validated['student_id'])
            ->where('period_id', $period->id)
            ->exists();

        if (! $enrolled) {
            return redirect()
                ->route('admin.subject-enrollment-outcomes', [
                    'period_id' => $period->id,
                    'subject_id' => $subject->id,
                ])
                ->with('error', 'El estudiante no está inscrito en esta materia para el periodo seleccionado.');
        }

        $passed = $request->boolean('passed');
        $diplomaDelivered = $request->boolean('diploma_delivered');

        Grade::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'subject_id' => $subject->id,
                'year' => $period->year,
                'trimester' => $period->trimester,
            ],
            ['passed' => $passed]
        );

        DB::table('subject_student')
            ->where('subject_id', $subject->id)
            ->where('student_id', $validated['student_id'])
            ->where('period_id', $period->id)
            ->update([
                'diploma_delivered' => $diplomaDelivered,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.subject-enrollment-outcomes', [
                'period_id' => $period->id,
                'subject_id' => $subject->id,
            ])
            ->with('success', 'Cambios guardados para el estudiante.');
    }
}
