@extends('layouts.app')

@section('content')
<div class="container">
    <div class="main-container">
        <h1 class="page-title">La Santa Biblia</h1>
        
        <!-- Antiguo Testamento -->
        <div class="bible-section">
            <h2 class="bible-section-title old-testament">Antiguo Testamento</h2>
            <div class="bible-books-grid">
                @foreach($oldTestament as $book)
                <a href="{{ route('bible.book', $book) }}" class="bible-book-card">
                    <h3 class="bible-book-name">{{ $book->name }}</h3>
                    <p class="bible-book-chapters">{{ $book->chapters_count }} capítulos</p>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Nuevo Testamento -->
        <div class="bible-section">
            <h2 class="bible-section-title new-testament">Nuevo Testamento</h2>
            <div class="bible-books-grid">
                @foreach($newTestament as $book)
                <a href="{{ route('bible.book', $book) }}" class="bible-book-card">
                    <h3 class="bible-book-name">{{ $book->name }}</h3>
                    <p class="bible-book-chapters">{{ $book->chapters_count }} capítulos</p>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Navegación adicional -->
        <div class="bible-navigation">
            <a href="{{ route('bible.favorites') }}" class="form-button bible-nav-button">
                Mis Versículos Favoritos
            </a>
            <a href="{{ route('bible.progress') }}" class="form-button bible-nav-button">
                Mi Progreso de Lectura
            </a>
        </div>
    </div>
</div>
@endsection