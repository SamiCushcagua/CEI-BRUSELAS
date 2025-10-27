<?php

namespace App\Http\Controllers;

use App\Models\GradeSetting;
use App\Models\Subject;
use App\Models\EvaluationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar configuración de calificaciones para una materia
     */
    public function index(Subject $subject)
    {
        // Verificar que el usuario es profesor de esta materia
        if (!$subject->professors()->where('professor_id', Auth::id())->exists()) {
            abort(403, 'No tienes permisos para configurar esta materia');
        }

        $gradeSettings = $subject->gradeSettings()->with('evaluationType')->get();
        $evaluationTypes = EvaluationType::active()->get();

        return view('grade-settings.index', compact('subject', 'gradeSettings', 'evaluationTypes'));
    }

    /**
     * Almacenar nueva configuración
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'evaluation_type_id' => 'required|exists:evaluation_types,id',
            'weight' => 'required|numeric|min:0|max:100',
            'max_score' => 'required|numeric|min:0|max:100',
            'passing_score' => 'required|numeric|min:0|max:100'
        ]);

        // Verificar que el usuario es profesor de esta materia
        $subject = Subject::findOrFail($request->subject_id);
        if (!$subject->professors()->where('professor_id', Auth::id())->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para configurar esta materia'
                ], 403);
            }
            abort(403, 'No tienes permisos para configurar esta materia');
        }

        try {
            $gradeSetting = GradeSetting::create($request->all());

            // SIEMPRE retornar JSON para peticiones AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuración guardada exitosamente',
                    'setting' => $gradeSetting
                ]);
            }

            return redirect()->back()->with('success', 'Configuración guardada exitosamente');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar la configuración: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al guardar la configuración');
        }
    }

    /**
     * Actualizar configuración existente
     */
    public function update(Request $request, GradeSetting $gradeSetting)
    {
        // Verificar que el usuario es profesor de esta materia
        if (!$gradeSetting->subject->professors()->where('professor_id', Auth::id())->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para modificar esta configuración'
                ], 403);
            }
            abort(403, 'No tienes permisos para modificar esta configuración');
        }

        $request->validate([
            'weight' => 'required|numeric|min:0|max:100',
            'max_score' => 'required|numeric|min:0|max:100',
            'passing_score' => 'required|numeric|min:0|max:100'
        ]);

        try {
            $gradeSetting->update($request->only(['weight', 'max_score', 'passing_score']));

            // SIEMPRE retornar JSON para peticiones AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuración actualizada exitosamente',
                    'setting' => $gradeSetting
                ]);
            }

            return redirect()->back()->with('success', 'Configuración actualizada exitosamente');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la configuración: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al actualizar la configuración');
        }
    }

    /**
     * Eliminar configuración
     */
    public function destroy(Request $request, GradeSetting $gradeSetting)
    {
        // Verificar que el usuario es profesor de esta materia
        if (!$gradeSetting->subject->professors()->where('professor_id', Auth::id())->exists()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para eliminar esta configuración'
                ], 403);
            }
            abort(403, 'No tienes permisos para eliminar esta configuración');
        }

        try {
            $gradeSetting->delete();

            // SIEMPRE retornar JSON para peticiones AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuración eliminada exitosamente'
                ]);
            }

            return redirect()->back()->with('success', 'Configuración eliminada exitosamente');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la configuración: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al eliminar la configuración');
        }
    }
}