@extends('layouts.admin.app')

@section('content')

@include('layouts.errors-and-messages')

<?php

/**
 * 
 * @param type $productId
 * @param type $arrProducts
 * @return type
 */
function getInventoryForProduct($productId, $arrProducts) {
    $test = $arrProducts->filter(function ($item) use($productId) {
                return $item->id == $productId;
            })->first();

    return array('quantity' => $test->quantity, 'reserved_stock' => $test->reserved_stock);
}
?>
<!-- Main content -->
<section class="content">
    <div class="col-lg-3">
        <div class="box">
            <div class="box-body">
                <h2>Orders</h2>

                <!-- search form -->
                <div class="col-lg-12">
                    <form action="{{ route('admin.orderLine.search') }}" method="post" id="admin-search">

                        {{ csrf_field() }}


                        <div style="margin-bottom: 10px;">
                            <div class="input-group" style="width:100%">
                                <input type="text" name="customer_ref" class="form-control" placeholder="Customer Ref" value="{{ old('q')}}">
                            </div>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <div class="input-group" style="width:100%">
                                <input type="text" name="customer_name" class="form-control" placeholder="Customer Name" value="{{ old('q')}}">
                            </div>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <div class="input-group" style="width:100%">
                                <input type="text" name="customer_email" class="form-control" placeholder="Customer Email" value="{{ old('email')}}">
                            </div>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <div class="input-group" style="width:100%">
                                <input type="text" name="product_name" class="form-control" placeholder="Product Name" value="{{ old('product_name')}}">
                            </div>
                        </div>


                        <div style="margin-bottom: 10px;">
                            @if(!$channels->isEmpty())
                            <div class="form-group">
                                <select name="order_channel" id="channel" class="form-control select2">
                                    <option value="">Channel</option>
                                    @foreach($channels as $channel)
                                    <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        <div style="margin-bottom: 10px;">
                            @if(!$couriers->isEmpty())
                            <div class="form-group">
                                <select name="line_courier[]" multiple='multiple' id="courier" class="form-control select2">
                                    @foreach($couriers as $courier)
                                    <option @if(old('courier') == $courier->id) selected="selected" @endif value="{{ $courier->id }}">{{ $courier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif


                            <span class="input-group-btn">
                                <button type="submit" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i> Search </button>
                            </span>

                            <input type="hidden" id="status" name="line_status" value="14">
                            <input type="hidden" id="module" name="module" value="allocations">
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="box">
            <div class="box-body">

                <a href="#" class="uncheck">Uncheck</a>

                <table class="table">
                    <thead>
                    <th class="col-md-2">Customer Ref</th>
                    <th class="col-md-2">Name</th>
                    <th class="col-md-2">Quantity</th>
                    <th class="col-md-2">Price</th>
                    <th class="col-md-2">Actions</th>
                    </thead>
                    <tbody>


                        @foreach($items as $item)

                        <?php
                        $arrInventory = getInventoryForProduct($item->product_id, $products);

                        if (strtotime($item->created_at) < strtotime('-30 days')) {
                            $color = '#FF6666';
                        } elseif (strtotime($item->created_at) < strtotime('-15 days')) {
                            $color = '#C0C0C0';
                        } else {
                            $color = '#FFFF99';
                        }
                        ?>

                        <tr style="background-color: {{ $color }}">
                            <td>{{ $item->product_sku }}</td>
                            <td>
                                {{ $item->product_name }}

                            </td>
                            <td>{{ $item->quantity }}
                                <br>Free Stock {{$arrInventory['quantity']}}
                                <br>Reserved Stock {{$arrInventory['reserved_stock']}}
                            </td>
                            <td>{{ $item->product_price }}</td>

                            <td>
                                <?php
                                $quantityAvailiable = $arrInventory['quantity'] - $arrInventory['reserved_stock'];
                                //$checked = $quantityAvailiable > 0 ? 'checked="checked"' : '';
                                //$disabled = $quantityAvailiable == 0 ? 'disabled="disabled"' : '';
                                ?>
                                <input type="checkbox" checked="checked" class="cb" name="services[]" order-id="{{ $item->order_id }}" value="{{ $item->id }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>



        </div>
    </div>


    <div class="box-footer col-lg-12">

        <div class="btn-group pull-right">
            <button type="button" class="btn btn-primary do-allocation">Allocate</button>
        </div>

        <div class='checkbox-count'></div>

        {{ $items->links() }}

    </div>

</section>
<!-- /.content -->
@endsection

@section('js')
<script type="text/javascript">
    $(document).ready(function () {

        $('.uncheck').click(function () {
            var checkboxes = $('.cb');
            $('.cb').prop('checked', !checkboxes.prop('checked'));
        });

        $('.cb').change(function () {
            var numberOfChecked = $('.cb:checked').length;
            var totalCheckboxes = $('.cb').length;
            $('.checkbox-count').html(numberOfChecked + ' / ' + totalCheckboxes);
        });

        $('.do-allocation').on('click', function () {

            if ($('.cb:checked').length == 0)
            {
                alert('Please select atleast one checkbox');
                return false;
            }
            var cb = {};
            $.each($('.cb:checked'), function () {

                var orderId = $(this).attr('order-id');

                if (cb[orderId] === undefined) {
                    cb[orderId] = [];
                }

                $(this).parent().parent().addClass('toBeRemoved');

                cb[orderId].push($(this).val());
            });

            $.ajax({
                type: "POST",
                url: '/admin/orderLine/doAllocation',
                data: {
                    lineIds: cb,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    var response = JSON.parse(response);

                    if (response.http_code === 400) {

                        $('.content').prepend("<div class='alert alert-danger'></div>");

                        $.each(response.FAILURES, function (lineId, val) {

                            $('.content .alert-danger').append("<p> Line Id: " + lineId + " " + val + "</p>");

                        });
                    } else {
                        $('.content').prepend("<div class='alert alert-success'></div>");

                        $.each(response.SUCCESS, function (lineId, val) {

                            $('.content .alert-success').append("<p>" + val + "</p>");

                        });

                        $('.toBeRemoved').remove();

                    }

                }
            });
            return false;
        });
    });
</script>
@endsection;
