<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        // Validar los datos del formulario
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'Nivel' => 'required|integer|min:1|max:20',
            'profesor_asignado' => 'required|string|max:255',
            'Archivo' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB máximo
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB máximo
        ]);

        // Manejar la subida del archivo
        if ($request->hasFile('Archivo')) {
            $archivo = $request->file('Archivo');
            $archivoNombre = time() . '_' . $archivo->getClientOriginalName();
            $archivo->storeAs('public/archivos', $archivoNombre);
            $validated['Archivo'] = $archivoNombre;
        }

        // Manejar la subida de la imagen
        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $imagenNombre = time() . '_' . $imagen->getClientOriginalName();
            $imagen->storeAs('public/imagenes', $imagenNombre);
            $validated['imagen'] = $imagenNombre;
        }

        // Crear la materia
        $subject = Subject::create($validated);

        // Redirigir con mensaje de éxito
        return redirect()->route('dashboard_cursos')
            ->with('success', 'Materia creada exitosamente.');
    }

    /**
     * Display the specified subject.
     */
    public function show(Subject $subject)
    {
        //
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject)
    {
        //
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        //
    }

    /**
     * Remove the specified subject from storage.
     */
    public function destroy(Subject $subject)
    {
        //
    }
} 