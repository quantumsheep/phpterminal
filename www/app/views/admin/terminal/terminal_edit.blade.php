@extends('admin/layout')
@section('title', 'Terminal ' . $model->terminal->mac . ' | Administration')

@section('styles')
<link rel="stylesheet" href="/assets/css/terminal.css">
@endsection

@section('content')
    <section class="page-content-wrapper container-fluid d-flex flex-column h-100">
        <section>
            <div class="d-flex justify-content-between">
                <h1>Terminal {{ $model->terminal->mac }}</h1>
                <div>
                    <a href="/admin/terminal/switchnet" class="btn btn-primary"><i class="fas fa-edit"></i> Change network</a>
                </div>
            </div>
            <h5>
                <a href="/admin/account/{{ $model->account->idaccount }}">{{ $model->account->username }}</a>
                <span>- {{ $model->account->email }} - Network</span>
                <a href="/admin/network/{{ $model->terminal->localnetwork }}">{{ $model->terminal->localnetwork }}</a>
            </h5>
            <br>
        </section>
        <div class="card h-100" style="overflow: auto;">
            <div class="terminal" id="terminal-container">
                <span class="terminal-content" id="terminal-content-user"></span>
                <span class="terminal-input" id="terminal-input" contenteditable="true" spellcheck="false"></span>
                <div id="nano" class="nano d-none flex-column h-100 w-100">
                    <div class="nano-header" id="nano-header">File: </div>
                    <textarea id="nano-content" class="w-100 h-100 nano-content" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
                    <div class="nano-controls mt-2">
                        <div id="nano-message" class="nano-message d-flex"></div>
                        <div class="d-flex flex-wrap">
                            <div class="col-sm-2"><span class="nano-badge">^G</span> Get help</div>
                            <div class="col-sm-2"><span class="nano-badge">^O</span> Write Out</div>
                            <div class="col-sm-2"><span class="nano-badge">^W</span> Where Is</div>
                            <div class="col-sm-2"><span class="nano-badge">^K</span> Cut Text</div>
                            <div class="col-sm-2"><span class="nano-badge">^J</span> Justify</div>
                            <div class="col-sm-2"><span class="nano-badge">^C</span> Cur Pos</div>
                            <div class="col-sm-2"><span class="nano-badge">^Y</span> Prev Page</div>
                            <div class="col-sm-2"><span class="nano-badge">^X</span> Exit</div>
                            <div class="col-sm-2"><span class="nano-badge">^R</span> Read File</div>
                            <div class="col-sm-2"><span class="nano-badge">^\</span> Replace</div>
                            <div class="col-sm-2"><span class="nano-badge">^U</span> Uncut Text</div>
                            <div class="col-sm-2"><span class="nano-badge">^T</span> To Spell</div>
                            <div class="col-sm-2"><span class="nano-badge">^_</span> Go To Line</div>
                            <div class="col-sm-2"><span class="nano-badge">^V</span> Next Page</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
<script src="/assets/js/terminal.js"></script>
@endsection
