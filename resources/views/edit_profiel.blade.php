@extends('layouts.app')

@section('content')
<div class="container">
    <div class="edit-profile-card">
        @php
            $isAdminEditingOtherUser = Auth::check() && Auth::user()->is_admin && Auth::user()->id !== $user->id;
        @endphp
        
        <h1>
            @if($isAdminEditingOtherUser)
                Edit User Profile: {{ $user->name }}
            @else
                Edit Profile: {{ $user->name }}
            @endif
        </h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('patch')
            
            @php
                $isAdminEditingOtherUser = Auth::check() && Auth::user()->is_admin && Auth::user()->id !== $user->id;
            @endphp
            
            @if($isAdminEditingOtherUser)
                <input type="hidden" name="user_id" value="{{ $user->id }}">
            @endif

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       class="form-input @error('name') form-input-error @enderror" 
                       value="{{ old('name', $user->name) }}">
                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       class="form-input @error('email') form-input-error @enderror" 
                       value="{{ old('email', $user->email) }}">
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="UsernameDummy">Pseudo name</label>
                <input type="text" 
                       name="UsernameDummy" 
                       id="UsernameDummy" 
                       class="form-input @error('UsernameDummy') form-input-error @enderror" 
                       value="{{ old('UsernameDummy', $user->UsernameDummy) }}">
                @error('UsernameDummy')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="verjaardag">Birthday</label>
                <input type="date" 
                       name="verjaardag" 
                       id="verjaardag" 
                       class="form-input @error('verjaardag') form-input-error @enderror" 
                       value="{{ old('verjaardag', $user->verjaardag) }}">
                @error('verjaardag')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="overMij">About Me</label>
                <textarea name="overMij" 
                          id="overMij" 
                          class="form-input @error('overMij') form-input-error @enderror">{{ old('overMij', $user->overMij) }}</textarea>
                @error('overMij')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="image" class="form-label">Profile Photo</label>
                <input type="file" 
                       name="image" 
                       id="image" 
                       class="form-input @error('image') form-input-error @enderror" 
                       accept="image/*">
                @if($user->image)
                    <div class="current-image">
                        <img src="{{ asset('storage/' . $user->image) }}" alt="Current profile photo" class="preview-image">
                        <p>Current photo</p>
                    </div>
                @endif
                <small class="form-help">Upload a profile photo (JPG, PNG, GIF, max 2MB)</small>
                @error('image')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    @if($isAdminEditingOtherUser)
                        Update User Profile
                    @else
                        Update Profile
                    @endif
                </button>
                <a href="{{ route('profile.public', $user) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.edit-profile-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 2rem;
    margin: 2rem auto;
    max-width: 600px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-top: 0.5rem;
}

.form-input-error {
    border-color: #dc3545;
}

.error-message {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
}

.btn-primary {
    background: #0d6efd;
    color: white;
    border: none;
}

.btn-secondary {
    background: #6c757d;
    color: white;
    border: none;
    margin-left: 0.5rem;
}

.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.current-image {
    margin-top: 1rem;
    text-align: center;
}

.preview-image {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #ddd;
}

.form-input[type="file"] {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 100%;
}

.form-help {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #666;
}
</style>
@endsection
