@extends('layout')
@section('title', 'Terminal')

@section('content')
    <div class="terminal container">
        <div class="terminal-content">
            <div>user@user:~ $<input type="text" id="terminal-input"></div>
        </div>
    </div>
@endsection

@section('script')
<script src="/assets/js/terminal.js"></script>
@endsection