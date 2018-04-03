@extends('admin/layout')
@section('title', 'Networks list | Administration')

@section('content')
    <section class="page-content-wrapper">
        <div class="container-fluid">
            <div class="d-flex justify-content-between mb-1">
                <h1>Networks list</h1>
                <div>
                    <a href="/admin/network/add" class="btn btn-primary"><i class="fas fa-plus"></i></a>
                </div>
            </div>
            @if($model->networks !== false)
                <div class="list-group">
                    @foreach($model->networks as $network)
                        <a href="/admin/network/{{ $network->mac }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>{{ $network->ipv4 }} - {{ $network->ipv6 }}</span>
                            <span class="badge badge-primary badge-pill">{{ $network->mac }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
