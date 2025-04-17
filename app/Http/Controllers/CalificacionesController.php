<?php

namespace App\Http\Controllers;
use App\Models\modelCalificaciones; //importamos el modelo para q sepa en q modelo estas trabajando en este caso es el modelo de calificaciones
use Illuminate\Http\Request;

class CalificacionesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('users.calificaciones');
        
    }

    /**
     * Show the form for creating a new resource. la pagina blade.php donde se ve el formulario create
     */
    public function create()
    {
        

        return view('users.create-calificacion');





    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)  //Request es una clase de Laravel que maneja los datos enviados desde el formulario y $request es el objeto que contiene todos los datos enviados
    {

        /*¡Exactamente! Te lo explico en detalle:
Cuando tienes store(Request $request):
Request es una clase de Laravel que:
Captura todos los datos enviados del formulario
Captura datos de la URL
Captura archivos subidos
Captura headers HTTP
Captura cookies*/

 // 1. Primero valida los datos
        $request->validate([
            'Examen1' => 'required',
            'Examen2' => 'required',
            'participacion' => 'required',
            'puntualidad' => 'required',
            'Material' => 'required',
            'VersiculoBiblico' => 'required',
            'tarea' => 'required',
            'total' => 'required',
        ]);
        
        
        
    // 2. Crea el registro en la base de datos
        modelCalificaciones::create($request->all());

//3. Redirige con un mensaje de éxito
return redirect()->route('calificaciones.index')->with('success', 'Calificación creada exitosamente');




/*option 2 Calificacion::create([
        'Examen1' => $request->Examen1,
        'Examen2' => $request->Examen2,
        'participacion' => $request->participacion,
        'puntualidad' => $request->puntualidad,
        'Material' => $request->Material,
        'VersiculoBiblico' => $request->VersiculoBiblico,
        'tarea' => $request->tarea,
        'total' => $request->total,
    ]);*
/*Sí, Calificacion::create($request->all()); es equivalente, pero con algunas consideraciones importantes:
Forma larga (más explícita):
Forma corta (usando all()):
IMPORTANTE:
La forma corta solo funcionará si:
Los nombres en el formulario (name="") coinciden EXACTAMENTE con los campos de la base de datos
Todos los campos están en el $fillable del modelo
No hay campos adicionales en el formulario que no deban guardarse
Ventajas y Desventajas:
Forma larga:
Forma larga:
✅ Más clara y legible
✅ Control exacto de qué campos se guardan
✅ Más fácil de depurar
❌ Más código para escribir
Forma corta:
✅ Menos código
✅ Más rápido de escribir
❌ Menos control explícito
❌ Potencialmente menos seguro si no se configura bien
La recomendación es:
Usa la forma larga cuando estés aprendiendo o necesites claridad
Usa la forma corta cuando estés seguro de tu configuración y necesites código más conciso*/


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
