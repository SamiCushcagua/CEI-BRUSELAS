@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/styles_PROFESOR.css') }}?v={{ filemtime(public_path('css/styles_PROFESOR.css')) }}">

<div class="grades-container">
    <div class="page-main-btn-wrapper">
        <a href="{{ route('welcome') }}" class="btn btn-primary">🏠 Página principal</a>
        @if(auth()->user()->is_profesor)
            <a href="{{ route('professors.subjects', auth()->user()) }}" class="btn btn-secondary">Mis materias</a>
        @endif
    </div>

    <div class="grades-header">
        <div>
            <h1 class="grades-title">Plan de curso — {{ $subject->name }}</h1>
            <p class="grades-subtitle">{{ $period->name }} · Completa el plan del trimestre. Ambos maestros pueden editarlo.</p>
        </div>
        <div class="grades-info">
            <span class="course-plan-status {{ $plan->isPublished() ? 'course-plan-status-published' : 'course-plan-status-draft' }}">
                {{ $plan->isPublished() ? 'Publicado' : 'Borrador' }}
            </span>
            @if($plan->lastEditor)
                <div style="margin-top: 0.5rem; font-size: 0.85rem; color: #666;">
                    Última edición: {{ $plan->lastEditor->name }}
                </div>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 1.2rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p style="margin-bottom: 1rem; color: #555;">
        <strong>Periodo vigente:</strong> {{ $period->name }}
    </p>

    @if($period->is_locked && ! auth()->user()->is_admin)
        <div class="alert alert-error">Este periodo está bloqueado. Puedes ver el plan pero no editarlo.</div>
    @endif

    @php $editable = ! $period->is_locked || auth()->user()->is_admin; @endphp

    @include('course-plans._form', ['editable' => $editable])

    <div class="course-plan-actions">
        <a href="{{ route('course-plans.show', $subject) }}" class="btn btn-secondary">
            Vista previa (como lo ven los alumnos)
        </a>
    </div>
</div>
@endsection
