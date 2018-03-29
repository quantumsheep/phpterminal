@extends('admin/layout')
@section('title', 'Terminal')

@section('content')
    <section class="page-content-wrapper">
        <div class="container-fluid">
            <h1>Accounts list</h1>
            <form method="GET">
                <div class="d-flex">
                    <input type="text" class="form-control mr-1" name="search" placeholder="Search an account" value="{{ $_GET["search"] ?? "" }}">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
                <ul class="pagination mt-3">
                    <li class="page-item"><a class="page-link" href="#"><</a></li>
                    @for($i = 0, $j = 1; $i < $model->numberAccounts; $i += 10, $j++)
                        <li class="page-item"><a class="page-link" href="?page={{ $j }}{{ $_GET["search"] ?? null !== null ? "&search=" . $_GET["search"] : "" }}">{{ $j }}</a></li>
                    @endfor
                    <li class="page-item"><a class="page-link" href="#">></a></li>
                </ul>
            </form>
            @if($model->accounts !== false)
                <div class="list-group">
                    @foreach($model->accounts as $account)
                        <a href="/admin/account/{{ $account->idaccount }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-circle text-{{ $account->status == 1 ? 'success' : 'danger' }}"></i> {{ $account->username }} - {{ $account->email }}</span>
                            <span class="badge badge-primary badge-pill">{{ $model->terminalsCount[$account->idaccount] }} Terminals</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
