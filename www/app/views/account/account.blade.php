@extends('../layout')
@section('title', 'account')

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
            <h2>Change Email adress</h2>
            <hr>
            <div class="form-group">
                <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Change your email address" value="{{ $_SESSION["data"]["email"] ?? null }}">
            </div>
            <div class="form-group">
                <input type="password" class="form-control" id="oldPassword2" name="oldPassword2" placeholder="Password">
            </div>
            <button type="submit" class="btn text-white bg-primary col-12">Change Email</button>
            <h2 style="padding-top:20px;">Change Username</h2>
            <hr>
            <div class="form-group">
                <input type="text" class="form-control" id="username" name="username" placeholder="Change your username">
            </div>
            <button type="submit" class="btn text-white bg-primary col-12">Change username</button>
            <h2 style="padding-top:20px;">Change Password</h2>
            <hr>
            <div class="form-group">
                <input type="password" class="form-control" id="oldPassword" name="oldPassword" placeholder="Old password">
            </div>
            <div class="form-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="New password">
            </div>
            <div class="form-group">
                <input type="password" class="form-control" id="newPasswordVerif" name="newPasswordVerif" placeholder="Confirm new password">
            </div>
            <br>
            <button type="submit" class="btn text-white bg-primary col-12">Update password</button>
        </form>
    </div>
</div>
@endsection
