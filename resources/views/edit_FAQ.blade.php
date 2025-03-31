@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="page-title">Edit FAQ</h1>

    <div class="card">
        <form action="{{ route('FAQ.update', $faq->id) }}" method="POST" class="form-container">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="question">Question:</label>
                <input type="text" id="question" name="question" value="{{ $faq->question }}" class="form-input">
            </div>
            <div class="form-group">
                <label for="answer">Answer:</label>
                <input type="text" id="answer" name="answer" value="{{ $faq->answer }}" class="form-input">
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" value="{{ $faq->category }}" class="form-input">
            </div>
            <button type="submit" class="btn btn-primary">Update FAQ</button>
        </form>
    </div>
</div>
@endsection


