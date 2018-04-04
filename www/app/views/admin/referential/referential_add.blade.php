@extends('admin/layout')
@section('title', 'New referential | Administration')

@section('styles')
<link rel="stylesheet" href="/assets/css/terminal.css">
@endsection

@section('content')
    <section class="page-content-wrapper container-fluid">
        <h1>New referential</h1>
        <br>
        @if(!empty($_SESSION["errors"]))
            <div class="col-12 alert alert-danger" role="alert">
                @foreach ($_SESSION["errors"] as $error)
                    <span>{!! $error !!}</span>
                @endforeach
            </div>
        @endif
        @if(!empty($_SESSION["success"]))
            <div class="col-12 alert alert-success" role="alert">
                @foreach ($_SESSION["success"] as $success)
                    <span>{!! $success !!}</span>
                @endforeach
            </div>
        @endif
        <form method="POST">
            {!! csrf_token() !!}

            <button type="submit" class="btn btn-primary">Create the new terminal</button>
        </form>
    </section>
@endsection
