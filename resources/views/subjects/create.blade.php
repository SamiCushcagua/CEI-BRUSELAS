@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Nueva Materia</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('subjects.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="name">Nombre de la Materia</label>
            <input type="text" 
                   name="name" 
                   id="name" 
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}"
                   required>
            @error('name')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Descripción</label>
            <textarea name="description" 
                      id="description" 
                      class="form-control @error('description') is-invalid @enderror"
                      rows="4"
                      required>{{ old('description') }}</textarea>
            @error('description')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="Nivel">Nivel (Número del curso)</label>
            <input type="number" 
                   name="Nivel" 
                   id="Nivel" 
                   class="form-control @error('Nivel') is-invalid @enderror"
                   value="{{ old('Nivel') }}"
                   min="1"
                   max="20"
                   required>
            @error('Nivel')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="profesor_asignado">Profesor Asignado</label>
            <input type="text" 
                   name="profesor_asignado" 
                   id="profesor_asignado" 
                   class="form-control @error('profesor_asignado') is-invalid @enderror"
                   value="{{ old('profesor_asignado') }}"
                   required>
            @error('profesor_asignado')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="Archivo">Archivo (PDF, Word, etc.)</label>
            <input type="file" 
                   name="Archivo" 
                   id="Archivo" 
                   class="form-control @error('Archivo') is-invalid @enderror"
                   accept=".pdf,.doc,.docx">
            @error('Archivo')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="imagen">Imagen</label>
            <input type="file" 
                   name="imagen" 
                   id="imagen" 
                   class="form-control @error('imagen') is-invalid @enderror"
                   accept="image/*">
            @error('imagen')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group mt-3">
            <button type="submit" class="btn btn-primary">Crear Materia</button>
            <a href="{{ route('subjects.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection 