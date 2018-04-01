@extends('admin/layout')
@section('title', 'Terminal ' . $model->terminal->mac . ' | Administration')

@section('styles')
<link rel="stylesheet" href="/assets/css/terminal.css">
@endsection

@section('content')
    <section class="page-content-wrapper container-fluid d-flex flex-column h-100">
        <section>
            <h1>Terminal {{ $model->terminal->mac }}</h1>
            <h5>
                <a href="/admin/account/{{ $model->account->idaccount }}">{{ $model->account->username }}</a>
                <span>- {{ $model->account->email }} - Network</span>
                <a href="/admin/network/{{ $model->terminal->localnetwork }}">{{ $model->terminal->localnetwork }}</a>
            </h5>
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
