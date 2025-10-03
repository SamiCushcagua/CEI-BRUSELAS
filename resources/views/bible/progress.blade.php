@extends('layouts.app')

@section('content')
<div class="container">
    <div class="main-container">
        <div class="bible-breadcrumb">
            <a href="{{ route('bible.index') }}" class="bible-back-link">← Volver a la Biblia</a>
        </div>
        
        <h1 class="page-title">Mi Progreso de Lectura</h1>

        <!-- Resumen general -->
        <div class="bible-progress-summary">
            <div class="bible-progress-stats">
                <div class="bible-stat-item">
                    <h3>{{ $readChapters }}</h3>
                    <p>Capítulos Leídos</p>
                </div>
                <div class="bible-stat-item">
                    <h3>{{ $totalChapters }}</h3>
                    <p>Total Capítulos</p>
                </div>
                <div class="bible-stat-item">
                    <h3>{{ number_format($progress, 1) }}%</h3>
                    <p>Progreso</p>
                </div>
            </div>
            <div class="bible-progress-bar">
                <div class="bible-progress-fill" style="width: {{ $progress }}%"></div>
            </div>
        </div>

        <!-- Lista de libros -->
        <div class="bible-books-progress">
            @foreach($books as $book)
            <div class="bible-book-progress">
                <div class="bible-book-progress-header">
                    <h3 class="bible-book-progress-name">{{ $book->name }}</h3>
                    <span class="bible-book-progress-count">
                        {{ $book->chapters->whereIn('id', $readChapterIds)->count() }}/{{ $book->chapters_count }}
                    </span>
                </div>
                <div class="bible-chapters-progress">
                    @foreach($book->chapters as $chapter)
                    <div class="bible-chapter-progress-item {{ in_array($chapter->id, $readChapterIds) ? 'read' : 'unread' }}">
                        <a href="{{ route('bible.chapter', $chapter) }}" class="bible-chapter-progress-link">
                            Cap. {{ $chapter->chapter_number }}
                        </a>
                        @if(in_array($chapter->id, $readChapterIds))
                            <span class="bible-chapter-read-date">
                                Leído
                            </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection