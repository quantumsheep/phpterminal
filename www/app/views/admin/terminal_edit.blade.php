@extends('admin/layout')
@section('title', 'Terminal')

@section('styles')
<link rel="stylesheet" href="/assets/css/terminal.css">
@endsection

@section('content')
    <section class="page-content-wrapper container-fluid d-flex flex-column h-100">
        <section>
            <h1>Terminal {{ $model->terminals[0]->mac }}</h1>
            <h5>{{ $model->users[0]->username }} - {{ $model->users[0]->email }}</h5>
            <br>
        </section>
        <div class="card h-100" style="overflow: auto;">
            <div class="terminal" id="terminal-container">
                <div class="terminal-content" id="terminal-content-user">
                    </div>
                    <div id="terminal-content-response">
                    <div id="terminal-user">user@user:~ $
                        <span class="terminal-input" id="terminal-input" contenteditable="true" spellcheck="false"></span>
                        <span class="terminal-caret">█</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
<script src="/assets/js/terminal.js"></script>
@endsection
