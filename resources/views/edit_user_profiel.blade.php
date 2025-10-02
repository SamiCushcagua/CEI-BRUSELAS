@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="title">Edit User Profile</h1>

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

    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.edit', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Nueva Contraseña (dejar vacía para mantener la actual)</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>

                @if(Auth::user()->is_admin)
                    <div class="form-group">
                        <label class="form-check-label">Rol de Usuario</label>
                        <div class="form-check">
                            <input type="checkbox" name="is_admin" id="is_admin" class="form-check-input" 
                                   value="1" {{ $user->is_admin ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_admin">Administrador</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_profesor" id="is_profesor" class="form-check-input" 
                                   value="1" {{ $user->is_profesor ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_profesor">Profesor</label>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label for="image">Foto de Perfil</label>
                    <input type="file" name="image" id="image" class="form-control-file @error('image') is-invalid @enderror">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if($user->image)
                    <div class="form-group">
                        <div class="current-image">
                            <p>Foto actual:</p>
                            <img src="{{ asset('storage/' . $user->image) }}" alt="Current profile image" class="img-thumbnail" style="max-width: 200px">
                        </div>
                    </div>
                @endif

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Actualizar Perfil</button>
                    <a href="{{ route('usersAllShow') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 