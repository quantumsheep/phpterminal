@extends('admin/layout')
@section('title', 'Terminals list | Administration')

@section('content')
    <section class="page-content-wrapper">
        <div class="container-fluid">
            <div class="d-flex justify-content-between mb-1">
                <h1>Referential categories</h1>
                <div>
                    <a href="/admin/referential/add" class="btn btn-primary"><i class="fas fa-plus"></i></a>
                </div>
            </div>
            <div class="list-group">
                @foreach($model->referentialCategories as $referentialCategory)
                    <a href="/admin/terminal/{{ $terminal->mac }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>{{ $model->accounts[$terminal->account]->username }} - {{ $model->accounts[$terminal->account]->email }}</span>
                        <span class="badge badge-primary badge-pill">{{ $terminal->mac }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
