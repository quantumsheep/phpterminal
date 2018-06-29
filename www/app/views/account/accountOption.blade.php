@extends('../layout')
@section('title', 'accountOption')

@section('content')
<div class="container">
    @if(!empty($_SESSION["errors"]))
        <div class="row col-12 alert alert-danger" role="alert">
            @foreach ($_SESSION["errors"] as $error)
                {{$error}}
            @endforeach
        </div>
    @endif
    @if(!empty($_SESSION["success"]))
        <div class="row col-12 alert alert-success" role="alert">
            @foreach ($_SESSION["success"] as $success)
                {{$success}}
            @endforeach
        </div>
    @endif
    <div class="row">
        <form class="col-md-6 m-auto" method="POST">
            {!! csrf_token() !!}
            <div class="form-group">
                <label for="email">Change your email address</label>
                <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email" value="{{ $_SESSION["data"]["email"] ?? null }}">
            </div>
            <div class="form-group">
                <label for="username">Change your username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="username">
            </div>
            <div class="form-group">
                <label for="password">Change your password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="password">
            </div>
            <div class="form-group">
                <label for="newPasswordVerif">Verify your new password</label>
                <input type="password" class="form-control" id="newPasswordVerif" name="newPasswordVerif" placeholder="newPasswordVerif">
            </div>
            <br>
            <div class="form-group">
                <label for="oldPassword">Enter your actual password</label>
                <input type="password" required="required" class="form-control" id="oldPassword" name="oldPassword" placeholder="oldPassword">
            </div>
            <button type="submit" class="btn text-white bg-primary col-12">Apply change</button>
        </form>
    </div>
</div>
@endsection
