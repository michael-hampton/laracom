
@extends('layouts.admin.app')

@section('content')

@if ($errors->import->any())
<div class="alert alert-danger">
    The import has following errors in <strong>line {{ session('error_line') }}</strong>:
    <ul>
        @foreach ($errors->import->all() as $message)
        <li>{{ $message }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="/admin/orders/saveImport" method="post"enctype="multipart/form-data">
       {{ csrf_field() }}
    <input type="file" id="csv_file" name="csv_file">
    <input type="submit" value="Submit">
</form>


Hereford

@endsection;
