@extends('../layout')
@section('title', 'accountOption')

@section('content')
<div class="container">
    
    <h2>Account Information</h2>
    <div class="container">
        <div class="row">
            <p >
            Username : {{ $_SESSION["account"]->username}}
            </p>
            
        </div>
        <div class="row">
        <p>

            Email : {{ $_SESSION["account"]->email }}
        </p>
            
        </div>
    </div>
        
        
</div>
@endsection
