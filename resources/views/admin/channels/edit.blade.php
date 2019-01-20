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

    <div class="col-lg-6 pull-left">
        <div class="box">
            <div class="box-body channel-div">
                <form id="channelForm" channel-id="{{ $channel->id }}" action="{{ route('admin.channels.update', $channel->id) }}" method="post" class="form" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="put">
                    <h2>{{ ucfirst($channel->name) }}</h2>

                    <div class="form-group">
                        <label for="name">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" placeholder="Name" class="form-control" value="{{ $channel->name ?: old('name') }}">
                    </div>
                    <div class="form-group">
                        <label for="description">Description </label>
                        <textarea class="form-control ckeditor" name="description" id="description" rows="5" placeholder="Description">{{ $channel->description ?: old('description') }}</textarea>
                    </div>
                    <div class="form-group">
                        @if(isset($channel->cover))
                        <div class="col-md-3">
                            <div class="row">
                                <img src="{{ asset("storage/$channel->cover") }}" alt="" class="img-responsive"> <br />
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
                            <option value="0" @if($channel->status == 0) selected="selected" @endif>Disable</option>
                            <option value="1" @if($channel->status == 1) selected="selected" @endif>Enable</option>
                        </select>
                    </div>

                    <div class="btn-group">
                        <a href="{{ route('admin.channels.index') }}" class="btn btn-default">Back</a>
                        <button type="submit" class="btn btn-primary">Update</button>
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
            </div>
        </div>
    </div>

    <div class="col-lg-6 pull-right">
        <div class="box">
            <div class="box-body product-div">
                <h2>Products</h2>

                <div class="form-inline">
                    <div class="form-group">
                        <!-- <input placeholder="Search Product" type="text" class="form-control">-->
                        <select id='productSelect' class="form-control">
                            <option value="paypal">Paypal</option>
                            <option value="stripe">Stripe</option>
                            <option value="bank-transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <input id='productPrice' placeholder="Price" type="text" class="form-control">
                    </div>

                    <button channel-id="{{ $channel->id }}"  class="btn btn-primary addProduct">+</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 pull-right">
        <div class="box">
            <div class="box-body template-div">
                <h2>Templates</h2>

                <form id='templateForm'>
                
                <input type='hidden' name='channel' value='{{ $channel->id }}'>
                           
                {{ csrf_field() }}
                
                <div class="form-group">
                    <label>Return</label>
                    <textarea name='templates[1][return]' class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>Dispatch</label>
                    <textarea name='templates[2][dispatch]' class="form-control"></textarea>
                </div>

                <button channel-id="{{ $channel->id }}"  class="btn btn-primary saveTemplate">Save</button>
                </form>


            </div>
        </div>
    </div>
    
    <div class="col-lg-6 pull-right">
        <div class="box">
            <div class="box-body provider-div">
                <h2>Channel Providers</h2>


                <div class="form-inline">
                    <div class="form-group">
                        <select id='paymentProviderSelect' class="form-control">
                            <option value="paypal">Paypal</option>
                            <option value="stripe">Stripe</option>
                            <option value="bank-transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <button channel-id="{{ $channel->id }}" class="btn btn-primary addProvider">+</button>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- /.content -->
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.js" data-turbolinks-track="true"></script>


<script type="text/javascript">


$(document).ready(function () {

    $('.test').bootstrapSwitch();
    
    $('.addProvider').on('click', function () {
     
     var channel = $(this).attr('channel-id');
     var provider = $('#paymentProviderSelect').val();
     
       $.ajax({
            type: "POST",
            url: '/admin/channels/addChannelProvider',
            data: {
            channel: channel,
                provider: provider,
                _token: '{{ csrf_token() }}'
            },
            success: function (msg) {
                $('.provider-div').prepend("<div class='alert alert-success'>Shipping rate has been updated successfully</div>");
            }
            });
    });
    
    $('.saveTemplate').on('click', function () {
    
    var channel = $(this).attr('channel-id');
    var formdata = $('#templateForm').serialize();
    
      $.ajax({
            type: "POST",
            url: '/admin/channels/saveChannelTemplate',
            data: formdata,
            success: function (msg) {
                $('.template-div').prepend("<div class='alert alert-success'>Shipping rate has been updated successfully</div>");
            }
            });
    });
    
    $('.addProduct').on('click', function () {
    
    var channel = $(this).attr('channel-id');
    var product = $('#productSelect').val();
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
            success: function (msg) {
                $('.product-div').prepend("<div class='alert alert-success'>Shipping rate has been updated successfully</div>");
            }
            });
    });
    
    $('.updateChannel').on('click', function () {
    
    var channel = $(this).attr('channel-id');
    var formdata = $('#channelForm').serialize();
    
      $.ajax({
            type: "POST",
            url: '/admin/channels/update',
            data: formdata,
            success: function (msg) {
                $('.channel-div').prepend("<div class='alert alert-success'>Shipping rate has been updated successfully</div>");
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
            }
        });
    });
});
</script>
@endsection

