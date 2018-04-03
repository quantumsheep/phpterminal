@extends('layout')
@section('title', 'Terminal')

@section('styles')
<link rel="stylesheet" href="/assets/css/terminal.css">
@endsection

@section('content')
    <div class="terminal container" id="terminal-container">
        <span class="terminal-content" id="terminal-content-user">
        
        </span>
        <span id="terminal-content-response">
            <span class="terminal-input" id="terminal-input" contenteditable="true" spellcheck="false"></span>
            <span class="terminal-caret">█</span>
        </span>
    </div>
@endsection

@section('script')
<script src="/assets/js/terminal.js"></script>
@endsection
