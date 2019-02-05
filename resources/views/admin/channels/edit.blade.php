@extends('layouts.admin.app')

<?php

function buildcheckBox($value, $label) {

    $checked = $value == 1 ? 'checked' : '';

    echo '<input type="checkbox" ' . $checked . ' class="test" id="' . $label . '">';
}
?>

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap2/bootstrap-switch.min.css" rel="stylesheet" type="text/css">

    <select name="channelSelect" id="channelSelect" class="form-control select2">
        <option value="">Select Channel</option>
        @foreach($channels as $objChannel)
        <option value="{{ $objChannel->id }}">{{ $objChannel->name }}</option>
        @endforeach
    </select>

    <div class="row">
        <div class="col-lg-6 pull-left">
            <div class="box">
                <div class="box-body channel-div">

                    <form action="{{ route('admin.channels.updateChannel') }}" id="channelForm" channel-id="{{ $channel->id }}" class="form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <!-- <input type="hidden" name="_method" value="put"> -->
                        <h2 class="channel-name">{{ ucfirst($channel->name) }}</h2>

                        <input type="hidden" name="id" value="{{$channel->id}}">

                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" placeholder="Name" class="form-control" value="{{ $channel->name ?: old('name') }}">
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="text" name="email" id="email" placeholder="Email" class="form-control" value="{{ $channel->email }}">
                        </div>

                        <div class="form-group">
                            <label for="description">Description </label>
                            <textarea class="form-control ckeditor" name="description" id="description" rows="5" placeholder="Description">{{ $channel->description ?: old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            @if(isset($channel->cover))
                            <div class="col-md-3">
                                <div class="row">
                                    <img src="{{ asset($channel->cover) }}" alt="" class="img-responsive"> <br />
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="cover">Cover </label>
                            <input type="file" name="cover" id="cover" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="status">Status </label>
                            <select name="status" id="status" class="form-control">
                                <option value="1" @if($channel->status == 1) selected="selected" @endif>Enable</option>
                                <option value="0" @if($channel->status == 0) selected="selected" @endif>Disable</option>
                            </select>
                        </div>

                        <div class="btn-group">
                            <a href="{{ route('admin.channels.index') }}" class="btn btn-default">Back</a>
                            <button type="button" class="btn btn-primary UpdateChannel">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        <div class="col-lg-6 pull-right">
            <div class="box">
                <div class="box-body">
                    <h2>Settings</h2>

                    <div class="form-group">
                        <label for="status">Has Priority </label>
                        {{buildCheckbox($channel->has_priority, 'has_priority')}}
                    </div>

                    <div class="form-group">
                        <label for="status">Allocate On Order </label>
                        {{buildCheckbox($channel->allocate_on_order, 'allocate_on_order')}}
                    </div>
                    <div class="form-group">
                        <label for="status">Backorders enabled </label>
                        {{buildCheckbox($channel->backorders_enabled, 'backorders_enabled')}}
                    </div>


                    <div class="form-group">
                        <label for="status">Strict validation </label>
                        {{buildCheckbox($channel->strict_validation, 'strict_validation')}}
                    </div>

                    <div class="form-group">
                        <label for="status">Partial Shipment </label>
                        {{buildCheckbox($channel->partial_shipment, 'partial_shipment')}}
                    </div>

                </div>
            </div>

        </div>

        <div class="col-lg-6 pull-right">
            <div class="box">
                <div class="box-body">
                    <h2>Notifications</h2>

                    <div class="form-group">
                        <label for="status">Send Order Received Email </label>
                        {{buildCheckbox($channel->send_received_email, 'send_received_email')}}
                    </div>

                    <div class="form-group">
                        <label for="status">Send Dispatched Email </label>
                        {{buildCheckbox($channel->send_dispatched_email, 'send_dispatched_email')}}
                    </div>

                    <div class="form-group">
                        <label for="status">Send Order Hung Email </label>
                        {{buildCheckbox($channel->send_hung_email, 'send_hung_email')}}
                    </div>

                    <div class="form-group">
                        <label for="status">Send Backorder Email </label>
                        {{buildCheckbox($channel->send_backorder_email, 'send_backorder_email')}}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 pull-right">
            <div class="box">
                <div class="box-body provider-div">
                    <h2>Channel Providers</h2>

                    <?php
                    $providerArr = $providers->toArray();
                    ?>

                    <div class="form-inline">
                        <div class="form-group">
                            <select id='paymentProviderSelect' class="form-control">
                                @foreach($arrProviders as $arrProvider)
                                @if(!in_array($arrProvider, $providerArr))
                                <option value="{{$arrProvider->id}}">{{$arrProvider->name}}</option>
                                @endif;
                                @endforeach;
                            </select>
                        </div>

                        <button channel-id="{{ $channel->id }}" class="btn btn-primary addProvider">+</button>
                    </div>

                    <ul class='providerList list list-group clear-list'>
                        @foreach($providers as $provider)
                        <li class='list-group-item'>{{$provider->name}}
                            <a href="#" class="deleteProvider" provider-id="{{$provider->id}}">x</a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6 pull-right">
            <div class="box">
                <div class="box-body product-div">
                    <h2>Products</h2>

                    <div class="form-inline">
                        <div class="form-group col-lg-6">
                            <!-- <input placeholder="Search Product" type="text" class="form-control">-->
                            <select style="width:100%;" id='productSelect' class="form-control">
                                @foreach($products as $product)
                                <option value="{{$product->id}}">{{$product->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <input id='productPrice' placeholder="Price" type="text" class="form-control">
                        </div>

                        <button channel-id="{{ $channel->id }}"  class="btn btn-primary addProduct">+</button>
                    </div>

                    <a href="#" class="view-all-products">View All Products</a>

                    <ul class='productList list list-group clear-list' style="display:none;">
                        @foreach($assigned_products as $objProduct)
                        <li style="margin-top:12px;" class='list-group-item'>{{$objProduct->name}} {{$objProduct->price}}</li>
                        @endforeach;
                    </ul>
                </div>
            </div>
        </div>
    </div>


    <div class="row">

        <div class="col-lg-6 pull-left">
            <div class="box">
                <div class="box-body template-div">
                    <h2>Templates</h2>

                    <form id='templateForm'>

                        <input type='hidden' name='channel' value='{{ $channel->id }}'>

                        {{ csrf_field() }}

                        <div class="form-group">
                            <label>Return</label>
                            <textarea name='templates[1][return]' class="form-control"><?= (isset($templates[1]) ? $templates[1]->description : '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Dispatch</label>
                            <textarea name='templates[2][dispatch]' class="form-control"><?= (isset($templates[2]) ? $templates[2]->description : '') ?></textarea>
                        </div>

                        <button channel-id="{{ $channel->id }}"  class="btn btn-primary saveTemplate">Save</button>
                    </form>


                </div>
            </div>
        </div>

        <div class="col-lg-6 pull-left">
            <div class="box">
                <div class="box-body warehouse-div">
                    <h2>Warehouses</h2>

                    <ul class='list list-group clear-list'>
                        <?php
                        foreach ($warehouses as $warehouse)
                        {

                            $channel_warehouse_id = isset($assigned_warehouses[$warehouse->id]) ? $assigned_warehouses[$warehouse->id]->id : '';
                            $class = isset($assigned_warehouses[$warehouse->id]) ? 'warehouse-assigned' : 'warehouse-not-assigned';
                            ?>


                            <li style = "margin-top:12px;" class='list-group-item'>
                                {{$warehouse->name}}
                                <img channel-warehouse-id="{{$channel_warehouse_id}}" channel-id="{{$channel->id}}" warehouse-id="{{$warehouse->id}}" style = "width:30px;" class = "<?= $class ?>" src="{{url('/images/tick.png')}}"/>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>






</section>



<!--/.content -->
@endsection

@section('css')
<style type = "text/css">

    .main-footer {
        display: none;
    }

    .warehouse-not-assigned {
        opacity: 0.6;
    }

    .warehouse-assigned {
        opacity: 1.0;
    }
</style>
@endsection

@section('js')
<script src = "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.js" data-turbolinks-track = "true"></script>

<script type="text/javascript">

$(document).ready(function () {

    $('#channelSelect').on('change', function () {
        location.href = '/admin/channels/' + $(this).val() + '/edit';
    });

    $(document).off('.warehouse-assigned');
    $(document).on('click', '.warehouse-assigned', function (ev) {

        $this = $(this);

        let id = $(this).attr("channel-warehouse-id");
        var $this = $(this);

        $.ajax({
            type: 'DELETE',
            url: '/admin/channels/deleteWarehouse/' + id,
            data: {id: id, "_token": "{{ csrf_token() }}"},
            success: function (data) {
                $this.removeClass('warehouse-assigned').addClass('warehouse-not-assigned');
                $('.warehouse-div').prepend("<div class='alert alert-success'>Provider has been deleted successfully</div>");
            },
            error: function (data) {
                alert(data);
            }
        });
    });

    $(document).off('.warehouse-not-assigned');
    $(document).on('click', '.warehouse-not-assigned', function (ev) {

        var channel = $(this).attr('channel-id');
        var warehouse = $(this).attr('warehouse-id');
        var $this = $(this);

        $('.warehouse-div .alert-danger').remove();
        $('.warehouse-div .alert-success').remove();

        $.ajax({
            type: "POST",
            url: '/admin/channels/addChannelToWarehouse',
            data: {
                channel: channel,
                warehouse: warehouse,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.http_code == 400) {
                    $('.warehouse-div').prepend("<div class='alert alert-danger'></div>");
                    $.each(response.errors, function (key, value) {
                        $('.warehouse-div .alert-danger').append("<p>" + value + "</p>");
                    });
                } else {
                    $('.warehouse-div').prepend("<div class='alert alert-success'>Warehouse has been added successfully</div>");
                    $this.removeClass('warehouse-not-assigned').addClass('warehouse-assigned');
                }
            }
            ,
            error: function (data) {
                alert(data);
            }
        });
    });

    $(document).off('.deleteProvider');
    $(document).on('click', '.deleteProvider', function (ev) {

        $this = $(this);

        let id = $(this).attr("provider-id");
        $.ajax({
            type: 'DELETE',
            url: '/admin/channels/deleteProvider/' + id,
            data: {id: id, "_token": "{{ csrf_token() }}"},
            success: function (data) {
                $this.parent().remove();
                $('.provider-div').prepend("<div class='alert alert-success'>Provider has been deleted successfully</div>");
            },
            error: function (data) {
                alert(data);
            }
        });
    });
    $('.test').bootstrapSwitch();

    $('.view-all-providers').off();
    $('.view-all-providers').on('click', function (e) {
        e.preventDefault();
        $('.providerList').animate({opacity: 'toggle'}, 'slow');
        var text = $(this).text();

        $(this).text(
                text == "View All Providers" ? "Hide Providers" : "View All Providers");

    });

    $('.view-all-products').off();
    $('.view-all-products').on('click', function (e) {
        e.preventDefault();
        $('.productList').animate({opacity: 'toggle'}, 'slow');
        var text = $(this).text();

        $(this).text(
                text == "View All Products" ? "Hide Products" : "View All Products");

    });

    $('.addProvider').off();
    $('.addProvider').on('click', function () {

        var channel = $(this).attr('channel-id');
        var provider = $('#paymentProviderSelect').val();
        var name = $('#paymentProviderSelect option:selected').text();

        $('.provider-div .alert-danger').remove();
        $('.provider-div .alert-success').remove();

        $.ajax({
            type: "POST",
            url: '/admin/channels/addChannelProvider',
            data: {
                channel: channel,
                provider: provider,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                $('.providerList').append('<li>' + name + '<a href="#" class="deleteProvider" provider-id="' + provider + '">x</a></li>');
                if (response.http_code == 400) {
                    $('.provider-div').prepend("<div class='alert alert-danger'></div>");
                    $.each(response.errors, function (key, value) {
                        $('.provider-div .alert-danger').append("<p>" + value + "</p>");
                    });
                } else {
                    $('.provider-div').prepend("<div class='alert alert-success'>Provider has been added successfully</div>");
                }


                //$('.provider-div').prepend("<div class='alert alert-success'>Shipping rate has been updated successfully</div>");
            }
            ,
            error: function (data) {
                alert(data);
            }
        });
    });
    $('.saveTemplate').on('click', function (e) {

        e.preventDefault();
        var channel = $(this).attr('channel-id');
        var formdata = $('#templateForm').serialize();

        $('.template-div .alert-danger').remove();
        $('.template-div .alert-success').remove();

        $.ajax({
            type: "POST",
            url: '/admin/channels/saveChannelTemplate',
            data: formdata,
            success: function (response) {
                if (response.http_code == 400) {
                    $('.template-div').prepend("<div class='alert alert-danger'></div>");
                    $.each(response.errors, function (key, value) {
                        $('.template-div .alert-danger').append("<p>" + value + "</p>");
                    });
                } else {
                    $('.template-div').prepend("<div class='alert alert-success'>Template has been updated successfully</div>");
                }


                //$('.template-div').prepend("<div class='alert alert-success'>Shipping rate has been updated successfully</div>");
            },
            error: function (data) {
                alert(data);
            }
        });
    });
    $('.addProduct').on('click', function () {

        var channel = $(this).attr('channel-id');
        var product = $('#productSelect').val();
        var productName = $('#productSelect option:selected').text();

        $('.product-div .alert-danger').remove();
        $('.product-div .alert-success').remove();

        var price = $('#productPrice').val();
        $.ajax({
            type: "POST",
            url: '/admin/channels/addProductToChannel',
            data: {
                product: product,
                price: price,
                channel: channel,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                $('.productList').append('<li class="list-group-item">' + productName + ' ' + price + '</li>');
                if (response.http_code == 400) {
                    $('.product-div').prepend("<div class='alert alert-danger'></div>");
                    $.each(response.errors, function (key, value) {
                        $('.product-div .alert-danger').append("<p>" + value + "</p>");
                    });
                } else {
                    $('.product-div').prepend("<div class='alert alert-success'>Product has been updated successfully</div>");
                    $('#productSelect option[value="' + product + '"]').remove();
                }


                //$('.product-div').prepend("<div class='alert alert-success'>Shipping rate has been updated successfully</div>");
            },
            error: function (data) {
                alert(data);
            }
        });
    });
    $('.UpdateChannel').on('click', function (e) {

        var channel = $('#channelForm').attr('channel-id');
        //var formdata = $(this).serialize();
        var formdata = new FormData($('#channelForm')[0]);
        var href = $('#channelForm').attr('action');

        $.ajax({
            type: "POST",
            url: href,
            data: formdata,
            cache: false,
            processData: false,
            contentType: false,
            success: function (response) {

                if (response.http_code == 400) {
                    $('.channel-div').prepend("<div class='alert alert-danger'></div>");
                    $.each(response.errors, function (key, value) {
                        $('.channel-div .alert-danger').append("<p>" + value + "</p>");
                    });
                } else {
                    $('.channel-name').html($('#name').val());
                    $('.channel-div').prepend("<div class='alert alert-success'>Product has been updated successfully</div>");
                }


                // $('.channel-div').prepend("<div class='alert alert-success'>Shipping rate has been updated successfully</div>");
            },
            error: function (data) {
                alert(data);
            }
        });
    });
    $('.test').on('switchChange.bootstrapSwitch', function () {

        if ($(this).bootstrapSwitch('state')) {
            var val = 1;
            $(this).val("1");
        } else {
            var val = 0;
            $(this).val("0");
        }

        var id = $(this).attr('id');
        var channelId = $("#channelForm").attr("channel-id");
        $.ajax({
            type: "POST",
            url: '/admin/channels/saveChannelAttribute',
            data: {
                channelId: channelId,
                id: id,
                value: val,
                _token: '{{ csrf_token() }}'
            },
            success: function (msg) {
                alert(msg);
            },
            error: function (data) {
                alert(data);
            }
        });
    });
});
</script>
@endsection

