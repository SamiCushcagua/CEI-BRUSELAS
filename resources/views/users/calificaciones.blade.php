@extends('layouts.app')
@section('content')
    <h1>Calificaciones</h1>

<form action="{{ route('calificaciones.create') }}" method="get">
    <button type="submit">Crear Calificación</button>
</form>





@endsection


