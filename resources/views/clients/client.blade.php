@extends('layout.layout')

@section('title', 'single client')


@section('single-client')

<div>

    <p>Single client with id {{$id}}</p>

    <div>

        @forelse ($users as $user )

        @if($user['age'] > 30 || $user['name'] == 'aziz')

        <h1>{{$user['name']}}</h1>

        <h4>{{$user['age']}}</h4>


       

        @endif

        
            
        @empty

        <p>Sorry, empty array</p>
            
        @endforelse

    

    </div>

    @include("reusable.button", [
        "text" => "Edit"
    ])
</div>


@endsection