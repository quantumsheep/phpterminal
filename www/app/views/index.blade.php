@extends('layout')
@section('title', 'Terminal')

@section('content')
    <div class="container">
        <b class="text-white">Welcome to PHP Terminal</b>
        <?php var_dump($_SESSION); ?>
    </div>
@endsection