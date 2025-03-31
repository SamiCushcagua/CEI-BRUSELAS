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
            @foreach($products as $product)
            <div class="product-card">
                @if($product->image)
                    <div class="product-image">
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                    </div>
                @endif
                <div class="product-info">
                    <h2 class="product-title">{{ $product->title }}</h2>
                    <p><strong>Name:</strong> {{ $product->name }}</p>
                    <p><strong>Price:</strong> €{{ number_format($product->prijs, 2) }}</p>
                    @if($product->description)
                        <p><strong>Description:</strong> {{ $product->description }}</p>
                    @endif
                    @if($product->created_date)
                        <p><strong>Created Date:</strong> {{ $product->created_date }}</p>
                    @endif

                    @auth
                        <form action="{{ route('cart.add', $product) }}" method="POST" style="margin-top: 10px;">
                            @csrf
                            <button type="submit" class="add-to-cart-button">Agregar al carrito</button>
                        </form>

                        @if(Auth::check() && Auth::user()->is_admin)
                            <div class="button-container">
                                <form action="{{ route('products.edit.form', $product->id) }}" method="GET" style="width: 50%;">
                                    @csrf
                                    <button type="submit" class="edit-button">
                                        Edit
                                    </button>
                                </form>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="width: 50%;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this product?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection