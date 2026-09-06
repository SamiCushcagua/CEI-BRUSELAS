@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}?v={{ filemtime(public_path('css/styles_PROFESOR.css')) }}">

<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
        @if(auth()->user()->is_profesor && ($canEdit ?? false))
            <a href="{{ route('professors.subjects', auth()->user()) }}" class="btn btn-secondary">Mis materias</a>
        @elseif(auth()->user()->is_profesor)
            <a href="{{ route('professors.subjects', auth()->user()) }}" class="btn btn-secondary">Mis materias</a>
        @elseif(! auth()->user()->is_profesor && ! auth()->user()->is_admin)
            <a href="{{ route('students.subjects', auth()->user()) }}" class="btn btn-secondary">Mis materias</a>
        @endif
    </div>

    <div class="grades-header">
        <div>
            <h1 class="grades-title">Plan de curso — {{ $subject->name }}</h1>
            <p class="grades-subtitle">{{ $period->name }}</p>
        </div>
        <div class="grades-info">
            @if($unpublishedForStudent ?? false)
                <span class="course-plan-status course-plan-status-draft">Aún no publicado</span>
            @else
                <span class="course-plan-status {{ $plan->isPublished() ? 'course-plan-status-published' : 'course-plan-status-draft' }}">
                    {{ $plan->isPublished() ? 'Publicado' : 'Borrador' }}
                </span>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <p style="margin-bottom: 1rem; color: #555;">
        <strong>Periodo vigente:</strong> {{ $period->name }}
    </p>

    @if($unpublishedForStudent ?? false)
        <div class="empty-state" style="max-width: 560px; margin: 1.5rem auto; text-align: center;">
            <div class="empty-icon">📋</div>
            <h3 class="empty-title">Plan de curso aún no publicado</h3>
            <p class="empty-description" style="line-height: 1.6;">
                El plan de curso de <strong>{{ $subject->name }}</strong> para
                <strong>{{ $period->name }}</strong> todavía no ha sido publicado por el maestro.
                Cuando lo publique, podrás consultarlo aquí.
            </p>
            <div style="margin-top: 1.25rem;">
                <a href="{{ route('students.subjects', auth()->user()) }}" class="btn btn-primary">
                    Volver a Mis cursos
                </a>
            </div>
        </div>
    @else
        @if($canEdit ?? false)
            <div class="course-plan-actions">
                <a href="{{ route('course-plans.edit', $subject) }}" class="btn btn-primary">
                    Editar plan de curso
                </a>
            </div>
        @endif
        @if($period->is_locked && ! auth()->user()->is_admin)
            <div class="alert alert-error" style="background: #fff3cd; border-color: #ffc107; color: #856404;">
                Este periodo está bloqueado. Solo puedes consultar el plan (no editarlo).
            </div>
        @endif

        @include('course-plans._form', ['editable' => false])

        @if($canEdit ?? false)
            <div class="course-plan-actions">
                <a href="{{ route('course-plans.edit', $subject) }}" class="btn btn-primary">
                    Editar plan de curso
                </a>
            </div>
        @endif
    @endif
</div>
@endsection
