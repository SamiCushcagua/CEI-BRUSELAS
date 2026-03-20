<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Listar todos los periodos
     */
    public function index()
    {
        $periods = Period::orderBy('year')->orderBy('trimester')->get();
        return view('periods.index', compact('periods'));
    }

    /**
     * Crear un nuevo periodo
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'year' => 'required|integer|min:2020|max:2030',
            'trimester' => 'required|integer|in:1,2,3',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        Period::create([
            'name' => $request->name,
            'year' => $request->year,
            'trimester' => $request->trimester,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => false,
            'is_locked' => false,
        ]);

        return redirect()->route('periods.index')->with('success', 'Periodo creado.');
    }

    /**
     * Marcar este periodo como vigente (el que ven profesores/alumnos)
     */
    public function setActive(Period $period)
    {
        Period::query()->update(['is_active' => false]);
        $period->update(['is_active' => true]);
        return redirect()->route('periods.index')->with('success', 'Periodo vigente actualizado.');
    }

    /**
     * Bloquear periodo (solo lectura, trimestre cerrado)
     */
    public function lock(Period $period)
    {
        $period->update(['is_locked' => true]);
        return redirect()->route('periods.index')->with('success', 'Periodo bloqueado.');
    }
}