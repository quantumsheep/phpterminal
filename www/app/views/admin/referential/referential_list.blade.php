@extends('admin/layout')
@section('title', 'Terminals list | Administration')

@section('content')
    <section class="page-content-wrapper">
        <div class="container-fluid">
            <div class="d-flex justify-content-between mb-1">
                <h1>Referential categories</h1>
                <div>
                    <a href="/admin/referential/add{{ !empty($model->idreferential) ? "?category=" . $model->idreferential : "" }}" class="btn btn-primary"><i class="fas fa-plus"></i></a>
                </div>
            </div>
            <form id="category-form" method="GET" class="d-flex justify-content-between mb-1">
                <select id="category-select" class="form-control mr-1">
                    <option></option>
                    @foreach($model->referentials as $referential)
                        <option value="{{ $referential->idreferential }}">{{ $referential->code }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-caret-right text-white"></i></button>
            </form>
            @if(!empty($model->referential) && $model->referential->type == 0)
            <table class="table table-bordered">
                <tr>
                    <th>Code</th>
                    <td>{{ $model->referential->code }}</td>
                </tr>
                <tr>
                    <th>Category</th>
                    <td>{{ $model->referentialParentName }}</td>
                </tr>
                <tr>
                    <th>Value</th>
                    <td>
                        <form method="POST" class="d-flex justify-content-between">
                            {!! csrf_token() !!}
                            <input type="text" class="form-control mr-2" name="value" id="value-input" value="{{ $model->referential->value }}">
                            <button type="submit" class="btn btn-primary">Change</button>
                        </form>
                    </td>
                </tr>
            </table>
            @else
            <div class="list-group">
                @foreach($model->referentials as $referential)
                    <a href="/admin/referential/{{ $referential->idreferential }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>{{ $referential->code }}</span>
                        <span class="badge badge-primary badge-pill">{{ $referential->type == 1 ? "Category" : "Item" }}</span>
                    </a>
                @endforeach
            </div>
            @endif
        </div>
    </section>
@endsection

@section('script')
<script>
document.getElementById('category-form').addEventListener('submit', e => {
    e.preventDefault();

    window.location = `/admin/referential/${document.getElementById('category-select').value}`;
});
</script>
@endsection
