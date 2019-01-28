
@extends('layouts.admin.app')

@section('content')

@if(isset($arrErrors) && $valid === false)

<div class="alert alert-danger">
    The import has the following errors:
    <ul>
        @foreach ($arrErrors as $message)
        <li>{{ $message }}</li>
        @endforeach
    </ul>
</div>
@endif;

<form action="/admin/products/saveImport" method="post"enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="file" id="csv_file" name="csv_file">
    <input type="submit" value="Submit">
</form>

@endsection;
