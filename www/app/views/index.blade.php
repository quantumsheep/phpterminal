@extends('layout')
@section('title', 'Terminal')

@section('styles')
<link rel="stylesheet" href="/assets/css/terminal.css">
@endsection

@section('content')
    <div class="container" id="index-container">
        <div id="index-anim01">
            <h1>Welcome to alPH</h1>
            <p class="lead">Ever wanted to learn about GNU/Linux commands? alPH will provide you everything you need to go further in your competencies!</p>
            <hr class="my-4 border-light">
            <p>It uses utility classes for typography and spacing to space content out within the larger container.</p>
        </div>
    </div>

    <div id="term-test" class="terminal container">
    <div class="terminal container" id="terminal-container-test">
        <div id="term">
            <div class="terminal-content" id="terminal-content-user-test">
                <div id="terminal-content-response-test">
                    <div id="terminal-user-test">user@user:~ $
                        <span class="terminal-input" id="terminal-input-test" contenteditable="false" spellcheck="false"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="terminal container shy" id="terminal-container">
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
<script src="/assets/js/terminal-index.js"></script>
@endsection
