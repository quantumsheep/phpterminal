@extends('layout')
@section('title', 'Terminal list')

@section('content')
    <div class="container d-flex flex-wrap justify-content-between">
        @foreach($model->terminals as $terminal)
            <a href="/terminal/{{ $terminal->mac }}" class="terminal-list-item col-md-2 p-2 m-1 d-inline-block">
                <img src="/assets/img/terminal_icon.png" class="w-100 h-auto">
                <span>{{ $terminal->mac }}</span>
            </a>
        @endforeach
    </div>
@endsection
