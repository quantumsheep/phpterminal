@extends('admin/layout')
@section('title', 'Terminal')

@section('styles')
<link rel="stylesheet" href="/assets/css/terminal.css">
@endsection

@section('content')
    <section class="page-content-wrapper container-fluid">
        <h1>Add a new terminal</h1>
        <br>
        <form method="POST">
            <div class="form-group">
                <label for="accountSelect">Owner's account</label>
                <select class="form-control" id="accountSelect" name="account">
                    <option></option>
                    @foreach($model->accounts as &$account)
                        @if(!empty($_GET["account"]) && $_GET["account"] == $account->idaccount)
                            <option selected="selected" value="{{ $account->idaccount }}">{{ $account->email }} - {{ $account->username }}</option>
                        @else
                            <option value="{{ $account->idaccount }}">{{ $account->email }} - {{ $account->username }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="networkSelect">Network</label>
                <select class="form-control" id="networkSelect" name="account">
                    <option></option>
                    @foreach($model->networks as &$network)
                        @if(!empty($_GET["network"]) && $_GET["network"] == $network->mac)
                            <option selected="selected" value="{{ $network->mac }}">{{ $network->mac }}</option>
                        @else
                            <option value="{{ $network->mac }}">{{ $network->mac }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create the new terminal</button>
        </form>
    </section>
@endsection

@section('script')
<script src="/assets/js/terminal.js"></script>
@endsection
