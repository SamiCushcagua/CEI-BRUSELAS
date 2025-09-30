<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    /**
     * Display a listing of the subjects.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        return view('subjects.create');
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(Request $request)
    {
        // Validar solo los datos del subject
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'Nivel' => 'required|numeric|min:1|max:20',
            'Archivo' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Manejar archivos e imágenes
        if ($request->hasFile('Archivo')) {
            $filePath = $request->file('Archivo')->store('archivos', 'public');
            $validated['Archivo'] = $filePath;
        }

        if ($request->hasFile('imagen')) {
            $imagePath = $request->file('imagen')->store('imagenes', 'public');
            $validated['imagen'] = $imagePath;
        }

        // Crear solo el subject
        $subject = Subject::create($validated);

        return redirect()->route('dashboard_cursos')
            ->with('success', 'Materia creada exitosamente.');
    }

    /**
     * Display the specified subject.
     */
    public function show(Subject $subject)
    {
        // Obtener todos los estudiantes que NO están inscritos en NINGUNA materia
        $availableStudents = User::where('is_profesor', false)
            ->where('is_admin', false)
            ->whereDoesntHave('subjectsAsStudent')
            ->get();
        
        // Obtener estudiantes ya inscritos en esta materia
        $enrolledStudents = $subject->students;
        
        return view('subjects.show_subject_student', compact('subject', 'availableStudents', 'enrolledStudents'));
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject)
    {
        return view('subjects.edit', compact('subject'));
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        // Validar los datos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'Nivel' => 'required|numeric|min:1|max:20',
            'Archivo' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Manejar archivos e imágenes
        if ($request->hasFile('Archivo')) {
            // Eliminar archivo anterior si existe
            if ($subject->Archivo) {
                Storage::disk('public')->delete($subject->Archivo);
            }
            $filePath = $request->file('Archivo')->store('archivos', 'public');
            $validated['Archivo'] = $filePath;
        }

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($subject->imagen) {
                Storage::disk('public')->delete($subject->imagen);
            }
            $imagePath = $request->file('imagen')->store('imagenes', 'public');
            $validated['imagen'] = $imagePath;
        }

        // Actualizar la materia
        $subject->update($validated);

        return redirect()->route('dashboard_cursos')
            ->with('success', 'Materia actualizada exitosamente.');
    }

    /**
     * Remove the specified subject from storage.
     */
    public function destroy(Subject $subject)
    {
        // Eliminar archivos físicos si existen
        if ($subject->Archivo) {
            Storage::disk('public')->delete($subject->Archivo);
        }
        
        if ($subject->imagen) {
            Storage::disk('public')->delete($subject->imagen);
        }
        
        // Eliminar relaciones con profesores (tabla subject_professor)
        $subject->professors()->detach();
        
        // Eliminar la materia
        $subject->delete();
        
        return redirect()->route('dashboard_cursos')
            ->with('success', 'Materia eliminada exitosamente.');
    }

    /**
     * Assign professors to a subject
     */
    public function assignProfessors(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'professor1' => 'required|integer|exists:users,id',
            'professor2' => 'nullable|integer|exists:users,id|different:professor1',
        ]);

        // Asignar profesores
        $professorsToAssign = [$request->professor1];
        if ($request->professor2) {
            $professorsToAssign[] = $request->professor2;
        }
        
        $subject->professors()->sync($professorsToAssign);

        return redirect()->route('dashboard_cursos')
            ->with('success', 'Profesores asignados exitosamente.');
    }

    /**
     * Inscribir estudiante en una materia
     */
    public function enrollStudent(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:users,id',
        ]);

        // Verificar que el usuario sea estudiante
        $student = User::find($validated['student_id']);
        if (!$student || $student->is_profesor || $student->is_admin) {
            return redirect()->route('subjects.show', $subject)
                ->with('error', 'El usuario seleccionado no es un estudiante.');
        }

        // Verificar que el estudiante no esté ya inscrito en otra materia
        $existingEnrollment = DB::table('subject_student')
            ->where('student_id', $validated['student_id'])
            ->first();

        if ($existingEnrollment) {
            return redirect()->route('subjects.show', $subject)
                ->with('error', 'Este estudiante ya está inscrito en otra materia.');
        }

        // Inscribir al estudiante
        $subject->students()->attach($validated['student_id']);

        return redirect()->route('subjects.show', $subject)
            ->with('success', 'Estudiante inscrito exitosamente.');
    }

    /**
     * Desinscribir estudiante de una materia
     */
    public function removeStudent(Subject $subject, User $student)
    {
        $subject->students()->detach($student->id);

        return redirect()->route('subjects.show', $subject)
            ->with('success', 'Estudiante eliminado exitosamente.');
    }
} 