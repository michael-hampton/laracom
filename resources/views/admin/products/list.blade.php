@extends('layouts.admin.app')

@section('content')

@include('layouts.errors-and-messages')
<!-- Main content -->
<section class="content">
    <div class="col-lg-3">
        <div class="box">
            <div class="box-body">
                <h2>Products</h2>

                <!-- search form -->
                <div class="col-lg-12">
                    <form action="{{ route('admin.products.search') }}" method="post" id="admin-search">

                        {{ csrf_field() }}

                        <div style="margin-bottom: 10px;">
                            <label for="category">Category </label>
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
                            <label for="status">Status </label>
                            <select name="product_status" id="status" class="form-control"
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
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
                @include('admin.shared.products')
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
