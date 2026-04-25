@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Volver a inicio</a>
    </div>

    <h1>🎥 Centro de ayuda en video</h1>
    <p style="margin-bottom: 1rem; color: #555;">Aqui puedes ver guias practicas para usar la plataforma.</p>

    <section class="help-video-section">
        <h2>Videos generales</h2>
        <div class="help-video-grid">
            @foreach($commonVideos as $video)
                <article class="help-video-card">
                    <h3>{{ $video['title'] }}</h3>
                    <p>{{ $video['description'] }}</p>
                    <div class="help-video-frame">
                        <iframe
                            src="{{ $video['url'] }}"
                            title="{{ $video['title'] }}"
                            loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen>
                        </iframe>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="help-video-section">
        <h2>Videos para {{ $roleLabel }}</h2>

        @if(count($roleVideos) === 0)
            <p class="help-video-empty">No hay videos asignados para este rol todavia.</p>
        @else
            <div class="help-video-grid">
                @foreach($roleVideos as $video)
                    <article class="help-video-card">
                        <h3>{{ $video['title'] }}</h3>
                        <p>{{ $video['description'] }}</p>
                        <div class="help-video-frame">
                            <iframe
                                src="{{ $video['url'] }}"
                                title="{{ $video['title'] }}"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>

<style>
    .help-video-section {
        margin: 1.5rem 0 2rem;
    }

    .help-video-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }

    .help-video-card {
        background: #fff;
        border: 1px solid #e6e6e6;
        border-radius: 10px;
        padding: 1rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    }

    .help-video-card h3 {
        margin: 0 0 0.4rem;
        font-size: 1rem;
    }

    .help-video-card p {
        margin: 0 0 0.8rem;
        color: #555;
        font-size: 0.92rem;
    }

    .help-video-frame {
        position: relative;
        width: 100%;
        padding-top: 56.25%;
        border-radius: 8px;
        overflow: hidden;
        background: #000;
    }

    .help-video-frame iframe {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }

    .help-video-empty {
        color: #666;
        background: #f8f9fa;
        border: 1px dashed #ccc;
        border-radius: 8px;
        padding: 0.9rem 1rem;
    }
</style>
@endsection
