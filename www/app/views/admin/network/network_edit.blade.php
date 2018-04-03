@extends('admin/layout')
@section('title', 'Network ' . $model->network->mac . ' | Administration')

@section('content')
    <section class="page-content-wrapper">
        <div class="container-fluid">
            <h1>Networks visualization</h1>
            <table class="table table-bordered">
                <tr>
                    <th>Mac address</th>
                    <td>{{ $model->network->mac }}</td>
                </tr>
                <tr>
                    <th>IPv4</th>
                    <td>{{ $model->network->ipv4 }}</td>
                </tr>
                <tr>
                    <th>IPv6</th>
                    <td>{{ $model->network->ipv6 }}</td>
                </tr>
            </table>

            <br>
            <div class="d-flex justify-content-between mb-1">
                <h2>Terminals ({{ count($model->terminals) }})</h2>
                <div>
                    <a href="/admin/terminal/add?network={{ $model->network->mac }}" class="btn btn-primary"><i class="fas fa-plus"></i></a>
                </div>
            </div>
            @if($model->terminals !== false)
                <div class="list-group">
                    @foreach($model->terminals as $terminal)
                        <a href="/admin/terminal/{{ $terminal->mac }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>{{ $terminal->mac }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
