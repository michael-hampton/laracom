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
                    <form action="{{ route('admin.products.search' }}" method="post" id="admin-search">

                <div class="form-group">
                    <label for="category">Category </label>
                    <select name="category" id="category" class="form-control">
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="country">Brand </label>
                    <select name="brand" id="brand" class="form-control">
                        @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="product_name">Product Name</label>
                    <input name="product_name" id="product_name" class="form-control">
                </div>
                </form>
                    @include('admin.shared.products')
                    {{ $products->links() }}
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        @endif

    </section>
    <!-- /.content -->
@endsection
