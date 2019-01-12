@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <!-- Default box -->
    @if($products)
    <div class="box">
        <div class="box-body">
            <h2>Products</h2>

            <form action="{{ route('admin.channel-prices.search') }}" method="post" id="admin-search">
                
                 {{ csrf_field() }}

                <div class="form-group col-lg-2">
                    <label for="channel">Channel</label>
                    <select name="channel_id" id="channel" class="form-control select2">
                        <option value="">--Select--</option>
                        @foreach($channels as $channel)
                        <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-lg-2">
                    <label for="country">Category </label>
                    <select name="category" id="category" class="form-control">
                        <option value="">--Select--</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-lg-2">
                    <label for="country">Brand </label>
                    <select name="brand" id="brand" class="form-control">
                        <option value="">--Select--</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-lg-2">
                    <label for="product_name">Product Name</label>
                    <input name="product_name" id="product_name" class="form-control">
                </div>

                <button style="margin-top:26px;" type="submit" class="btn btn-primary">Search</button>

            </form>

            @include('admin.shared.channel-products')
            {{ $products->links() }}
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
    @endif

</section>
<!-- /.content -->
@endsection
