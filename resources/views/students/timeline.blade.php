@extends('layouts.app')

@section('content')
<div class="timeline-page">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
       </div>

    <header class="timeline-header">
        <h1 class="timeline-title">Mi recorrido</h1>
        <p class="timeline-subtitle">
            Así vas en el plan de cursos.
            @if($currentPeriod)
                Periodo actual: <strong>{{ $currentPeriod->name }}</strong>.
            @endif
        </p>
        <ul class="timeline-legend" aria-label="Leyenda">
            <li><span class="timeline-dot timeline-dot--past" aria-hidden="true"></span> Ya aprobado</li>
            <li><span class="timeline-dot timeline-dot--current" aria-hidden="true"></span> Curso actual</li>
            <li><span class="timeline-dot timeline-dot--future" aria-hidden="true"></span> Pendiente</li>
        </ul>
    </header>

    @if($nodes->isEmpty())
        <p class="timeline-empty">Aún no hay cursos registrados en el sistema.</p>
    @else
        <ol class="timeline-track" aria-label="Línea de cursos">
            @foreach($nodes as $node)
                <li class="timeline-step{{ $node['status'] === 'current' ? ' is-current' : '' }}"
                    @if($node['status'] === 'current') id="timeline-current" @endif>
                    <div class="timeline-step-main">
                        <button type="button"
                            class="timeline-node timeline-node--{{ $node['status'] }}"
                            data-timeline-toggle
                            aria-expanded="false"
                            aria-controls="timeline-desc-{{ $node['id'] }}"
                            aria-label="{{ $node['name'] }} — ver descripción">
                            <span class="timeline-node-circle" aria-hidden="true">
                                @if($node['nivel'] !== null && $node['nivel'] !== '')
                                    {{ $node['nivel'] }}
                                @endif
                            </span>
                        </button>
                        <button type="button"
                            class="timeline-node-label"
                            data-timeline-toggle
                            aria-expanded="false"
                            aria-controls="timeline-desc-{{ $node['id'] }}">
                            {{ $node['name'] }}
                        </button>
                    </div>
                    <div class="timeline-desc"
                         id="timeline-desc-{{ $node['id'] }}"
                         hidden>
                        <p class="timeline-desc-status timeline-desc-status--{{ $node['status'] }}">
                            @if($node['status'] === 'past')
                                Ya aprobado
                            @elseif($node['status'] === 'current')
                                Curso actual
                            @else
                                Pendiente
                            @endif
                        </p>
                        <p class="timeline-desc-text">{{ $node['description'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
        <p class="timeline-hint">Toca un curso para ver su descripción debajo.</p>
    @endif
</div>

<style>
.timeline-page {
    max-width: 640px;
    margin: 0 auto;
    padding: 1rem 1.25rem 3rem;
    width: 100%;
    box-sizing: border-box;
    overflow-x: hidden;
}
.timeline-header {
    margin-bottom: 1.25rem;
}
.timeline-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e3a5f;
    margin: 0 0 0.35rem;
    word-wrap: break-word;
}
.timeline-subtitle {
    color: #4b5563;
    margin: 0 0 1rem;
    line-height: 1.45;
    word-wrap: break-word;
}
.timeline-legend {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem 1.25rem;
    font-size: 0.9rem;
    color: #374151;
}
.timeline-legend li {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.timeline-dot {
    width: 0.85rem;
    height: 0.85rem;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}
.timeline-dot--past { background: #1d4ed8; }
.timeline-dot--current {
    background: #c9a227;
    box-shadow: 0 0 0 2px #fef3c7;
}
.timeline-dot--future {
    background: transparent;
    border: 2px solid #3b82f6;
}
.timeline-empty {
    color: #6b7280;
    padding: 2rem 0;
}
.timeline-track {
    list-style: none;
    margin: 0;
    padding: 0.25rem 0 0;
    position: relative;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}
.timeline-step {
    position: relative;
    padding: 0 0 1.35rem 0;
    padding-left: 3.5rem;
    max-width: 100%;
    box-sizing: border-box;
    overflow-wrap: anywhere;
}
.timeline-step:last-child {
    padding-bottom: 0.25rem;
}
.timeline-step:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 1.2rem;
    top: 2.75rem;
    bottom: 0;
    width: 3px;
    background: #93c5fd;
    border-radius: 2px;
}
.timeline-step-main {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-height: 2.75rem;
    min-width: 0;
    max-width: 100%;
}
.timeline-node {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    position: absolute;
    left: 0;
    top: 0;
    z-index: 1;
}
.timeline-node-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 50%;
    font-size: 0.85rem;
    font-weight: 700;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.timeline-node:hover .timeline-node-circle,
.timeline-node:focus-visible .timeline-node-circle {
    transform: scale(1.06);
}
.timeline-node:focus-visible {
    outline: none;
}
.timeline-node:focus-visible .timeline-node-circle {
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.35);
}
.timeline-node--past .timeline-node-circle {
    background: #1d4ed8;
    color: #fff;
    border: 3px solid #1d4ed8;
}
.timeline-node--current .timeline-node-circle {
    background: #c9a227;
    color: #1f2937;
    border: 3px solid #a16207;
    box-shadow: 0 0 0 4px rgba(201, 162, 39, 0.35);
}
.timeline-node--future .timeline-node-circle {
    background: #fff;
    color: #2563eb;
    border: 3px solid #3b82f6;
}
.timeline-node-label {
    background: none;
    border: none;
    padding: 0.15rem 0;
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    text-align: left;
    line-height: 1.3;
    cursor: pointer;
    min-width: 0;
    max-width: 100%;
    overflow-wrap: anywhere;
    word-break: break-word;
}
.timeline-node-label:hover {
    color: #1d4ed8;
    text-decoration: underline;
}
.timeline-step.is-current .timeline-node-label {
    color: #92400e;
}
.timeline-desc {
    margin: 0.55rem 0 0.15rem;
    padding: 0.7rem 0.85rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    border-left: 3px solid #93c5fd;
    max-width: 100%;
    width: 100%;
    box-sizing: border-box;
    overflow: hidden;
}
.timeline-step.is-current .timeline-desc {
    border-left-color: #c9a227;
    background: #fffbeb;
}
.timeline-desc-status {
    margin: 0 0 0.35rem;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.timeline-desc-status--past { color: #1d4ed8; }
.timeline-desc-status--current { color: #a16207; }
.timeline-desc-status--future { color: #3b82f6; }
.timeline-desc-text {
    margin: 0;
    color: #374151;
    font-size: 0.92rem;
    line-height: 1.5;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    word-break: break-word;
    max-width: 100%;
}
.timeline-hint {
    margin-top: 1rem;
    font-size: 0.85rem;
    color: #6b7280;
}
.page-main-btn-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

@media (max-width: 480px) {
    .timeline-page {
        padding: 0.75rem 0.85rem 2.5rem;
    }
    .timeline-step {
        padding-left: 3.15rem;
    }
    .timeline-desc {
        padding: 0.6rem 0.7rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var current = document.getElementById('timeline-current');
    if (current) {
        requestAnimationFrame(function () {
            current.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }

    document.querySelectorAll('.timeline-step').forEach(function (step) {
        var toggles = step.querySelectorAll('[data-timeline-toggle]');
        var panel = step.querySelector('.timeline-desc');
        if (!panel || !toggles.length) return;

        function setOpen(open) {
            panel.hidden = !open;
            toggles.forEach(function (btn) {
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }

        toggles.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var willOpen = panel.hasAttribute('hidden');
                document.querySelectorAll('.timeline-desc').forEach(function (other) {
                    if (other !== panel) other.hidden = true;
                });
                document.querySelectorAll('[data-timeline-toggle]').forEach(function (otherBtn) {
                    if (!step.contains(otherBtn)) {
                        otherBtn.setAttribute('aria-expanded', 'false');
                    }
                });
                setOpen(willOpen);
            });
        });
    });
});
</script>
@endsection
