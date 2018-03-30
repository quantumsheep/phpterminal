@extends('admin/layout')
@section('title', 'Terminal')

@section('content')
    <section class="page-content-wrapper">
        <div class="container-fluid">
            <h1>Networks list</h1>
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
