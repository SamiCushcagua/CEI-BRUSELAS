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
                    <h2>Create New User</h2>
                    <form action="{{ route('users.create') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Name</label>
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
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-input @error('password') form-input-error @enderror" required>
                            @error('password')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-checkbox-label">
                                <input type="checkbox" name="is_admin" value="1">
                                Es Administrator
                                <input type="checkbox" name="is_profesor" value="1">
                                Es Profesor

                            </label>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="form-button">Create User</button>
                        </div>
                    </form>
                </div>
            @endif
        @endauth

        

        <div class="users-grid">
            @foreach($allUser as $user)
                <div class="user-card">
                    <div class="user-card-header">
                        @if($user->image)
                            <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}'s profile picture" class="profile-image">
                        @else
                            <div class="profile-image-placeholder">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="user-info">
                            <h3>{{ $user->name }}</h3>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                        </div>
                    </div>
                    
                    @auth
                        @if(Auth::check() && Auth::user()->is_admin)
                            <p><strong>Tipo de usuario:</strong> 
                                @if($user->is_admin)
                                    Administrador
                                @elseif($user->is_profesor)
                                    Profesor
                                @else
                                    Usuario
                                @endif
                            </p>
                            
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
                                @method('DELETE')
                                <button type="submit" class="form-button delete-button">ELIMINAR</button>
                            </form>
                        @endif
                    @endauth
                    
                    <a href="{{ route('profile.public', $user) }}" class="btn btn-info">View Profile</a>
                </div>
            @endforeach
        </div>

        <style>
            .users-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 1.5rem;
                padding: 1.5rem 0;
            }

            .user-card {
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                padding: 1.5rem;
            }

            .btn {
                display: inline-block;
                padding: 0.5rem 1rem;
                border-radius: 4px;
                text-decoration: none;
                margin-bottom: 0.5rem;
            }

            .btn-info {
                background: #17a2b8;
                color: white;
            }

            .form-button {
                padding: 0.5rem 1rem;
                border-radius: 4px;
                border: none;
                cursor: pointer;
                margin: 0.25rem;
            }

            .edit-button {
                background: #ffc107;
                color: #000;
            }

            .delete-button {
                background: #dc3545;
                color: white;
            }

            .user-card-header {
                display: flex;
                align-items: center;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .profile-image {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                object-fit: cover;
            }

            .profile-image-placeholder {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                background-color: #4f46e5;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2rem;
                font-weight: bold;
            }

            .user-info {
                flex: 1;
            }

            .user-info h3 {
                margin: 0 0 0.5rem 0;
            }

            .user-info p {
                margin: 0;
            }
        </style>
    </div>
@endsection


