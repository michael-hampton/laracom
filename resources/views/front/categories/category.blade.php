@extends('layouts.front.app')
@section('css')
<style>
    #loading
    {
        text-align:center; 
        background: url('/images/loader.gif') no-repeat center; 
        height: 150px;
    }
</style>
@endsection


<?php
$max = max($cost);
$min = min($cost);
?>    

@section('og')
<meta property="og:type" content="category"/>
<meta property="og:title" content="{{ $category->name }}"/>
<meta property="og:description" content="{{ $category->description }}"/>
@if(!is_null($category->cover))
<meta property="og:image" content="{{ asset("storage/$category->cover") }}"/>
@endif
@endsection

@section('content')
<div class="container">
    <hr>
    <div class="row">
        <div class="category-top col-md-12">
            <h2>{{ $category->name }}</h2>
            {!! $category->description !!}
        </div>
    </div>
    <hr>
    <div class="col-md-3">
        @include('front.categories.sidebar-category')
        @include('front.categories.sidebar-filters')
    </div>
    <div class="col-md-9">
        <div class="row">
            <div class="category-image">
                @if(isset($category->cover))
                <img src="{{ asset("$category->cover") }}" alt="{{ $category->name }}" class="img-responsive" />
                @else
                <img src="https://placehold.it/1200x200" alt="{{ $category->cover }}" class="img-responsive" />
                @endif
            </div>
        </div>
        <hr>
        <div class="row filter_data">
        </div>
    </div>
</div>
@endsection 
