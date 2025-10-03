@extends('layouts.app')

@section('content')


<div class="container">
    <div class="main-container">
        <div class="bible-breadcrumb">
            <a href="{{ route('bible.book', $chapter->book) }}" class="bible-back-link">
                ← Volver a {{ $chapter->book->name }}
            </a>
        </div>
        
        <h1 class="page-title">
            {{ $chapter->book->name }} {{ $chapter->chapter_number }}
        </h1>
        @if($chapter->title)
            <p class="bible-chapter-subtitle">{{ $chapter->title }}</p>
        @endif

        <!-- Navegación entre capítulos -->
        <div class="bible-chapter-navigation">
            @if($previousChapter)
                <a href="{{ route('bible.chapter', $previousChapter) }}" class="bible-nav-button bible-nav-prev">
                    ← {{ $previousChapter->book->name }} {{ $previousChapter->chapter_number }}
                </a>
            @endif
            
            <div class="bible-nav-center">
                <a href="{{ route('bible.book', $chapter->book) }}" class="bible-nav-button bible-nav-book">
                    {{ $chapter->book->name }}
                </a>
            </div>
            
            @if($nextChapter)
                <a href="{{ route('bible.chapter', $nextChapter) }}" class="bible-nav-button bible-nav-next">
                    {{ $nextChapter->book->name }} {{ $nextChapter->chapter_number }} →
                </a>
            @endif
        </div>

        <!-- Versículos -->
        <div class="bible-verses-container">
            @foreach($verses as $verse)
            <div class="bible-verse">
                <div class="bible-verse-content">
                    <span class="bible-verse-number">{{ $verse->verse_number }}</span>
                    <p class="bible-verse-text">{{ $verse->text }}</p>
                    <button onclick="addToFavorites({{ $verse->id }})" class="bible-favorite-btn">
                        ★
                    </button>
                </div>
            </div>
            @endforeach
        </div>


          <!-- Navegación entre capítulos -->
          <div class="bible-chapter-navigation">
            @if($previousChapter)
                <a href="{{ route('bible.chapter', $previousChapter) }}" class="bible-nav-button bible-nav-prev">
                    ← {{ $previousChapter->book->name }} {{ $previousChapter->chapter_number }}
                </a>
            @endif
            
            <div class="bible-nav-center">
                <a href="{{ route('bible.book', $chapter->book) }}" class="bible-nav-button bible-nav-book">
                    {{ $chapter->book->name }}
                </a>
            </div>
            
            @if($nextChapter)
                <a href="{{ route('bible.chapter', $nextChapter) }}" class="bible-nav-button bible-nav-next">
                    {{ $nextChapter->book->name }} {{ $nextChapter->chapter_number }} →
                </a>
            @endif
        </div>


        <!-- Botón para marcar como leído -->
        @if(!$isRead)
        <div class="bible-read-section">
            <button onclick="markChapterAsRead({{ $chapter->id }})" class="form-button bible-read-button">
                Marcar Capítulo como Leído
            </button>
        </div>
        @else
        <div class="bible-read-section">
            <div class="success-message bible-read-message">
                ✅ Capítulo leído
            </div>
            <button onclick="unmarkChapterAsRead({{ $chapter->id }})" class="form-button bible-unread-button">
                Desmarcar como Leído
            </button>
        </div>
        @endif
    </div>
</div>

<script>
function markChapterAsRead(chapterId) {
    fetch(`/bible/chapter/${chapterId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function unmarkChapterAsRead(chapterId) {
    fetch(`/bible/chapter/${chapterId}/mark-read`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function addToFavorites(verseId) {
    fetch(`/bible/verse/${verseId}/favorite`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Versículo agregado a favoritos');
        }
    });
}
</script>
@endsection