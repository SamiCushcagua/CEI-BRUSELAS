@extends('layouts.app')

@section('content')

<h1>Crear Calificación</h1>

<form action="{{ route('calificaciones.store') }}" method="post">
@csrf

<label for="Examen1">Examen 1</label>
<input type="number" name="Examen1" id="Examen1">
<label for="Examen2">Examen 2</label>
<input type="number" name="Examen2" id="Examen2"> //¡Exactamente! El name="" debe coincidir con el nombre de la columna en tu base de datos para que funcione correctamente.
<label for="participacion">Participación</label>
<input type="number" name="participacion" id="participacion">
<label for="puntualidad">Puntualidad</label>
<input type="number" name="puntualidad" id="puntualidad">
<label for="Material">Material</label>
<input type="number" name="Material" id="Material">
<label for="VersiculoBiblico">Versiculo Biblico</label>
<input type="number" name="VersiculoBiblico" id="VersiculoBiblico">
<label for="tarea">Tarea</label>
<input type="number" name="tarea" id="tarea">
<label for="total">Total</label>
<input type="number" name="total" id="total">

<button type="submit">Crear Calificación</button>   

</form>

@endsection 
