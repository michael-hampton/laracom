@extends('layouts.admin.app')

@section('content')

@include('layouts.errors-and-messages')
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
                        </div>

                        <span class="input-group-btn">
                            <button type="submit" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i> Search </button>
                        </span>

                        <input type="hidden" id="status" name="line_status" value="11">
                        <input type="hidden" id="module" name="module" value="backorders">


                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="box">
            <div class="box-body">
                @if(!$items->isEmpty())
                <div class="box-body">
                    <h4> <i class="fa fa-gift"></i> Items</h4>

                    <a href="#" class="uncheck">Uncheck</a>

                    <table class="table table-striped table-hover">
                        <thead>
                        <th class="col-md-2">Order Id</th>
                        <th class="col-md-2">Channel</th>
                        <th class="col-md-2">Order Date</th>
                        <th class="col-md-2">Customer Name</th>
                        <th class="col-md-2">Name</th>
                        <th class="col-md-2">Quantity</th>

                        <th class="col-md-2">Actions</th>
                        </thead>
                        <tbody>

                            <?php
                            foreach ($items as $item) {

                                $arrOrder = $orders[$item->order_id];

                                if (strtotime($item->created_at) < strtotime('-30 days')) {
                                    $color = 'table-danger';
                                } elseif (strtotime($item->created_at) < strtotime('-15 days')) {
                                    $color = 'table-warning';
                                } else {
                                    $color = 'table-info';
                                }
                                ?>

                                <tr class="{{ $color }}">
                                @if($customerRef !== $arrOrder['customer_ref'])
                                    <td>{{$arrOrder->id}}</td>
                                    <td>{{$arrOrder->channel->name}}</td>
                                    <td>{{$arrOrder->created_at}}</td>
                                    <td>{{$arrOrder->customer->name}}</td>
                                    @else
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    @endif
                                    <td>
                                        {{ $item->product_name }}

                                    </td>

                                    <?php
                                    $quantityAvailiable = $products[$item->product_id]['quantity'] - $products[$item->product_id]['reserved_stock'];
                                    $reservedStock = $products[$item->product_id]['reserved_stock'];
                                    $checked = $quantityAvailiable > 0 ? 'checked="checked"' : '';
                                    $disabled = $quantityAvailiable == 0 ? 'disabled="disabled"' : '';
                                    ?>

                                    <td>{{ $item->quantity }}
                                        <br>Free Stock {{$quantityAvailiable}}
                                        <br>Reserved Stock {{$reservedStock}}
                                    </td>
                                    <td>{{ $item->product_price }}</td>

                                    <td>

                                        <input type="checkbox" {{ $checked }} {{ $disabled }} class="cb" name="services[]" order-id="{{ $item->order_id }}" value="{{ $item->id }}">
                                        <i email='{{$arrOrder->customer->email}}'order-id="{{$item->order_id}}" class="fa fa-envelope-open-o open-message" aria-hidden="true"></i>
                                    </td>
                                </tr>
                                <?php
                                $customerRef = $arrOrder['customer_ref'];
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                @endif
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

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <i class="fa fa-laptop modal-icon"></i>
                <h4 class="modal-title">Messages</h4>

            </div>
            <div class="modal-body">

                <div class="existing-messages col-lg-12" style="margin-bottom: 12px; border: 1px solid #CCC;">
                    
                    @foreach($messages as $message):
                    <div style="border-bottom: 1px #CCC dotted; padding:6px;" class="col-lg-12">
                        
                        <div class="col-lg-4 pull-right">
                            {{$message->created_at}}
                        </div>
                        
                        <div class="col-lg-8 pull-right">
                             {{$message->subject}}
                        </div>
                        {{$message->body}}<br>
                    </div>
                    
                     
                    
                    @endforeach;
                </div>



                <form id='backorderForm'>
                    
                     {{ csrf_field() }}

                    <input type='hidden' id='order_id' name='order_id' value="" class='form-control'>
                    <input type='hidden' id='message_type' name='message_type' value="1" class='form-control'>
                    <input type="hidden" name="thread_id" value="{{$messages[0]->thread_id}}">
                    <input type="hidden" id='email_address' name="email_address" value="">
                    
                    <div class="form-group">
                        <label>Subject</label> 
                        <input type='text' id='subject' name='subject' class='form-control'>
                    </div>

                    <div class="form-group">
                        <label>Comment</label> 
                        <textarea id='comment' name='message' class='form-control'></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary saveBackorderForm">Save changes</button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script type="text/javascript">
    $(document).ready(function () {

        $('.saveBackorderForm').on('click', function () {
            var formdata = $('#backorderForm').serialize();


            $.ajax({
                type: "POST",
                url: '/admin/message/store',
                data: formdata,
                success: function (response) {
                    var response = JSON.parse(response);

                    if (response.http_code === 400) {

                        $('.content').prepend("<div class='alert alert-danger'></div>");


                    } else {
                        $('.content').prepend("<div class='alert alert-success'></div>");

                    }
                }
            });

        });

        $('.open-message').on('click', function () {
            $('#myModal').show();
            $('#order_id').val($(this).attr('order-id'));
            $('#email_address').val($(this).attr('email'));
        });

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

                cb[orderId].push($(this).val());
                $(this).parent().parent().addClass('toBeRemoved');
            });

            $.ajax({
                type: "POST",
                url: '/admin/orderLine/processBackorders',
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
