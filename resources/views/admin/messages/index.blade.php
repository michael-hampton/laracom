@extends('layouts.admin.app')
@section('content')
    @include('admin.messages.partials.flash')

    @each('admin.messages.partials.thread', $threads, 'thread', 'admin.messages.partials.no-threads')
@stop
