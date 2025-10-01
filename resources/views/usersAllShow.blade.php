@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Users List</h1>

        @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="error-message">
                {{ session('error') }}
            </div>
        @endif





        @auth
            @if(Auth::user()->is_admin)
                <div class="form-container">
                    <h2>Crear Nuevo Usuario</h2>
                    <form action="{{ route('users.create') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input type="text" name="name" id="name" class="form-input @error('name') form-input-error @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-input @error('email') form-input-error @enderror" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-input @error('password') form-input-error @enderror" required>
                            @error('password')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-checkbox-label">
                                <input type="checkbox" name="is_admin" value="1">
                                Es Administrador
                                <input type="checkbox" name="is_profesor" value="1">
                                Es Profesor

                            </label>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="form-button">Crear Usuario</button>
                        </div>
                    </form>
                </div>
            @endif
        @endauth

        

        <div class="users-grid">
            <!-- Administradores -->
            <div class="user-category">
                <h2 class="category-title admin-title">👑 Administradores</h2>
                <div class="users-grid-category">
                    @foreach($allUser->where('is_admin', true) as $user)
                        <div class="user-card admin-card">
                            <div class="user-card-header">
                                @if($user->image)
                                    <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}'s profile picture" class="profile-image">
                                @else
                                    <div class="profile-image-placeholder admin-placeholder">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="user-info">
                                    <h3>{{ $user->name }}</h3>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                    @if($user->verjaardag)
                                        <p><strong>Fecha de nacimiento:</strong> {{ \Carbon\Carbon::parse($user->verjaardag)->format('d/m/Y') }}</p>
                                    @endif
                                    <p><strong>Tipo:</strong> <span class="user-type admin-type">Administrador</span></p>
                                </div>
                            </div>
                            
                            @auth
                                @if(Auth::check() && Auth::user()->is_admin)
                                    <form action="{{ route('users.edit', $user->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PUT')
                                        <select name="role" class="form-select" onchange="this.form.submit()">
                                            <option value="">Cambiar rol...</option>
                                            <option value="admin" {{ $user->is_admin ? 'selected' : '' }}>Administrador</option>
                                            <option value="profesor" {{ $user->is_profesor ? 'selected' : '' }}>Profesor</option>
                                            <option value="user" {{ !$user->is_admin && !$user->is_profesor ? 'selected' : '' }}>Usuario</option>
                                        </select>
                                    </form>

                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="form-button delete-button" onclick="return confirm('¿Estás seguro de que quieres eliminar este usuario?')">ELIMINAR</button>
                                    </form>
                                @endif
                            @endauth
                            
                            <a href="{{ route('profile.public', $user) }}" class="btn btn-info">View Profile</a>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Profesores -->
            <div class="user-category">
                <h2 class="category-title profesor-title">🎓 Profesores</h2>
                <div class="users-grid-category">
                    @foreach($allUser->where('is_profesor', true)->where('is_admin', false) as $user)
                        <div class="user-card profesor-card">
                            <div class="user-card-header">
                                @if($user->image)
                                    <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}'s profile picture" class="profile-image">
                                @else
                                    <div class="profile-image-placeholder profesor-placeholder">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="user-info">
                                    <h3>{{ $user->name }}</h3>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                    @if($user->verjaardag)
                                        <p><strong>Fecha de nacimiento:</strong> {{ \Carbon\Carbon::parse($user->verjaardag)->format('d/m/Y') }}</p>
                                    @endif
                                    <p><strong>Tipo:</strong> <span class="user-type profesor-type">Profesor</span></p>
                                </div>
                            </div>
                            
                            @auth
                                @if(Auth::check() && Auth::user()->is_admin)
                                    <form action="{{ route('users.edit', $user->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PUT')
                                        <select name="role" class="form-select" onchange="this.form.submit()">
                                            <option value="">Cambiar rol...</option>
                                            <option value="admin" {{ $user->is_admin ? 'selected' : '' }}>Administrador</option>
                                            <option value="profesor" {{ $user->is_profesor ? 'selected' : '' }}>Profesor</option>
                                            <option value="user" {{ !$user->is_admin && !$user->is_profesor ? 'selected' : '' }}>Usuario</option>
                                        </select>
                                    </form>

                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="form-button delete-button" onclick="return confirm('¿Estás seguro de que quieres eliminar este usuario?')">ELIMINAR</button>
                                    </form>
                                @endif
                            @endauth
                            
                            <a href="{{ route('profile.public', $user) }}" class="btn btn-info">View Profile</a>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Usuarios Normales -->
            <div class="user-category">
                <h2 class="category-title user-title">👤 Usuarios</h2>
                <div class="users-grid-category">
                    @foreach($allUser->where('is_admin', false)->where('is_profesor', false) as $user)
                        <div class="user-card user-card-normal">
                            <div class="user-card-header">
                                @if($user->image)
                                    <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}'s profile picture" class="profile-image">
                                @else
                                    <div class="profile-image-placeholder user-placeholder">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="user-info">
                                    <h3>{{ $user->name }}</h3>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                    @if($user->verjaardag)
                                        <p><strong>Fecha de nacimiento:</strong> {{ \Carbon\Carbon::parse($user->verjaardag)->format('d/m/Y') }}</p>
                                    @endif
                                    <p><strong>Tipo:</strong> <span class="user-type user-type-normal">Usuario</span></p>
                                </div>
                            </div>
                            
                            @auth
                                @if(Auth::check() && Auth::user()->is_admin)
                                    <form action="{{ route('users.edit', $user->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PUT')
                                        <select name="role" class="form-select" onchange="this.form.submit()">
                                            <option value="">Cambiar rol...</option>
                                            <option value="admin" {{ $user->is_admin ? 'selected' : '' }}>Administrador</option>
                                            <option value="profesor" {{ $user->is_profesor ? 'selected' : '' }}>Profesor</option>
                                            <option value="user" {{ !$user->is_admin && !$user->is_profesor ? 'selected' : '' }}>Usuario</option>
                                        </select>
                                    </form>

                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="form-button delete-button" onclick="return confirm('¿Estás seguro de que quieres eliminar este usuario?')">ELIMINAR</button>
                                    </form>
                                @endif
                            @endauth
                            
                            <a href="{{ route('profile.public', $user) }}" class="btn btn-info">View Profile</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <style>
            .users-grid {
                display: flex;
                flex-direction: column;
                gap: 2rem;
                padding: 1.5rem 0;
            }

            .user-category {
                margin-bottom: 2rem;
            }

            .category-title {
                font-size: 1.5rem;
                font-weight: bold;
                margin-bottom: 1rem;
                padding: 0.5rem 1rem;
                border-radius: 8px;
                color: white;
            }

            .admin-title {
                background: linear-gradient(135deg, #dc3545, #c82333);
            }

            .profesor-title {
                background: linear-gradient(135deg, #28a745, #20c997);
            }

            .user-title {
                background: linear-gradient(135deg, #6c757d, #495057);
            }

            .users-grid-category {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 1.5rem;
            }

            .user-card {
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                padding: 1.5rem;
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .user-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            }

            .admin-card {
                border-left: 4px solid #dc3545;
            }

            .profesor-card {
                border-left: 4px solid #28a745;
            }

            .user-card-normal {
                border-left: 4px solid #6c757d;
            }

            .user-card-header {
                display: flex;
                align-items: center;
                margin-bottom: 1rem;
            }

            .profile-image {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                object-fit: cover;
                margin-right: 1rem;
            }

            .profile-image-placeholder {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 1.5rem;
                color: white;
                margin-right: 1rem;
            }

            .admin-placeholder {
                background: linear-gradient(135deg, #dc3545, #c82333);
            }

            .profesor-placeholder {
                background: linear-gradient(135deg, #28a745, #20c997);
            }

            .user-placeholder {
                background: linear-gradient(135deg, #6c757d, #495057);
            }

            .user-info {
                flex: 1;
            }

            .user-info h3 {
                margin: 0 0 0.5rem 0;
                color: #333;
            }

            .user-info p {
                margin: 0.25rem 0;
                font-size: 0.9rem;
                color: #666;
            }

            .user-type {
                padding: 0.25rem 0.5rem;
                border-radius: 4px;
                font-size: 0.8rem;
                font-weight: bold;
                color: white;
            }

            .admin-type {
                background: #dc3545;
            }

            .profesor-type {
                background: #28a745;
            }

            .user-type-normal {
                background: #6c757d;
            }

            .btn {
                display: inline-block;
                padding: 0.5rem 1rem;
                border-radius: 4px;
                text-decoration: none;
                margin-bottom: 0.5rem;
                transition: background-color 0.2s;
            }

            .btn-info {
                background: #17a2b8;
                color: white;
            }

            .btn-info:hover {
                background: #138496;
            }

            .form-button {
                padding: 0.5rem 1rem;
                border-radius: 4px;
                border: none;
                cursor: pointer;
                margin: 0.25rem;
                transition: background-color 0.2s;
            }

            .edit-button {
                background: #ffc107;
                color: #000;
            }

            .delete-button {
                background: #dc3545;
                color: white;
            }

            .delete-button:hover {
                background: #c82333;
            }

            .form-select {
                padding: 0.5rem;
                border: 1px solid #ddd;
                border-radius: 4px;
                margin: 0.5rem 0;
                width: 100%;
            }
        </style>
    </div>
@endsection


