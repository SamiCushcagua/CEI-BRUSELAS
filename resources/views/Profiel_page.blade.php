@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                @if($user->image)
                    @php
                        $imagePath = 'storage/' . $user->image;
                        $fullPath = public_path($imagePath);
                    @endphp
                    
                    @if(file_exists($fullPath))
                        <img src="{{ asset($imagePath) }}" alt="{{ $user->name }}'s profile picture" class="profile-image">
                    @else
                        <div class="debug-info">
                            <p>Image file not found at: {{ $fullPath }}</p>
                            <p>DB Image path: {{ $user->image }}</p>
                        </div>
                        <div class="profile-image-placeholder">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                @else
                    <div class="profile-image-placeholder">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <h1>{{ $user->name }}'s Profile</h1>
            </div>

            <div class="profile-info">
                <div class="info-item">
                    <label>Name:</label>
                    <span>{{ $user->name }}</span>
                </div>

                <div class="info-item">
                    <label>Email:</label>
                    <span>{{ $user->email }}</span>
                </div>

                <div class="info-item">
                    <label>Pseudo name:</label>
                    <span>{{ $user->UsernameDummy ?? 'Not specified' }}</span>
                </div>

                <div class="info-item">
                    <label>Birthday:</label>
                    <span>
                        @if($user->verjaardag)
                            {{ \Carbon\Carbon::parse($user->verjaardag)->format('d/m/Y') }}
                        @else
                            Not specified
                        @endif
                    </span>
                </div>

                <div class="info-item">
                    <label>About Me:</label>
                    <p>{{ $user->overMij ?? 'Not specified' }}</p>
                </div>

                <div class="info-item">
                    <label>Profile Photo:</label>
                    <div class="photo-display">
                        @if($user->image)
                            <img src="{{ asset('storage/' . $user->image) }}" alt="Profile photo" class="profile-photo">
                        @else
                            <span class="no-photo">No profile photo uploaded</span>
                        @endif
                    </div>
                </div>

                <div class="info-item">
                    <label>User Type:</label>
                    <span>
                        @if($user->is_admin)
                            <span class="user-type admin-type">👑 Administrador</span>
                        @elseif($user->is_profesor)
                            <span class="user-type profesor-type">🎓 Profesor</span>
                        @else
                            <span class="user-type user-type-normal">👤 Usuario</span>
                        @endif
                    </span>
                </div>
            </div>

            <div class="profile-actions">
                @auth
                    @if(Auth::user()->id === $user->id)
                        <a href="{{ route('profile.edit') }}" class="edit-button">Edit Profile</a>
                    @elseif(Auth::user()->is_admin)
                        <a href="{{ route('profile.edit') }}?user_id={{ $user->id }}" class="edit-button admin-edit">Edit User Profile</a>
                    @endif
                @endauth
                <a href="{{ route('usersAllShow') }}" class="back-button">← Volver a Usuarios</a>
            </div>
        </div>
    </div>

    <style>
    .profile-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 2rem;
        margin: 2rem auto;
        max-width: 800px;
    }

    .profile-header {
        display: flex;
        align-items: center;
        gap: 2rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }

    .profile-image {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .profile-image-placeholder {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background-color: #4f46e5;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .profile-header h1 {
        margin: 0;
        color: #1f2937;
        font-size: 2rem;
    }

    .profile-info {
        display: grid;
        gap: 1.5rem;
    }

    .info-item {
        padding: 1rem;
        background: #f9fafb;
        border-radius: 8px;
    }

    .info-item label {
        display: block;
        font-weight: bold;
        color: #4b5563;
        margin-bottom: 0.5rem;
    }

    .info-item span,
    .info-item p {
        color: #1f2937;
        line-height: 1.5;
    }

    .profile-actions {
        margin-top: 2rem;
        display: flex;
        justify-content: flex-end;
    }

    .edit-button {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background-color: #4f46e5;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: background-color 0.2s;
    }

    .edit-button:hover {
        background-color: #4338ca;
    }

    .admin-edit {
        background-color: #dc3545;
    }

    .admin-edit:hover {
        background-color: #c82333;
    }

    .back-button {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background-color: #6c757d;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: background-color 0.2s;
        margin-left: 1rem;
    }

    .back-button:hover {
        background-color: #545b62;
    }

    .user-type {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: bold;
        font-size: 0.9rem;
        color: white;
        display: inline-block;
    }

    .admin-type {
        background: linear-gradient(135deg, #dc3545, #c82333);
    }

    .profesor-type {
        background: linear-gradient(135deg, #28a745, #20c997);
    }

    .user-type-normal {
        background: linear-gradient(135deg, #6c757d, #495057);
    }

    .photo-display {
        margin-top: 0.5rem;
    }

    .profile-photo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e9ecef;
    }

    .no-photo {
        color: #6c757d;
        font-style: italic;
    }

    @media (max-width: 640px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .profile-actions {
            justify-content: center;
        }
    }

    .debug-info {
        background: #fff3cd;
        border: 1px solid #ffeeba;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        color: #856404;
    }

    .debug-info p {
        margin: 0.25rem 0;
    }
    </style>
@endsection
