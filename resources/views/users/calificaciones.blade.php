@extends('layouts.app')
@section('content')

<div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
    </div>
    <h1>Calificaciones</h1>

<form action="{{ route('calificaciones.create') }}" method="get">
    <button type="submit">Crear Calificación</button>
</form>





@endsection


