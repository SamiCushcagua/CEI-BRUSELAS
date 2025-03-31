@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Cart</h1>

    @if($cartItems->isEmpty())
        <p>Emty cart</p>
    @else
        <div class="cart-items">
            @foreach($cartItems as $item)
                <div class="cart-item">
                    <h3>{{ $item->product->name }}</h3>
                    <p>Cantidad: {{ $item->quantity }}</p>
                    <form action="{{ route('cart.remove', $item) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Eliminar</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection 