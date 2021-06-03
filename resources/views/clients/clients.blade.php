@extends('layout.layout')

@section('title', 'clients')

@section('clients')


<div>
    <form action="/clients/clients-test" method="POST">
        @csrf
        @method('POST')
        <input type="text" name="username">
        <button type="submit">Go</button>
    </form>

</div>


@endsection