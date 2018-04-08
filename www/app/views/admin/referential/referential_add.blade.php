@extends('admin/layout')
@section('title', 'New referential | Administration')

@section('styles')
<link rel="stylesheet" href="/assets/css/terminal.css">
@endsection

@section('content')
    <section class="page-content-wrapper container-fluid">
        <h1>New referential</h1>
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
                <label for="type-select">Type</label>
                <select class="form-control" name="type" id="type-select">
                    @if(!empty($_GET["category"]))
                    <option value="0" selected="selected">Item</option>
                    <option value="1">Category</option>
                    @else
                    <option value="0">Item</option>
                    <option value="1" selected="selected">Category</option>
                    @endif
                </select>
            </div>
            <div class="form-group">
                <label for="category-select">Category</label>
                <select class="form-control" name="category" id="category-select">
                    <option></option>
                    @foreach($model->referentials as &$referential)
                        @if($referential->idreferential == $_GET["referential"])
                            <option value="{{ $referential->idreferential }}" selected="selected">{{ $referential->code }}</option>
                        @else
                            <option value="{{ $referential->idreferential }}">{{ $referential->code }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="code-input">Code</label>
                <input type="text" class="form-control" name="code" id="code-input">
            </div>
            <div class="form-group{{ !empty($_GET["category"]) ? "" : " d-none" }}" id="value-input-parent">
                <label for="value-input">Value</label>
                <input type="text" class="form-control" name="value" id="value-input">
            </div>
            <button type="submit" class="btn btn-primary">Create the new referencial</button>
        </form>
    </section>
@endsection

@section('script')
<script>
document.getElementById("type-select").addEventListener("change", e => {
    if(e.target.value === "0") {
        document.getElementById("value-input-parent").classList.remove("d-none");
    } else if(e.target.value === "1") {
        document.getElementById("value-input-parent").classList.add("d-none");
    }
});
</script>
@endsection
