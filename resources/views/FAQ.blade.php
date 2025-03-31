@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="page-title">FAQ</h1>

    @Auth
        @if(Auth::user()->is_admin)
            <div class="card">
                <h2 class="card-title">Add a new question</h2>
                <form action="{{ route('FAQ') }}" method="POST" class="form-container">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="question" placeholder="Question" class="form-input">
                    </div>
                    <div class="form-group">
                        <input type="text" name="answer" placeholder="Answer" class="form-input">
                    </div>
                    <div class="form-group">
                        <select name="category" class="form-input">
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
            </div>
        @endif
    @endauth

    <div class="faq-section">
        @foreach($categories as $category)
            <div class="card">
                <div class="category-header">
                    <h3 class="category-title">{{ $category }}</h3>
                </div>
                <div class="card-content">
                    @foreach($faqs->where('category', $category) as $faq)
                        <div class="faq-item">
                            <h4 class="faq-question">{{ $faq->question }}</h4>
                            <p class="faq-answer">{{ $faq->answer }}</p>

                            @Auth
                                @if(Auth::user()->is_admin)
                                    <div class="faq-actions">
                                        <form action="{{ route('FAQ.delete', $faq->id) }}" method="POST" class="inline-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>

                                        <a href="{{ route('FAQ.edit', $faq->id) }}" class="btn btn-primary">Edit</a>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection


