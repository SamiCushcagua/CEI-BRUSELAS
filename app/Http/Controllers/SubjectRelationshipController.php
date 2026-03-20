<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubjectRelationshipController extends Controller
{
    // Asignar profesor a una materia (por periodo activo)
    public function assignProfessor(Request $request, Subject $subject)
    {
        $request->validate([
            'professor1' => 'required|exists:users,id',
            'professor2' => 'nullable|exists:users,id'
        ]);

        $period = Period::active()->firstOrFail();

        // Reemplazo por periodo: eliminar cualquier asignación previa para este subject+period
        // para que el cambio de maestro se refleje correctamente y no se acumulen filas.
        DB::table('subject_professor')
            ->where('subject_id', $subject->id)
            ->where('period_id', $period->id)
            ->delete();

        // Asignar primer profesor
        $professor1 = User::findOrFail($request->professor1);
        
        if (!$professor1->is_profesor) {
            return back()->with('error', 'El primer usuario seleccionado no es un profesor.');
        }

        DB::table('subject_professor')->updateOrInsert(
            [
                'subject_id'   => $subject->id,
                'professor_id' => $professor1->id,
                'period_id'    => $period->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Asignar segundo profesor si se proporciona
        if ($request->professor2) {
            $professor2 = User::findOrFail($request->professor2);
            
            if (!$professor2->is_profesor) {
                return back()->with('error', 'El segundo usuario seleccionado no es un profesor.');
            }

            // Evitar asignar el mismo profesor como profesor1 y profesor2
            if ($professor2->id === $professor1->id) {
                return back()->with('error', 'El segundo profesor no puede ser el mismo que el primero.');
            }

            DB::table('subject_professor')->updateOrInsert(
                [
                    'subject_id'   => $subject->id,
                    'professor_id' => $professor2->id,
                    'period_id'    => $period->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        return back()->with('success', 'Profesores asignados exitosamente.');
    }
    // Remover profesor de una materia (solo del periodo activo)
    public function removeProfessor(Subject $subject, User $professor)
    {
        $period = Period::active()->firstOrFail();

        DB::table('subject_professor')
            ->where('subject_id', $subject->id)
            ->where('professor_id', $professor->id)
            ->where('period_id', $period->id)
            ->delete();

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

        $period = Period::active()->firstOrFail();

        // Insertar o mantener la inscripción solo para el periodo actual
        DB::table('subject_student')->updateOrInsert(
            [
                'subject_id' => $subject->id,
                'student_id' => $student->id,
                'period_id'  => $period->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'Estudiante inscrito exitosamente.');
    }

    // Remover estudiante de una materia
    public function removeStudent(Subject $subject, User $student)
    {
        $period = Period::active()->firstOrFail();

        DB::table('subject_student')
            ->where('subject_id', $subject->id)
            ->where('student_id', $student->id)
            ->where('period_id', $period->id)
            ->delete();

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
        // Importante: los profesores deben listarse SOLO para el periodo activo.
        // Evitamos usar relaciones "globales" que mezclan asignaciones de trimestres distintos.
        $period = Period::active()->firstOrFail();

        $professorIds = DB::table('subject_student as ss')
            ->join('subject_professor as sp', 'sp.subject_id', '=', 'ss.subject_id')
            ->where('ss.student_id', $student->id)
            ->where('ss.period_id', $period->id)
            ->where('sp.period_id', $period->id)
            ->select('sp.professor_id')
            ->distinct()
            ->pluck('sp.professor_id');

        $professors = $professorIds->isNotEmpty()
            ? User::whereIn('id', $professorIds)->orderBy('name')->get()
            : collect();

        return view('users.student-professors', compact('professors', 'student'));
    }
} 