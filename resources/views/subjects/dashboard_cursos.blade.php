@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="page-title">Dashboard de Cursos</h1>

    <div class="products-grid">
        @foreach($subjects as $subject)
            <div class="product-card">
                @if($subject->imagen)
                    <div class="product-image">
                        <img src="{{ asset('storage/imagenes/' . $subject->imagen) }}" 
                             alt="{{ $subject->name }}">
                    </div>
                @endif
                <div class="product-info">
                    <h2 class="product-title">{{ $subject->name }}</h2>
                    <p>{{ $subject->description }}</p>
                    <p><strong>Nivel:</strong> Curso {{ $subject->Nivel }}</p>
                    <p><strong>Profesor:</strong> {{ $subject->profesor_asignado }}</p>
                    @if($subject->Archivo)
                        <a href="{{ asset('storage/archivos/' . $subject->Archivo) }}" 
                           class="btn-primary" 
                           target="_blank">
                            Ver Material
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection


