@extends('../layout')
@section('title', 'Logon')

@section('content')
<div class="container">
    @if(!empty($_SESSION["errors"]))
        <div class="row col-12 alert alert-danger" role="alert">
            @foreach ($_SESSION["errors"] as $error)
                {{$error}}
            @endforeach
        </div>
    @endif
    <div class="row">
        <form class="col-md-6 m-auto" method="POST">
            <div class="form-group">
                <label for="password">Username</label>
                    <input type="text" class="form-control" id="uname" name="username" aria-describedby="usernameHelp" placeholder="Enter username" value="{{ifsetor($_SESSION["data"]["username"], "")}}">
                <small id="usernameHelp" class="form-text text-muted">We'll never share your username with anyone else.</small>
            </div>
            <div class="form-group">
                <label for="password">Email address</label>
                <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email" value="{{ifsetor($_SESSION["data"]["email"], "")}}">
                <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password">
            </div>
            <div class="text-muted">By clicking on Sign up, you agree to <a href="/about/tos" target="_blank">SMN's terms & conditions</a></div>

            <br>
            <button type="submit" class="btn btn-primary col-12">Sign Up</button>
        </form>
    </div>
</div>
@endsection
