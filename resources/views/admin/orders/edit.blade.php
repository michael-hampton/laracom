@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <!-- Default box -->
    <div class="box">
        <div class="box-header">
            <div class="row">
                <div class="col-md-6">
                    <h2>
                        <a href="{{ route('admin.customers.show', $customer->id) }}">{{$customer->name}}</a> <br />
                        <small>{{$customer->email}}</small> <br />
                        <small>reference: <strong>{{$order->reference}}</strong></small>
                    </h2>
                </div>
                <div class="col-md-1">
                    <a href="{{route('admin.orders.invoice.generate', $order['id'])}}">Download Invoice</a>
                </div>

                <div class="col-md-1">
                    <a href="#" class="do-refund" order-id="{{ $order->id }}">
                    <span class='glyphicon glyphicon-transfer'></span>
                    </a>
                </div>

                <div class="col-md-1">
                    <a href="{{route('admin.orders.cloneOrder', $order['id'])}}" class="do-clone" order-id="{{ $order->id }}">
                    <span class='glyphicon glyphicon-flash'></span>
                    </a>
                </div>

                <div class="col-md-1">
                    <a href="#" class="do-swap">
                    <span class='glyphicon glyphicon-retweet'></span>
                    </a>
                </div>

                <div class="col-md-1">
                    <a href="#" class="cancel-order" order-id="{{ $order->id }}">
                    <span class='glyphicon glyphicon-trash'></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="box">
        <div class="box-body">
            <h4> <i class="fa fa-shopping-bag"></i> Order Information</h4>
            <table class="table">
                <thead>
                    <tr>
                        <td class="col-md-3">Date</td>
                        <td class="col-md-3">Customer</td>
                        <td class="col-md-3">Channel</td>
                        <td class="col-md-3">Payment</td>
                        <td class="col-md-3">Status</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ date('M d, Y h:i a', strtotime($order['created_at'])) }}</td>
                        <td><a href="{{ route('admin.customers.show', $customer->id) }}">{{ $customer->name }}</a></td>
                        <td><a href="{{ route('admin.customers.show', $customer->id) }}">{{ $order->channel }}</a></td>
                        <td><strong>{{ $order['payment'] }}</strong></td>
                        <td>
                            <form action="{{ route('admin.orders.update', $order->id) }}" method="post">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="put">
                                <label for="order_status_id" class="hidden">Update status</label>
                                <input type="text" name="total_paid" class="form-control" placeholder="Total paid" style="margin-bottom: 5px; display: none" value="{{ old('total_paid') ?? $order->total_paid }}" />
                                <div class="input-group">
                                    <select name="order_status_id" id="order_status_id" class="form-control select2">
                                        @foreach($statuses as $status)
                                        <option @if($currentStatus->id == $status->id) selected="selected" @endif value="{{ $status->id }}">{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-btn"><button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-primary">Update</button></span>
                                </div>
                            </form>
                        </td>
                    </tr>
                </tbody>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="bg-warning">Subtotal</td>
                        <td class="bg-warning">{{ $order['total_products'] }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="bg-warning">Tax</td>
                        <td class="bg-warning">{{ $order['tax'] }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="bg-warning">Discount</td>
                        <td class="bg-warning">{{ $order['discounts'] }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="bg-success text-bold">Order Total</td>
                        <td class="bg-success text-bold">{{ $order['total'] }}</td>
                    </tr>
                    @if($order['total_paid'] != $order['total'])
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="bg-danger text-bold">Total paid</td>
                        <td class="bg-danger text-bold">{{ $order['total_paid'] }}</td>
                    </tr>
                    @endif
                    
                        @if($order['amount_refunded'] > 0)
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="bg-danger text-bold">Total refunded</td>
                        <td class="bg-danger text-bold">{{ $order['amount_refunded'] }}</td>
                    </tr>
                    @endif
                    
                </tbody>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
    @if($order)
    @if(($order->payment == 'bank transfer' && 
    strtotime($order['created_at']) < strtotime('-30days') && 
    $order->total != $order->total_paid) || 
    ($order->payment != 'bank transfer' &&
    $order->total != $order->total_paid))
    <p class="alert alert-danger">
        Ooops, there is discrepancy in the total amount of the order and the amount paid. <br />
        Total order amount: <strong>{{ config('cart.currency') }} {{ $order->total }}</strong> <br>
        Total amount paid <strong>{{ config('cart.currency') }} {{ $order->total_paid }}</strong>
    </p>

    @endif
    <div class="box">
        @if(!$items->isEmpty())
        <div class="box-body">
            <h4> <i class="fa fa-gift"></i> Items</h4>
            
            <form>
            <div class="form-row">
    <div class="form-group col-md-3">
      <label for="inputCity">City</label>
      <input type="text" class="form-control" id="inputCity">
    </div>
    <div class="form-group col-md-3">
      <label for="inputState">State</label>
      <select id="inputState" class="form-control">
        <option selected>Choose...</option>
        <option>...</option>
      </select>
    </div>
    <div class="form-group col-md-3">
      <label for="inputZip">Zip</label>
      <input type="text" class="form-control" id="inputZip">
    </div>
  </div>
  
  <div class="form-row">
    <div class="form-group col-md-3">
      <label for="inputCity">City</label>
      <input type="text" class="form-control" id="inputCity">
    </div>
    <div class="form-group col-md-3">
      <label for="inputState">State</label>
      <select id="inputState" class="form-control">
        <option selected>Choose...</option>
        <option>...</option>
      </select>
    </div>
    <div class="form-group col-md-3">
      <label for="inputZip">Zip</label>
      <input type="text" class="form-control" id="inputZip">
    </div>
  </div>
  
  <div class="form-row">
    <div class="form-group col-md-3">
      <label for="inputCity">City</label>
      <input type="text" class="form-control" id="inputCity">
    </div>
    <div class="form-group col-md-3">
      <label for="inputState">State</label>
      <select id="inputState" class="form-control">
        <option selected>Choose...</option>
        <option>...</option>
      </select>
    </div>
    <div class="form-group col-md-3">
      <label for="inputZip">Zip</label>
      <input type="text" class="form-control" id="inputZip">
    </div>
  </div>
  </form>
            
            
            <table class="table">
                <thead>
                <th class="col-md-2">SKU</th>
                <th class="col-md-2">Name</th>
                <th class="col-md-2">Description</th>
                <th class="col-md-2">Quantity</th>
                <th class="col-md-2">Price</th>
                <th class="col-md-2">Status</th>
                <th class="col-md-2">Actions</th>
                </thead>
                <tbody>


                    @foreach($items as $item)

                    <tr>
                        <td>{{ $item->product_sku }}</td>
                        <td>
                            @if($item->status != 8)
                            <select disabled="disabled" order-id="{{ $order->id }}" quantity="{{ $item->quantity }}" line-id="{{ $item->id }}" class="productSelect" class="form-control">
                                @foreach($products as $product)
                                @if($product->name == $item->product_name)
                                <option selected="selected" value="{{ $product->id }}">{{ str_limit($product->name, 20, '...') }}</option>
                                @else
                                <option value="{{ $product->id }}">{{ str_limit($product->name, 20, '...') }}</option>
                                @endif
                                @endforeach
                            </select>
                            @endif

                        </td>
                        <td>{!! $item->product_description !!}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->product_price }}</td>
                        <td>
                            @if($item->status != 8)
                            <div class="input-group">
                                <select name="line_status_id" order-id="{{ $order->id }}" line-id="{{ $item->id }}" class="line_status_id form-control select2">
                                    @foreach($statuses as $status)
                                    <option @if($item->status == $status->id) selected="selected" @endif value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif;
                        </td>



                        <td>
                            @if($item->status != 8)
                            <input type="checkbox" class="cb" name="services[]" value="{{ $item->id }}">
                            @endif;
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="box">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <h4> <i class="fa fa-truck"></i> Shipping</h4>
                    <table class="table">
                        <thead>
                        <th class="col-md-3">Name</th>
                        <th class="col-md-4">Description</th>
                        <th class="col-md-5">Link</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $order->courier->name }}</td>
                                <td>{{ $order->courier->description }}</td>
                                <td>{{ $order->courier->url }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-12">
                    <h4> <i class="fa fa-map-marker"></i> Address</h4>
                    <table class="table">
                        <thead>
                        <th>Address 1</th>
                        <th>Address 2</th>
                        <th>City</th>
                        <th>Province</th>
                        <th>Zip</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $order->address->address_1 }}</td>
                                <td>{{ $order->address->address_2 }}</td>
                                <td>
                                    @if(isset($order->address->city))
                                    {{ $order->address->city }}
                                    @endif
                                </td>
                                <td>
                                    @if(isset($order->address->province))
                                    {{ $order->address->province }}
                                    @endif
                                </td>
                                <td>{{ $order->address->zip }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($voucher))
    <div class="box">
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <h4> <i class="fa fa-calculator"></i> Voucher</h4>
                    <table class="table">
                        <thead>
                        <th class="col-md-3">Voucher Code</th>
                        <th class="col-md-4">Amount Redeemed</th>
                        <th class="col-md-5">Scope</th>
                        <th class="col-md-5">Type</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $voucher->id }}</td>
                                <td>{{ $order->discounts }}</td>
                                <td>{{ $voucher->scope_type }}</td>
                                <td>{{ $voucher->amount_type }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    @endif

    <div class="box">
        @if(!$audits->isEmpty())
        <div class="box-body">
            <h4> <i class="fa fa-gift"></i> Audit</h4>

            @foreach($audits as $audit)
            <div class="row">
                {{ json_encode($audit->old_values) }}
            </div>

            <div class="row col-lg-12">
                {{ json_encode($audit->new_values) }}
            </div>
            @endforeach
        </div>
        @endif
    </div>


    <div class="box">
        <div class="box-body">
            <h4> <i class="fa fa-gift"></i> Comments</h4>

            <form action="{{ route('admin.orders.saveComment') }}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <textarea id="comment" name="comment" class="form-control"></textarea>
                <span class="input-group-btn"><button type="submit" class="btn btn-primary">Save</button></span>

            </form>

            @if (!empty($comments))
            <br><br>
            <ul class="list-group">
                @foreach($comments as $comment)

                <li class="list-group-item">

                    <p>
                        <a class="text-info" href="#">
                            @ {{ $comment->user }} </a> 
                        {{ $comment->content }}
                    </p>
                    <small class="block text-muted"><i class="fa fa-clock-o"></i> {{ $comment->created_at }}</small>
                <li>
                    @endforeach
            </ul>

            @endif
        </div>
    </div>


    <!-- /.box -->
    <div class="box-footer">
        <div class="btn-group">
            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-default">Back</a>
        </div>
    </div>
    @endif

</section>
<!-- /.content -->
@endsection
@section('js')
<script type="text/javascript">
    $(document).ready(function () {

        $('.line_status_id').on('change', function () {

            var lineId = $(this).attr('line-id');
            var orderId = $(this).attr('order-id');
            var status = $(this).val();

            $.ajax({
                type: "POST",
                url: '/admin/orderLine/updateLineStatus',
                data: {line_id: lineId,
                    order_id: orderId,
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function (msg) {
                    alert('success');
                },
                error: function(data){
                alert('unable to complete action');
                }
            });

            //$('#line-status-form').submit();

            return false;
        });

        $('.cancel-order').on('click', function () {

            var orderId = $(this).attr('order-id');

            $.ajax({
                type: "POST",
                url: '/admin/orders/destroy/' + orderId,
                data: {
                    order_id: orderId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (msg) {
                    alert(msg);
                },
                error: function(data){
                alert('unable to complete action');
                }
            });

            //$('#line-status-form').submit();

            return false;
        });

        $('.do-swap').on('click', function () {
            $('.productSelect').prop('disabled', false);
        });

        $('.do-refund').on('click', function () {

            var status = 8;
            var orderId = $(this).attr('order-id');

            if ($('.cb:checked').length == 0)
            {
                alert('Please select atleast one checkbox');
                return false;
            }

            var cb = [];
            $.each($('.cb:checked'), function () {
                cb.push($(this).val());
            });

            $.ajax({
                type: "POST",
                url: '/admin/refunds/doRefund',
                data: {
                    order_id: orderId,
                    status: status,
                    lineIds: cb,
                    _token: '{{ csrf_token() }}'
                },
                success: function (msg) {
                    alert('success');
                },
                error: function(data){
                alert('unable to complete action');
                }
            });

            return false;
        });

        $('.do-clone').on('click', function () {

            var orderId = $(this).attr('order-id');

            if ($('.cb:checked').length == 0)
            {
                alert('Please select atleast one checkbox');
                return false;
            }

            var cb = [];
            $.each($('.cb:checked'), function () {
                cb.push($(this).val());
            });

            $.ajax({
                type: "POST",
                url: '/admin/orders/cloneOrder',
                data: {
                    order_id: orderId,
                    lineIds: cb,
                    _token: '{{ csrf_token() }}'
                },
                success: function (msg) {
                    alert('success');
                },
                error: function(data){
                alert('unable to complete action');
                }
            });

            return false;
        });

        $('.productSelect').on('change', function () {

            var lineId = $(this).attr('line-id');
            var quantity = $(this).attr('quantity');
            var orderId = $(this).attr('order-id');
            var productId = $(this).val();

            $.ajax({
                type: "POST",
                url: '/admin/orderLine/update',
                data: {
                    lineId: lineId,
                    quantity: quantity,
                    orderId: orderId,
                    productId: productId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (msg) {

                },
                error: function(data){
                alert('unable to complete action');
                }
            });

            return false;
        });

        let osElement = $('#order_status_id');
        osElement.change(function () {
            if (+$(this).val() === 1) {
                $('input[name="total_paid"]').fadeIn();
            } else {
                $('input[name="total_paid"]').fadeOut();
            }
        });
    })
</script>
@endsection
