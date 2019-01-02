
@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <div class="box">
        <form action="{{ route('admin.orders.store') }}" method="post" class="form" enctype="multipart/form-data">
            <div class="box-body">
                {{ csrf_field() }}

                @if(empty($selectedChannel) && !$channels->isEmpty())
                <div class="form-group">
                    <label for="channel">Channel</label>
                    <select name="channel" id="channel" class="form-control select2">
                        <option value="">--Select--</option>
                        @foreach($channels as $channel)
                        <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>
                @else;
                <input type="hidden" name="channel" id="channel" value="{{ $selectedChannel }}">
                @endif;

                <input type="hidden" name="total" id="total">

                @if(!empty($customers))
                <div class="form-group">
                    <label for="customer">Customer</label>
                    <select name="customer" id="customer" class="form-control select2 scope">

                        @foreach($customers as $customer)
                        <option @if(old('customer') == $customer->id) selected="selected" @endif value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if(!empty($products))
                <div class="form-group">
                    <label for="product">Product</label>
                    <select name="products[0][id]" id="product" class="form-control select2 scope-select">
                        <option value="">--Select--</option>
                        @foreach($products as $product)
                        <option price="{{ $product->price }}" @if(old('product') == $product->id) selected="selected" @endif value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input name="products[0][quantity]" id="quantity" class="form-control">

                </div>


                <input type="hidden" name="price" id="price">
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <div class="btn-group">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-default">Back</a>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </div>
        </form>
    </div>
    <!-- /.box -->

</section>
<!-- /.content -->
@endsection

@section('js')
<script type="text/javascript">
    $(document).ready(function () {

        $('#product').on('change', function () {

            var price = $(this).find(":selected").attr('price');

            $('#total').val(price);
            $('#price').val(price);

            return false;
        });
    });


</script>
@endsection

