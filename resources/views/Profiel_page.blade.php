@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                @if(Auth::user()->image)
                    @php
                        $imagePath = 'storage/' . Auth::user()->image;
                        $fullPath = public_path($imagePath);
                    @endphp
                    
                    @if(file_exists($fullPath))
                        <img src="{{ asset($imagePath) }}" alt="{{ Auth::user()->name }}'s profile picture" class="profile-image">
                    @else
                        <div class="debug-info">
                            <p>Image file not found at: {{ $fullPath }}</p>
                            <p>DB Image path: {{ Auth::user()->image }}</p>
                        </div>
                        <div class="profile-image-placeholder">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    @endif
                @else
                    <div class="profile-image-placeholder">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                @endif
                <h1>My Profile</h1>
            </div>

            <div class="profile-info">
                <div class="info-item">
                    <label>Name:</label>
                    <span>{{ Auth::user()->name }}</span>
                </div>

                <div class="info-item">
                    <label>Email:</label>
                    <span>{{ Auth::user()->email }}</span>
                </div>

                @if(Auth::user()->UsernameDummy)
                    <div class="info-item">
                        <label>Pseudo name:</label>
                        <span>{{ Auth::user()->UsernameDummy }}</span>
                    </div>
                @endif

                @if(Auth::user()->verjaardag)
                    <div class="info-item">
                        <label>Birthday:</label>
                        <span>{{ Auth::user()->verjaardag }}</span>
                    </div>
                @endif

                @if(Auth::user()->overMij)
                    <div class="info-item">
                        <label>About Me:</label>
                        <p>{{ Auth::user()->overMij }}</p>
                    </div>
                @endif
            </div>

            <div class="profile-actions">
                <a href="{{ route('profile.edit', Auth::user()) }}" class="edit-button">Edit Profile</a>
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
