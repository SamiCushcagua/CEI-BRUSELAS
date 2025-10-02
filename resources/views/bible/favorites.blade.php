@extends('layouts.app')

@section('content')
<div class="container">
    <div class="main-container">
        <div class="bible-breadcrumb">
            <a href="{{ route('bible.index') }}" class="bible-back-link">← Volver a la Biblia</a>
        </div>
        
        <h1 class="page-title">Mis Versículos Favoritos</h1>

        @if($favorites->count() > 0)
            <div class="bible-favorites-container">
                @foreach($favorites as $favorite)
                <div class="bible-favorite-item">
                    <div class="bible-favorite-header">
                        <h3 class="bible-favorite-reference">
                            {{ $favorite->verse->chapter->book->name }} 
                            {{ $favorite->verse->chapter->chapter_number }}:{{ $favorite->verse->verse_number }}
                        </h3>
                        <button onclick="removeFromFavorites({{ $favorite->verse->id }})" class="bible-remove-favorite-btn">
                            ✕
                        </button>
                    </div>
                    <p class="bible-favorite-text">{{ $favorite->verse->text }}</p>
                    @if($favorite->note)
                        <p class="bible-favorite-note">{{ $favorite->note }}</p>
                    @endif
                    <p class="bible-favorite-date">Agregado: {{ $favorite->created_at->format('d/m/Y') }}</p>
                </div>
                @endforeach
            </div>
        @else
            <div class="bible-empty-state">
                <p>No tienes versículos favoritos aún.</p>
                <a href="{{ route('bible.index') }}" class="form-button">Explorar la Biblia</a>
            </div>
        @endif
    </div>
</div>

<script>
function removeFromFavorites(verseId) {
    if (confirm('¿Estás seguro de que quieres eliminar este versículo de tus favoritos?')) {
        fetch(`/bible/verse/${verseId}/favorite`, {
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
}
</script>
@endsection