@extends('layout')
@section('title', 'Terminal')

@section('styles')
<link rel="stylesheet" href="/assets/css/terminal.css">
@endsection

@section('content')
    <div class="terminal container" id="terminal-container">
        <div class="terminal-content" id="terminal-content-user">
            </div>
            <div id="terminal-content-response">
            <div id="terminal-user">user@user:~ $
                <span class="terminal-input" id="terminal-input" contenteditable="true" spellcheck="false"></span>
                <span class="terminal-caret">█</span>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="/assets/js/terminal.js"></script>
@endsection
