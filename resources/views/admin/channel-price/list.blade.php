@extends('layouts.admin.app')

@section('content')

@include('layouts.errors-and-messages')
<!-- Main content -->
<section class="content">
    <div class="col-lg-3">
        <div class="box">
            <div class="box-body">

                <!-- search form -->
                <div class="col-lg-12">
                    <form action="{{ route('admin.channel-prices.search') }}" method="post" id="admin-search">

                        {{ csrf_field() }}

                        <div style="margin-bottom: 10px;">
                            <label for="channel">Channel</label>
                            <select name="channel_id" id="channel" class="form-control select2">
                                <option value="">--Select--</option>
                                @foreach($channels as $channel)
                                <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <label for="country">Category </label>
                            <select name="category" id="category" class="form-control">
                                <option value="">--Select--</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <label for="country">Brand </label>
                            <select name="brand" id="brand" class="form-control">
                                <option value="">--Select--</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <label for="product_name">Product Name</label>
                            <input name="product_name" id="product_name" class="form-control">
                        </div>

                        <button style="margin-top:26px;" type="submit" class="btn btn-primary">Search</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="box">
            <div class="box-body">
                <h2>Products</h2>
                @if($products)
                @include('admin.shared.channel-products')
                {{ $products->links() }}
                @endif
            </div>



        </div>
    </div>


    <div class="box-footer col-lg-12">
        {{ $products->links() }}
    </div>

</section>
<!-- /.content -->
@endsection

@section('js')
<script type="text/javascript">
    $(document).ready(function () {

        $(document).on('mouseenter', '.product-div', function () {

            $(this).find(".btn-group").show();
        }).on('mouseleave', '.product-div', function () {
            $(this).find(".btn-group").hide();
        });
    });
</script>
@endsection;
