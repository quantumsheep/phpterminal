@extends('admin/layout')
@section('title', 'Terminal')

@section('content')
    <section class="page-content-wrapper">
        <div class="container-fluid">
            <h1>Terminals list</h1>
            @if($model->terminals !== false)
                <div class="list-group">
                    @foreach($model->terminals as $terminal)
                        <a href="/admin/terminal/{{ $terminal->mac }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>{{ $model->users[$terminal->account]->username }} - {{ $model->users[$terminal->account]->email }}</span>
                            <span class="badge badge-primary badge-pill">{{ $terminal->mac }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
