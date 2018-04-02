@extends('../layout')
@section('title', 'Signup')

@section('content')
<div class="container">
    @if(!empty($_SESSION["errors"]))
        <div class="row col-12 alert alert-danger" role="alert">
            @foreach ($_SESSION["errors"] as $error)
                {!! $error !!}
            @endforeach
        </div>
    @endif
    @if(!empty($_SESSION["success"]))
        <div class="row col-12 alert alert-success" role="alert">
            @foreach ($_SESSION["success"] as $success)
                {!! $success !!}
            @endforeach
        </div>
    @endif
    <div class="row">
        <form class="col-md-6 m-auto" method="POST">
            {!! csrf_token() !!}
            <div class="form-group">
                <label for="password">Username</label>
                    <input type="text" required="required" class="form-control" id="uname" name="username" aria-describedby="usernameHelp" placeholder="Enter username" value="{{ $_SESSION["data"]["username"] ?? null }}">
            </div>
            <div class="form-group">
                <label for="password">Email address</label>
                <input type="email" required="required" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email" value="{{ $_SESSION["data"]["email"] ?? null }}">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" required="required" class="form-control" id="password" name="password" placeholder="Password">
            </div>
            <div class="form-group">
                <label for="password2">Password</label>
                <input type="password" required="required" class="form-control" id="password2" name="password2" placeholder="Retype password">
            </div>
            <div class="text-muted">By clicking on Sign up, you agree to <a href="/about/tos" target="blank">SMN's terms & conditions</a></div>

            <br>
            <button type="submit" class="btn btn-primary col-12">Sign Up</button>
            <a href="/signin">Or login to your account here.</a>
        </form>
    </div>
</div>
@endsection
