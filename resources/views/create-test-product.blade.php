@extends('layouts.app')

@section('content')
<div class="product-form-container">
    <div class="product-form-header">
        <h1>Create New Product</h1>
        <p class="subtitle">Fill in the details to create a new product</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    @auth
        <form action="{{ route('store-test-product') }}" method="POST" enctype="multipart/form-data" class="product-form">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="Enter product name"
                           required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" 
                           name="title" 
                           id="title" 
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}"
                           placeholder="Enter product title"
                           required>
                    @error('title')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="prijs">Price</label>
                    <div class="price-input-wrapper">
                        <span class="currency-symbol">€</span>
                        <input type="number" 
                               name="prijs" 
                               id="prijs" 
                               class="form-control @error('prijs') is-invalid @enderror"
                               value="{{ old('prijs') }}"
                               placeholder="0.00"
                               step="0.01"
                               required>
                    </div>
                    @error('prijs')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="created_date">Created Date</label>
                    <input type="date" 
                           name="created_date" 
                           id="created_date" 
                           class="form-control @error('created_date') is-invalid @enderror"
                           value="{{ old('created_date') }}"
                           required>
                    @error('created_date')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group full-width">
                <label for="description">Description</label>
                <textarea name="description" 
                          id="description" 
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="Enter product description"
                          rows="4"
                          required>{{ old('description') }}</textarea>
                @error('description')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label for="image">Product Image</label>
                <div class="file-upload-wrapper">
                    <input type="file" 
                           name="image" 
                           id="image" 
                           class="form-control @error('image') is-invalid @enderror"
                           accept="image/*">
                    <div class="file-upload-preview">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Drag and drop an image here or click to browse</p>
                    </div>
                </div>
                @error('image')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Create Product
                </button>
            </div>
        </form>
    @else
        <div class="auth-warning">
            <i class="fas fa-lock"></i>
            Please <a href="{{ route('login') }}">login</a> to create a product.
        </div>
    @endauth
</div>
@endsection 