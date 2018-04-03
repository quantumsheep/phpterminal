@extends('admin/layout')
@section('title', $model->account->username . ' account\'s | Administration')

@section('content')
    <section class="page-content-wrapper">
        <div class="container-fluid">
            <h1>Accounts edition : {{ $model->account->username }}</h1>
            <table class="table table-bordered">
                <tr>
                    <th>Status</th>
                    <td><i class="fas fa-circle text-{{ $model->account->status == 1 ? 'success' : 'danger' }}"></i></td>
                </tr>
                <tr>
                    <th>Username</th>
                    <td>{{ $model->account->username }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $model->account->email }}</td>
                </tr>
                <tr>
                    <th>Created date</th>
                    <td>{{ $model->account->createddate }}</td>
                </tr>
                @if($model->account->createddate !== $model->account->editeddate)
                <tr>
                    <th>Modified date</th>
                    <td>{{ $model->account->editeddate }}</td>
                </tr>
                @endif
            </table>

            <br>
            <div class="d-flex justify-content-between mb-1">
                <h2>Terminals</h2>
                <div>
                    <a href="/admin/terminal/add?account={{ $model->account->idaccount }}" class="btn btn-primary"><i class="fas fa-plus"></i></a>
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
