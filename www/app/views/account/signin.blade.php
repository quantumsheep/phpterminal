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
                <label for="password">Email address</label>
                <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email" value="{{ifsetor($_SESSION["data"]["email"], "")}}">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password">
            </div>

            <br>
            <button type="submit" class="btn btn-primary col-12">Sign In</button>
        </form>
    </div>
</div>
@endsection
