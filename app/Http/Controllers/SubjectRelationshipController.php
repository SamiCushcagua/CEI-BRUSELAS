<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectRelationshipController extends Controller
{
    // Asignar profesor a una materia
    public function assignProfessor(Request $request, Subject $subject)
    {
        $request->validate([
            'professor_id' => 'required|exists:users,id'
        ]);

        $professor = User::findOrFail($request->professor_id);
        
        if ($professor->role !== 'professor') {
            return back()->with('error', 'El usuario seleccionado no es un profesor.');
        }

        $subject->professors()->attach($professor->id);

        return back()->with('success', 'Profesor asignado exitosamente.');
    }

    // Remover profesor de una materia
    public function removeProfessor(Subject $subject, User $professor)
    {
        $subject->professors()->detach($professor->id);
        return back()->with('success', 'Profesor removido exitosamente.');
    }

    // Inscribir estudiante a una materia
    public function enrollStudent(Request $request, Subject $subject)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id'
        ]);

        $student = User::findOrFail($request->student_id);
        
        if ($student->role !== 'student') {
            return back()->with('error', 'El usuario seleccionado no es un estudiante.');
        }

        $subject->students()->attach($student->id);

        return back()->with('success', 'Estudiante inscrito exitosamente.');
    }

    // Remover estudiante de una materia
    public function removeStudent(Subject $subject, User $student)
    {
        $subject->students()->detach($student->id);
        return back()->with('success', 'Estudiante removido exitosamente.');
    }

    // Asignar estudiante a un profesor
    public function assignStudentToProfessor(Request $request, User $professor)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id'
        ]);

        $student = User::findOrFail($request->student_id);
        
        if ($student->role !== 'student') {
            return back()->with('error', 'El usuario seleccionado no es un estudiante.');
        }

        $professor->students()->attach($student->id);

        return back()->with('success', 'Estudiante asignado al profesor exitosamente.');
    }

    // Remover estudiante de un profesor
    public function removeStudentFromProfessor(User $professor, User $student)
    {
        $professor->students()->detach($student->id);
        return back()->with('success', 'Estudiante removido del profesor exitosamente.');
    }

    // Obtener materias de un profesor
    public function getProfessorSubjects(User $professor)
    {
        $subjects = $professor->subjectsAsProfessor;
        return view('subjects.professor-subjects', compact('subjects', 'professor'));
    }

    // Obtener materias de un estudiante
    public function getStudentSubjects(User $student)
    {
        $subjects = $student->subjectsAsStudent;
        return view('subjects.student-subjects', compact('subjects', 'student'));
    }

    // Obtener estudiantes de un profesor
    public function getProfessorStudents(User $professor)
    {
        $students = $professor->students;
        return view('users.professor-students', compact('students', 'professor'));
    }

    // Obtener profesores de un estudiante
    public function getStudentProfessors(User $student)
    {
        $professors = $student->professors;
        return view('users.student-professors', compact('professors', 'student'));
    }
} 