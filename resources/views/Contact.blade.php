@extends('layouts.app')

@section('content')
<div class="container">


    <!-- Main Content -->
    <div class="main-container static-content">
        <h1>Contact Us</h1>
        <p>Welcome, {{ $name }}</p>
        
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
        
        <form action="{{ route('send-email') }}" method="POST"  class="form-container">
            @csrf
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="message" class="form-label">Message</label>
                <textarea name="message" id="message" class="form-textarea @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                @error('message')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="form-button">Send Message</button>
            </div>
        </form>
    </div>
</div>
@endsection