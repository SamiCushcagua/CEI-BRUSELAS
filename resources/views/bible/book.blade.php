@extends('layouts.app')

@section('content')

<div class="container">
    <div class="main-container">
        <div class="bible-breadcrumb">
            <a href="{{ route('bible.index') }}" class="bible-back-link">← Volver a la Biblia</a>
        </div>
        
        <h1 class="page-title">{{ $book->name }}</h1>
        <p class="bible-book-info">{{ $book->chapters_count }} capítulos</p>

        <div class="bible-chapters-grid">
            @foreach($chapters as $chapter)
            <a href="{{ route('bible.chapter', $chapter) }}" 
               class="bible-chapter-card {{ in_array($chapter->id, $readChapterIds) ? 'bible-chapter-read' : '' }}">
                <h3 class="bible-chapter-title">Capítulo {{ $chapter->chapter_number }}</h3>
                @if($chapter->title)
                    <p class="bible-chapter-subtitle">{{ $chapter->title }}</p>
                @endif
                <p class="bible-chapter-verses">{{ $chapter->verses_count }} versículos</p>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endsection