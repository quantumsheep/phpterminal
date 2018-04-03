@extends('admin/layout')
@section('title', 'New terminal | Administration')

@section('styles')
<link rel="stylesheet" href="/assets/css/terminal.css">
@endsection

@section('content')
    <section class="page-content-wrapper container-fluid">
        <h1>New terminal</h1>
        <br>
        @if(!empty($_SESSION["errors"]))
            <div class="col-12 alert alert-danger" role="alert">
                @foreach ($_SESSION["errors"] as $error)
                    <span>{!! $error !!}</span>
                @endforeach
            </div>
        @endif
        @if(!empty($_SESSION["success"]))
            <div class="col-12 alert alert-success" role="alert">
                @foreach ($_SESSION["success"] as $success)
                    <span>{!! $success !!}</span>
                @endforeach
            </div>
        @endif
        <form method="POST">
            {!! csrf_token() !!}
            <div class="form-group">
                <label for="account-select">Owner's account</label>
                <div class="d-flex">
                    <select required="required" class="form-control mr-1" id="account-select" name="account">
                        <option></option>
                        @foreach($model->accounts as &$account)
                            @if(!empty($_GET["account"]) && $_GET["account"] == $account->idaccount)
                                <option selected="selected" value="{{ $account->idaccount }}">{{ $account->email }} - {{ $account->username }}</option>
                            @else
                                <option value="{{ $account->idaccount }}">{{ $account->email }} - {{ $account->username }}</option>
                            @endif
                        @endforeach
                    </select>

                    @if(!empty($_GET["account"]) && array_key_exists($_GET["account"], $model->accounts))
                        <a href="/admin/account/{{ $_GET["account"] }}" target="blank" id="account-selected" class="btn btn-primary"><i class="fas fa-caret-right text-white"></i></a>
                    @else
                        <a href="" target="blank" id="account-selected" class="btn btn-primary disabled"><i class="fas fa-caret-right text-white"></i></a>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label for="network-select">Network</label>
                <div class="d-flex">
                    <select class="form-control mr-1" id="network-select" name="network">
                        <option></option>
                        @foreach($model->networks as &$network)
                            @if(!empty($_GET["network"]) && $_GET["network"] == $network->mac)
                                <option selected="selected" value="{{ $network->mac }}">{{ $network->mac }}</option>
                            @else
                                <option value="{{ $network->mac }}">{{ $network->mac }}</option>
                            @endif
                        @endforeach
                    </select>

                    @if(!empty($_GET["network"]) && array_key_exists($_GET["network"], $model->networks))
                        <a href="/admin/network/{{ $_GET["network"] }}" target="blank" id="network-selected" class="btn btn-primary"><i class="fas fa-caret-right text-white"></i></a>
                    @else
                        <a href="" target="blank" id="network-selected" class="btn btn-primary disabled"><i class="fas fa-caret-right text-white"></i></a>
                    @endif
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create the new terminal</button>
        </form>
    </section>
@endsection

@section('script')
<script>
if(document.querySelector('#account-select option[selected]')) {
    const index = [].indexOf.call(document.getElementById("account-select").children, document.querySelector('#account-select option[selected]'));
    document.querySelector('#account-select option[selected]').removeAttribute("selected");
    document.getElementById("account-select").selectedIndex = index;
}

if(document.querySelector('#network-select option[selected]')) {
    const index = [].indexOf.call(document.getElementById("network-select").children, document.querySelector('#network-select option[selected]'));
    document.querySelector('#network-select option[selected]').removeAttribute("selected");
    document.getElementById("network-select").selectedIndex = index;
}

document.getElementById("account-select").addEventListener("change", () => {
    if(document.getElementById("account-select").value) {
        document.getElementById("account-selected").classList.remove("disabled");
        document.getElementById("account-selected").href = `/admin/account/${document.getElementById("account-select").value}`;
    } else {
        document.getElementById("account-selected").classList.add("disabled");
        document.getElementById("account-selected").href = "";
    }
}, false);

document.getElementById("network-select").addEventListener("change", () => {
    if(document.getElementById("network-select").value) {
        document.getElementById("network-selected").classList.remove("disabled");
        document.getElementById("network-selected").href = `/admin/network/${document.getElementById("network-select").value}`;
    } else {
        document.getElementById("network-selected").classList.add("disabled");
        document.getElementById("network-selected").href = "";
    }
}, false);
</script>
@endsection
