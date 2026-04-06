<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfessorController extends Controller
{
    /**
     * Display a listing of the professors.
     */
    public function index()
    {
        $professors = User::where('is_profesor', true)->get();
        return view('professors.index', compact('professors'));
    }

    public function subjects(User $professor)
    {
        $subjects = $professor->subjects;
        return view('subjects.professor-subjects', compact('professor', 'subjects'));
    }

    public function students(User $professor)
    {
        $period = Period::active()->firstOrFail();

        $studentIds = DB::table('subject_student as ss')
            ->join('subject_professor as sp', function ($join) {
                $join->on('sp.subject_id', '=', 'ss.subject_id')
                    ->on('sp.period_id', '=', 'ss.period_id');
            })
            ->where('sp.professor_id', $professor->id)
            ->where('ss.period_id', $period->id)
            ->distinct()
            ->pluck('ss.student_id');

        $students = User::whereIn('id', $studentIds)
            ->where('is_profesor', false)
            ->where('is_admin', false)
            ->orderBy('name')
            ->get();

        $subjectCounts = collect();
        if ($studentIds->isNotEmpty()) {
            $subjectCounts = DB::table('subject_student as ss')
                ->join('subject_professor as sp', function ($join) {
                    $join->on('sp.subject_id', '=', 'ss.subject_id')
                        ->on('sp.period_id', '=', 'ss.period_id');
                })
                ->where('sp.professor_id', $professor->id)
                ->where('ss.period_id', $period->id)
                ->whereIn('ss.student_id', $studentIds)
                ->groupBy('ss.student_id')
                ->select('ss.student_id', DB::raw('COUNT(DISTINCT ss.subject_id) as cnt'))
                ->pluck('cnt', 'student_id');
        }

        $availableStudents = User::where('is_profesor', false)
            ->where('is_admin', false)
            ->whereNotIn('id', $studentIds)
            ->orderBy('name')
            ->get();

        return view('users.professor-students', compact(
            'professor',
            'students',
            'availableStudents',
            'period',
            'subjectCounts'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
