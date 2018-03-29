@extends('layout')
@section('title', 'Terminal list')

@section('content')
    <div class="container">
        <div class="d-flex">
        @foreach($model->terminals as $terminal)
            <a href="/terminal/{{ $terminal->mac }}" class="terminal-list-item col-md-2 p-2 m-1">
                <img src="/assets/img/terminal_icon.png" class="w-100 h-auto">
                <span>{{ $terminal->mac }}</span>
            </a>
            <a href="/terminal/{{ $terminal->mac }}" class="terminal-list-item col-md-2 p-2 m-1">
                <img src="/assets/img/terminal_icon.png" class="w-100 h-auto">
                <span>{{ $terminal->mac }}</span>
            </a>
            <a href="/terminal/{{ $terminal->mac }}" class="terminal-list-item col-md-2 p-2 m-1">
                <img src="/assets/img/terminal_icon.png" class="w-100 h-auto">
                <span>{{ $terminal->mac }}</span>
            </a>
            <a href="/terminal/{{ $terminal->mac }}" class="terminal-list-item col-md-2 p-2 m-1">
                <img src="/assets/img/terminal_icon.png" class="w-100 h-auto">
                <span>{{ $terminal->mac }}</span>
            </a>
            <a href="/terminal/{{ $terminal->mac }}" class="terminal-list-item col-md-2 p-2 m-1">
                <img src="/assets/img/terminal_icon.png" class="w-100 h-auto">
                <span>{{ $terminal->mac }}</span>
            </a>
            <a href="/terminal/{{ $terminal->mac }}" class="terminal-list-item col-md-2 p-2 m-1">
                <img src="/assets/img/terminal_icon.png" class="w-100 h-auto">
                <span>{{ $terminal->mac }}</span>
            </a>
            <a href="/terminal/{{ $terminal->mac }}" class="terminal-list-item col-md-2 p-2 m-1">
                <img src="/assets/img/terminal_icon.png" class="w-100 h-auto">
                <span>{{ $terminal->mac }}</span>
            </a>
        @endforeach
    </div>
@endsection

@section('script')
<script src="/assets/js/terminal.js"></script>
@endsection
