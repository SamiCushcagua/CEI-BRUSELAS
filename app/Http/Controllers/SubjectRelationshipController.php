<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectRelationshipController extends Controller
{
    // Asignar profesor a una materia
   // Asignar profesor a una materia
public function assignProfessor(Request $request, Subject $subject)
{
    $request->validate([
        'professor1' => 'required|exists:users,id',
        'professor2' => 'nullable|exists:users,id'
    ]);

    // Asignar primer profesor
    $professor1 = User::findOrFail($request->professor1);
    
    if (!$professor1->is_profesor) {
        return back()->with('error', 'El primer usuario seleccionado no es un profesor.');
    }

    // Verificar si ya está asignado
    if (!$subject->professors()->where('professor_id', $professor1->id)->exists()) {
        $subject->professors()->attach($professor1->id);
    }

    // Asignar segundo profesor si se proporciona
    if ($request->professor2) {
        $professor2 = User::findOrFail($request->professor2);
        
        if (!$professor2->is_profesor) {
            return back()->with('error', 'El segundo usuario seleccionado no es un profesor.');
        }

        // Verificar si ya está asignado
        if (!$subject->professors()->where('professor_id', $professor2->id)->exists()) {
            $subject->professors()->attach($professor2->id);
        }
    }

    return back()->with('success', 'Profesores asignados exitosamente.');
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
        
        if ($student->is_profesor || $student->is_admin) {
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
        $students = $professor->students()->get();
        
        // Obtener estudiantes disponibles para asignar (que no están ya asignados)
        $assignedStudentIds = $students->pluck('id');
        $availableStudents = User::where('is_profesor', false)
            ->where('is_admin', false)
            ->whereNotIn('id', $assignedStudentIds)
            ->get();
        
        return view('users.professor-students', compact('students', 'professor', 'availableStudents'));
    }

    // Obtener profesores de un estudiante
    public function getStudentProfessors(User $student)
    {
        $professors = $student->professors()->get();
        return view('users.student-professors', compact('professors', 'student'));
    }
} 