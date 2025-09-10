@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Materia: {{ $subject->name }}</h1>

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

    <form action="{{ route('subjects.update', $subject) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Nombre de la Materia</label>
            <input type="text" 
                   name="name" 
                   id="name" 
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $subject->name) }}"
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
                      required>{{ old('description', $subject->description) }}</textarea>
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
                   value="{{ old('Nivel', $subject->Nivel) }}"
                   min="1"
                   max="20"
                   required>
            @error('Nivel')
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
            @if($subject->Archivo)
                <small class="form-text text-muted">
                    Archivo actual: <a href="{{ asset('storage/' . $subject->Archivo) }}" target="_blank">Ver archivo</a>
                </small>
            @endif
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
            @if($subject->imagen)
                <div class="mt-2">
                    <img src="{{ asset('storage/' . $subject->imagen) }}" 
                         alt="{{ $subject->name }}" 
                         style="max-width: 200px; max-height: 200px;">
                    <small class="form-text text-muted">Imagen actual</small>
                </div>
            @endif
            @error('imagen')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group mt-3">
            <button type="submit" class="btn btn-primary">Actualizar Materia</button>
            <a href="{{ route('dashboard_cursos') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
