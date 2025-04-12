<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
        $students = $professor->students;
        return view('users.professor-students', compact('professor', 'students'));
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
