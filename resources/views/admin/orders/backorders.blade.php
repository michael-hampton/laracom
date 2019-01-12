@extends('layouts.admin.app')

@section('content')


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

    @include('layouts.errors-and-messages')
    <!-- Default box -->

    <div class="box">
        <div class="box-body">
            <h2>Backorders</h2>

            <!-- search form -->
            <div class="col-lg-12">
                <form action="{{ route('admin.orderLine.search') }}" method="post" id="admin-search">

                    {{ csrf_field() }}

                    <div class="row">
                        <div class="pull-left col-lg-3">
                            <div class="input-group" style="width:100%">
                                <input type="text" name="customer_ref" class="form-control" placeholder="Customer Ref" value="{{ old('q')}}">
                            </div>
                        </div>

                        <div class="pull-left col-lg-3">
                            <div class="input-group" style="width:100%">
                                <input type="text" name="customer_name" class="form-control" placeholder="Customer Name" value="{{ old('q')}}">
                            </div>
                        </div>

                        <div class="pull-left col-lg-3">
                            <div class="input-group" style="width:100%">
                                <input type="text" name="customer_email" class="form-control" placeholder="Customer Email" value="{{ old('email')}}">
                            </div>
                        </div>

                        <div class="pull-left col-lg-3">
                            <div class="input-group" style="width:100%">
                                <input type="text" name="product_name" class="form-control" placeholder="Product Name" value="{{ old('product_name')}}">
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-top: 12px;">


                        <div class="pull-left col-lg-2">
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

                        <div class="pull-left col-lg-2">
                            @if(!$couriers->isEmpty())
                            <div class="form-group">
                                <select name="line_courier[]" multiple='multiple' id="courier" class="form-control select2">
                                    @foreach($couriers as $courier)
                                    <option @if(old('courier') == $courier->id) selected="selected" @endif value="{{ $courier->id }}">{{ $courier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        <span class="input-group-btn">
                            <button type="submit" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i> Search </button>
                        </span>

                        <input type="hidden" id="status" name="line_status" value="11">
                        <input type="hidden" id="module" name="module" value="backorders">
                    </div>

                </form>
            </div>
            <!-- /.box-body -->

        </div>
        <!-- /.box -->

        <div class="box">
            @if(!$items->isEmpty())
            <div class="box-body">
                <h4> <i class="fa fa-gift"></i> Items</h4>
                <table class="table">
                    <thead>
                    <th class="col-md-2">SKU</th>
                    <th class="col-md-2">Name</th>
                    <th class="col-md-2">Quantity</th>
                    <th class="col-md-2">Price</th>
                    <th class="col-md-2">Actions</th>
                    </thead>
                    <tbody>

                        <?php
                        foreach ($items as $item) {

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
                                    <input type="checkbox" checked='checked' class="cb" name="services[]" order-id="{{ $item->order_id }}" value="{{ $item->id }}">
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <div class="box-footer">
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
            var cb = [];
            $.each($('.cb:checked'), function () {
                cb.push({
                    order_id: $(this).attr('order-id'),
                    line_id: $(this).val()
                });
            });

            $.ajax({
                type: "POST",
                url: '/admin/orderLine/processBackorders',
                data: {
                    lineIds: cb,
                    _token: '{{ csrf_token() }}'
                },
                success: function (msg) {
                    alert(msg);
                }
            });
            return false;
        });
    });
</script>
@endsection;

