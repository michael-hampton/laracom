
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
                
                <div class="form-group">
                    <label for="courier">Courier</label>
                    <select name="courier" id="courier" class="form-control">
                        <option value="">--Select--</option>
                        @foreach($couriers as $courier)
                        <option @if(old('courier') == $courier->id) selected="selected" @endif value="{{ $courier->id }}">{{ $courier->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                            <label class="" for="voucher_code">Voucher Code</label>
                            <input lineid="0" placeholder="Voucher Code" name="voucher_code" id="voucher_code" class="form-control">
                </div>

                <button class="btn btn-sm btn-primary float-right m-t-n-xs add-more" style="margin-bottom:10px;" type="button"><strong>+</strong></button>

                <div class="products">
                    <div class="form-inline">
                        @if(!empty($products))
                        <div class="form-group">
                            <label class="sr-only" for="product">Product</label>
                            <select lineid="0" name="products[0][id]" id="product" class="main form-control select2 scope-select">
                                <option value="">--Select--</option>
                                @foreach($products as $product)
                                <option price="{{ $product->price }}" @if(old('product') == $product->id) selected="selected" @endif value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="form-group">
                            <label class="sr-only" for="quantity">Quantity</label>
                            <input lineid="0" placeholder="Quantity" name="products[0][quantity]" id="quantity" class="form-control quantity">
                        </div

                        <div class="form-group">
                            <label class="sr-only" for="price">Price</label>
                            <input lineid="0" placeholder="Price" readonly="readonly" id="price" class="form-control price">
                        </div
                    </div>
                </div>


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

        bindHandlers();

        $('.add-more').off();
        $('.add-more').on('click', function () {
            
            var count = $('.scope-select').length;
            
            var products = $('.main.scope-select option');

            var HTML = '<div class="form-inline" style="margin-top: 12px;">' +
                    '<div class="form-group">' +
                    '<label class="sr-only" for="product">Product</label>' +
                    '<select lineid="' + count + '" name="products[' + count + '][id]" id="product" class="form-control select2 scope-select">' +
                    products +
                    '</select>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<label class="sr-only" for="quantity">Quantity</label>' +
                    '<input lineid="' + count + '" placeholder="Quantity" name="products[' + count + '][quantity]" id="quantity" class="form-control quantity">' +
                    '</div' +
                    '<div class="form-group">' +
                    '<label class="sr-only" for="price">Price</label>' +
                    '<input lineid="' + count + '" placeholder="Price" readonly="readonly" id="price" class="form-control price">' +
                    '</div>' +
                    '</div>';

            $('.products').append(HTML);
            populateProductSelect(count);
            bindHandlers();
        });
    });

    /**
     * 
     
     * @param {type} lineId
     * @returns {undefined} */
    function populateProductSelect(lineId) {
        $(".main > option").each(function (key, value) {

            var price = $(this).attr('price');

            $('.scope-select[lineid=' + lineId + ']')
                    .append($("<option></option>")
                            .attr("price", price)
                            .attr("value", this.value)
                            .text(this.text));
        });
    }

    /**
     * 
     * @param {type} lineId
     * @returns {undefined}
     */
    function calculatePrice(lineId) {
        var price = $('.scope-select[lineid=' + lineId + '] option:selected').attr('price');
        var quantity = $('.quantity[lineid=' + lineId + ']').val();

        if (quantity !== '' && price !== '') {
            var total = price * parseInt(quantity);
            $('.price[lineid=' + lineId + ']').val(total);
        }

        calculateTotal();
    }

    function calculateTotal() {

        var total = 0;

        $('.price').each(function () {

            var price = parseFloat($(this).val());

            total += price;

        });

        $('#total').val(total);
    }

    function bindHandlers() {

        $('.quantity').on('keyup', function () {

            var lineId = $(this).attr('lineid');
            calculatePrice(lineId);
        });

        $('#product').on('change', function () {

            var lineId = $(this).attr('lineid');
            calculatePrice(lineId);

            return false;
        });
    }


</script>
@endsection

