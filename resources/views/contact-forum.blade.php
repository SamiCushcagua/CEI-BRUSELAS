@extends('layouts.app')

@section('content')
<div class="container">
    <div class="contact-forum-container">
        <h1>Contact Forum</h1>

        @auth
            @if(Auth::user()->is_admin)
                <h2>Contact Messages</h2>

                <table>
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contactForum as $contact)
                            <tr>
                                <td>{{ $contact->email }}</td>
                                <td>{{ $contact->message }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>        
            @endif
        @endauth
    </div>
</div>
@endsection