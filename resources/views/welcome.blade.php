@extends('layouts.app')

@section('content')
<style>
    .edit-button {
        background-color: #3b82f6;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-weight: bold;
        width: 50%;
    }
    .edit-button:hover {
        background-color: #2563eb;
    }
    .delete-button {
        background-color: #ef4444;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-weight: bold;
        width: 50%;
    }
    .delete-button:hover {
        background-color: #dc2626;
    }
    .button-container {
        margin-top: 16px;
        display: flex;
        width: 100%;
    }
</style>

<div class="container">
    <!-- Main Content -->
    <div class="main-container">
        <!-- Authentication Status -->
        @auth
            <div class="auth-message success">
                <p>Welcome, {{ auth()->user()->name }}</p>
            </div>
        @else
            <div class="auth-message warning">
                <p>Please log in to see all content.</p>
            </div>
        @endauth

        <!-- Products Section -->
        <h1 class="page-title">Products Information</h1>
        <div class="products-grid">


        
            
        </div>
    </div>
</div>
@endsection