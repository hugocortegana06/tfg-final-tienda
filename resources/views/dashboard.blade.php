@extends('layouts.app')

@section('title','Dashboard')

@section('content')
<div class="text-center mt-5">
    <h1>¡Hola, {{ Auth::user()->name }}!</h1>
    <p>Rol: {{ Auth::user()->role }}</p>
</div>
@endsection
