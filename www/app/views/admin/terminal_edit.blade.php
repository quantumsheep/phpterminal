@extends('admin/layout')
@section('title', 'Terminal')

@section('styles')
<link rel="stylesheet" href="/assets/css/terminal.css">
@endsection

@section('content')
    <section class="page-content-wrapper container-fluid d-flex flex-column h-100">
        <section>
            <h1>Terminal {{ $model->terminals[0]->mac }}</h1>
            <h5>{{ $model->accounts[0]->username }} - {{ $model->accounts[0]->email }}</h5>
            <br>
        </section>
        <div class="card h-100" style="overflow: auto;">
            <div class="terminal" id="terminal-container">
                <span class="terminal-content" id="terminal-content-user">
                    
                </span>
                <span id="terminal-content-response">
                    <span class="terminal-input" id="terminal-input" contenteditable="true" spellcheck="false"></span>
                    <span class="terminal-caret">█</span>
                </span>
            </div>
        </div>
    </section>
@endsection

@section('script')
<script src="/assets/js/terminal.js"></script>
@endsection
