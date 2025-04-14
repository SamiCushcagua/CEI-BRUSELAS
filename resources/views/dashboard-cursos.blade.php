@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="title">Dashboard de Cursos</h1>

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

    <div class="dashboard-grid">
        <div class="card">
            <h2>Materias</h2>
            <div class="card-content">
                <a href="{{ route('subjects.index') }}" class="btn btn-primary">Ver todas las materias</a>
                <a href="{{ route('subjects.create') }}" class="btn btn-success">Crear nueva materia</a>
            </div>
        </div>

        <div class="card">
            <h2>Profesores</h2>
            <div class="card-content">
                <a href="{{ route('professors.index') }}" class="btn btn-primary">Ver todos los profesores</a>
            </div>
        </div>

        <div class="card">
            <h2>Estudiantes</h2>
            <div class="card-content">
                <a href="{{ route('students.index') }}" class="btn btn-primary">Ver todos los estudiantes</a>
            </div>
        </div>

        @if(Auth::user()->is_admin)
            <div class="card">
                <h2>Administración</h2>
                <div class="card-content">
                    <a href="{{ route('usersAllShow') }}" class="btn btn-primary">Gestionar usuarios</a>
                    <a href="{{ route('FAQ') }}" class="btn btn-primary">Gestionar FAQ</a>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
}

.card h2 {
    margin-top: 0;
    color: #333;
}

.card-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn {
    padding: 10px 15px;
    border-radius: 4px;
    text-decoration: none;
    text-align: center;
    color: white;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #0d6efd;
}

.btn-success {
    background: #198754;
}

.btn:hover {
    opacity: 0.9;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>
@endsection 