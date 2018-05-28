@extends('../layout')
@section('title', 'accountOption')

@section('content')
<div class="container">
    
    <h2>Account Information</h2>
    <div class="container">
        <div>
            username : {{ $_SESSION["account"]->username}}
        </div>
        <div>
            email : {{ $_SESSION["account"]->email }}
        </div>
    </div>
</div>
@endsection
