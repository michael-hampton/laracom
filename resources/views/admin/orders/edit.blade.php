@extends('layouts.admin.app')

<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">

<style>
    .glyphicon {
        font-size: 26px;
    }
</style>

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
                    <a title="Refund" href="#" class="do-refund" id='refundBtn' order-id="{{ $order->id }}">
                        <span class='glyphicon glyphicon-transfer'></span>
                    </a>
                </div>

                <div class="col-md-1">
                    <a title="Lost In Post" href="{{route('admin.orders.cloneOrder', $order['id'])}}" class="do-clone" id='lostInPostBtn' order-id="{{ $order->id }}">
                        <span class='glyphicon glyphicon-flash'></span>
                    </a>
                </div>

                <div class="col-md-1">
                    <a title="Product Swap" href="#" class="do-swap" id='replaceBtn'>
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

    <div class="refund-window" style="display:none;">

        <div class="panel panel-default">

            <div class="panel-heading block_title">
                <h3>Refund Order Lines</h3>
            </div>

            <div class="panel-body">
                <div class="col-lg-12 col-md-8 refund-help">
                    <p class="message">Please select the order lines you wish to refund by clicking the tick box on the right hand side of the order line.</p>

                </div>

                <div class="col-lg-12">
                    <button type="button" class="btn btn-primary koms-submit-button" id="continue-refund">
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Continue
                    </button>
                    <button type="button" class="btn btn-danger koms-cancel-button" id="cancelRefundBtn">
                        <span class="glyphicon glyphicon-cross" aria-hidden="true"></span> Cancel
                    </button>
                </div>

            </div>
            <div class="response">
                <table class="table">
                    <thead>
                    <th class="col-md-2">SKU</th>
                    <th class="col-md-2">Name</th>
                    <th class="col-md-2">Description</th>
                    <th class="col-md-2">Quantity</th>
                    <th class="col-md-2">Price</th>
                    <th class="col-md-2">Actions</th>
                    </thead>
                    <tbody>


                        @foreach($items as $item)

                        <tr>
                            <td>{{ $item->product_sku }}</td>
                            <td>
                                {{$item->name}}
                            </td>
                            <td>{!! $item->product_description !!}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->product_price }}</td>




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
        </div>
    </div>

    <div class="replace-window" style="display:none">

        <div class="panel panel-default">

            <div class="panel-heading block_title">
                <h3 class="lost-inpost-title">Create RMA Order</h3>
            </div>

            <div class="panel-body swap-line">
                <div class="col-lg-12 col-md-8 response"></div>
                <div id="currentLineWrap" class="col-lg-3 col-md-2">
                    <h3>Current Products</h3>
                    
                    @foreach($items as $count => $item)


                        <div class="current-line-ref btn btn-primary btn-outline {{ if($count == 0) 'active' @endif  }}" data-line-ref="{{ $item->id }}" data-product-code="{{ $item->product_sku }}" data-warehouse-ref ="KW" >
                            @if ($item->free_stock > 0 || $item->reserve_stock > 0)
                                <img src="/images/accept.png" />
                           @else
                                <img alt="No stock information available " title="No stock information available" src="/images/exclamation-point.png" />
                           @endif;
                            <div class="product-code">{{ $item->product_sku }}</div>
                            <div class="product-title">{{ $item->product_name }}</div>
                        </div>
                     @endforeach;
                </div>

                <div id="searchBoxWrapper" class="col-lg-4 col-md-2">
                    <label for="freeTextLostinPost">Replace to...</label>
                    <input type="text" placeholder="Start typing to find a swappable product" class="form-control" data-channel="EEA/3/14" name="freeTextLostinPost" id="freeTextLostinPost">
                    <p class="no-products"></p>
                    <h4 class="title">Notice: Product codes may ONLY contain "a-z 0-9 - _"</h4>
                    <input type="hidden" name="channel" id="channel" value="{{$order->channel}}">
                    <input type="hidden" name="current-line" id="current-line" value="">
                    <input type="hidden" name="warehouse-ref" id="warehouse-ref" value="">
                </div>

                <div class="selected-for-swap col-lg-2 col-md-2">
                    <div class="h4 selected-product-code"></div>
                    <div class="selected-product-title"><p></p></div>
                    <div class="selected-stock-lvl"></div>
                    <div class="selected-image"></div>
                    <input type="hidden" name="product-title" class="product-title" value="">
                    <input type="hidden" name="warehouse-ref" id="warehouse-ref" value="">
                    <input type="hidden" name="line-status" class="line-status" value="">
                    <input type="hidden" name="product-code" class="product-code" value="">
                    <input type="hidden" name="product-image" class="product-image" value="">
                    <input type="hidden" name="freestock" class="freestock" value="">
                    <input type="hidden" name="product-rrp" class="product-rrp" value="">
                    <input type="hidden" name="product-std-cost" class="product-std-cost" value="">
                    <button id="replaceProduct" class="btn btn-primary koms-submit-button">Swap To This</button>
                </div>

                <div id="saveProductReplacementWrapper" class="col-lg-3 col-md-2">

                    <div class="swapped-product">
                        <h3>New order content</h3>
                    </div>

                    <form id="newOrder">
                        <div class="replaced-products"></div>
                    </form>
                    <div id="rma-delivery-select">
                        <div class="col-sm-11 input-group input-group-sm pull-right">
                            <span class="input-group-addon order-details-label">Select Delivery Code:</span>
                            <select class="form-control" name="courier_id" id="onlyRMADeliveryDropDown2">
                              @foreach($couriers as $courier)
                                    <option @if(old('courier') == $courier->id) selected="selected" @endif value="{{ $courier->id }}">{{ $courier->name }}</option>
                                    @endforeach;
                            </select>
                        </div>
                    </div>
                    <button id="createNewOrder" class="btn btn-primary koms-submit-button pull-right" action="CreateOrder">Create Order</button>
                    <button id="cancelReplace" class="btn btn-danger koms-cancel-button pull-right">Cancel</button>
                    <i id="createOrderSpinner" class="fa fa-circle-o-notch fa-spin pull-right icon-btn bulk-download-spinner" hidden=""></i>
                </div>
            </div>
        </div>
    </div>

    <div style="display:none;" class="swap-window">

        <div class="panel panel-default">

            <div class="panel-heading block_title">
                <h3 class="product-swap-title">Swap Products</h3>
            </div>

            <div class="panel-body swap-line">
                <div class="col-lg-12 col-md-8 response"></div>
                <div id="currentLineWrap" class="col-lg-3 col-md-2">
                    <h3>Current Products</h3>
                    @foreach($items as $item)

                         <div class="current-line-ref btn btn-primary btn-outline {{ @if($count == 0) 'active' @endif }}" data-line-ref="{{ $item->id }}" data-product-code="{{ $item->product_sku
                    }}"  data-warehouse-ref ="KW"
                         data-product-title="{{ $item->product_name }}" data-product-rrp="{{ $item->product_price }}" data-product-cost="{{ $item->product_price }}"
                         data-line-quantity="{{ $item->quantity }}" data-line-status="{{ $item->status }}">
                        @if ($item->free_stock > 0 || $item->reserved_stock > 0)
                            <img src="/images/accept.png" />
                        @else;
                            <img alt="No stock information availabe " title="No stock information available" src="/images/exclamation-point.png" />
                       @endif;
                        <div class="product-code">{{ $item->product_sku }}</div>
                        <div class="product-title">{{ $item->product_name }}</div>
                    </div>
                    @endforeach;

                </div>

                <div id="searchBoxWrapper" class="col-lg-4 col-md-2">
                    <h3>Replace to...</h3>
                    <input type="text" placeholder="Start typing to find a swappable product" class="form-control" data-channel="EEA/3/14" name="freeTextLostinPost" id="SwapFinder">
                    <input type="hidden" name="channel" id="channel" value="{{$order->channel}}">
                    <input type="hidden" name="current-line" id="current-line" value="">
                    <p class="no-products"></p>
                    <h4 class="title">Notice: Product codes may ONLY contain "a-z 0-9 - _"</h4>
                </div>

                <div class="selected-for-swap col-lg-2 col-md-2">
                    <div class="h4 selected-product-code"></div>
                    <div class="selected-product-title"><p></p></div>
                    <div class="selected-stock-lvl"></div>
                    <div class="selected-image"></div>
                    <input type="hidden" name="product-title" class="product-title" value="">
                    <input type="hidden" name="warehouse-ref" id="warehouse-ref" value="">
                    <input type="hidden" name="product-code" class="product-code" value="">
                    <input type="hidden" name="freestock" class="freestock" value="">
                    <input type="hidden" name="product-image" class="product-image" value="">
                    <input type="hidden" name="product-rrp" class="product-rrp" value="">
                    <input type="hidden" name="product-std-cost" class="product-std-cost" value="">
                    <button id="swapToSelectedProduct" class="btn btn-primary koms-submit-button">Swap To This</button>
                </div>

                <div id="saveProductReplacementWrapper" class="col-lg-3 col-md-2">

                    <div class="swapped-product">
                        <h3>Products to be swapped</h3>
                    </div>

                    <form id="newOrder">
                        <div class="swapped-products"></div>
                        <i style="display:none;" class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
                        <button id="swap-products" class="btn btn-primary koms-submit-button pull-right">Swap Products</button>
                        <button id="cancel-swap" class="btn btn-danger koms-cancel-button pull-right">Cancel</button>
                    </form>
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


    <div class="box">
        @if(!$items->isEmpty())
        <div class="box-body">
            <h4> <i class="fa fa-gift"></i> Items</h4>

            <form id="linesForm">
                {{ csrf_field() }}

                @foreach($items as $count => $item)
       <div data-line-ref="1">
                <input type="hidden" name='form[{{$count}}][line_id]' value='{{$item->id}}'>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="product_name">Product</label><br>
                        {{$item->product_name}}
                    </div>

                    <div class="form-group col-md-6">
                        <label for="inputState">Description</label><br>
                        {{$item->product_description}}
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="inputCity">Tote</label>
                        <input type="text" value="{{$item->tote}}" placeholder='Tote' class="form-control" id="tote" name='form[{{$count}}][tote]'>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="inputState">Sage Ref</label>
                        <input type="text" value="{{$item->sage_ref}}" placeholder='Sage Ref' class="form-control" id="sage_ref" name='form[{{$count}}][sage_ref]'>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="inputZip">Picklist Ref</label>
                        <input value="{{$item->picklist_ref}}" type="text" placeholder='Picklist Ref' class="form-control" id="picklist_ref" name='form[{{$count}}][picklist_ref]'>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="inputZip">Warehouse</label>
                        <select id="warehouse" name='form[{{$count}}][warehouse]' class="form-control">
                            <option>Choose...</option>
                            <option value='KW' @if($item->warehouse == 'KW') selected="selected" @endif>KW</option>
                            <option value='RW' @if($item->warehouse == 'RW') selected="selected" @endif>RW</option>
                        </select>
                    </div>

                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="inputCity">Status</label>

                        <select id="status" name='form[{{$count}}][status]' class="form-control">
                            @foreach($statuses as $status)
                            <option @if($item->status == $status->id) selected="selected" @endif value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="inputState">Delivery Code</label>
                        <select id="courier_id" name='form[{{$count}}][courier_id]' class="form-control">
                            <option>Choose...</option>
                              @foreach($couriers as $courier)
                                    <option @if($item->courier_id == $courier->id) selected="selected" @endif value="{{ $courier->id }}">{{ $courier->name }}</option>
                                    @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="inputZip">Tracking Code</label>
                        <input type="text" value="{{$item->tracking_code}}" class="form-control" id="tracking_code" placeholder='Tracking code' name='form[{{$count}}][tracking_code]'>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="inputZip">Dispatch Date</label>
                        <input type="text" disabled class="form-control" id="dispatch_date" name='dispatch_date' value='{{$item->dispatch_date}}'>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="inputCity">Reserved Stock</label>
                        <input type="text" class="form-control" disabled='disabled' id="reserved_stock" name='reserved_stock'>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="inputState">Stock Available</label>
                        <input type="text" class="form-control" disabled='disabled' id="stock_availiable" name='stock_availiable'>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="inputZip">Quantity</label>
                        <input value="{{$item->quantity}}" type="text" class="form-control" disabled='disabled' id="quantity" name='quantity'>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="inputZip">Price</label>
                        <input value="{{$item->product_price}}"type="text" class="form-control" disabled='disabled' id="price" name='price'>
                    </div>
                </div>
                </div>
                @endforeach;

                <button type='submit' id='SaveOrder' class='pull-right btn btn-primary'>Save</button>
            </form>

        </div>
        @endif;
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

</section>
<!-- /.content -->
@endsection

@section('js')
<script
    src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
    integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
crossorigin="anonymous"></script>


<script type="text/javascript">
                                        $(document).ready(function () {

                                            // Bind click event to close swap window
                                            $(document).on("click", "#cancel-swap", function (e) {
                                                e.preventDefault();
                                                $('body').removeClass('product-swap');
                                                location.reload();
                                            });

// Bind click event to lost in post button
                                            $(document).on("click", "#cancelReplace", function (e) {
                                                e.preventDefault();
                                                $('body').removeClass('lost-in-post');
                                                $('#order-details-refresh').trigger('click');
                                            });


                                            $('#SaveOrder').on('click', function () {

                                                var data = $('#linesForm').serialize();

                                                $.ajax({
                                                    type: "POST",
                                                    url: '/admin/orderLine/updateLineStatus',
                                                    data: data,
                                                    success: function (msg) {
                                                        alert('success');
                                                    },
                                                    error: function (data) {
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
                                                    error: function (data) {
                                                        alert('unable to complete action');
                                                    }
                                                });

                                                //$('#line-status-form').submit();

                                                return false;
                                            });

                                            $('.do-swap').on('click', function () {
                                                $('.productSelect').prop('disabled', false);
                                            });

                                            // Bind click event to refund button
                                            $(document).on("click", "#refundBtn", function () {
                                                preRefundCheck();
                                                var orderLineTicks = $('.orderline-refund i');
                                                orderLineTicks.on('click', function () {
                                                    $(this).removeClass('pulsing').addClass('selected');
                                                    $('.refund-window #continue-refund').attr('disabled', false).addClass('btn-success');
                                                });
                                            });

                                            // Bind click event to replace order button
                                            $(document).on("click", "#replaceBtn", function () {
                                                var firstLineRef = $('#currentLineWrap .active').attr('data-line-ref');
                                                alert('a ' + firstLineRef);
                                                $('#searchBoxWrapper #current-line').val(firstLineRef);

                                                initProductAutoComplete('#SwapFinder');
                                                $('body').removeClass('lost-in-post');
                                                $('body').addClass('product-swap');
                                                $('.swap-window').slideDown();
                                            });

// Bind click event on the current product to swap
                                            $(document).on('change', '.replace-window #currentLineWrap .current-line-ref', function (e) {
                                                var line = $(this).prev();
                                                console.log(line);

                                                var lineRef = $(this).attr('data-line-ref');
                                                alert('lexie ' + lineRef);
                                                var newOrder = $('#newOrder').find('div[data-line-ref="' + lineRef + '"]');
                                                line.toggleClass('removed');
                                                newOrder.toggleClass('removed');
                                                var allLines = $('.current-line-ref');
                                                allLines.removeClass('active');
                                                newOrder.removeClass('active');

                                                if (line.hasClass('removed')) {
                                                    line.removeClass('active');
                                                    newOrder.removeClass('active');
                                                } else {
                                                    line.addClass('active');
                                                    newOrder.addClass('active');
                                                }
                                            });

// Bind click event on the current product to swap
                                            $(document).on('click', '.replace-window .current-line-ref', function (e) {
                                                var lineRef = $(this).attr('data-line-ref');
                                                
                                                alert('b ' + lineRef);
                                                $('#freeTextLostinPost').attr('disabled', false).val('');
                                                $('#searchBoxWrapper #current-line').val(lineRef);
                                                var newOrder = $('#newOrder').find('div[data-line-ref="' + lineRef + '"]');
                                                var allLines = $('.current-line-ref');

                                                allLines.not($(this)).removeClass('active');
                                                newOrder.not($(this)).removeClass('active');

                                                $(this).addClass('active');
                                                newOrder.toggleClass('active');
                                            });

// Bind click event on the current product to swap
                                            $(document).on('click', '.swap-window .current-line-ref', function (e) {
                                                var lineRef = $(this).attr('data-line-ref');
                                                alert('c ' + lineRef);
                                                $('#freeTextLostinPost').attr('disabled', false);
                                                $('#SwapFinder').val('');
                                                $('#searchBoxWrapper #current-line').val(lineRef);
                                                var allLines = $('.current-line-ref');
                                                allLines.not($(this)).removeClass('active');
                                                $(this).addClass('active');
                                            });

// Bind click event to the Lost in post btn
                                            $(document).on("click", "#replaceProduct", function (e) {
                                                e.preventDefault();
                                                var lineRef = $('#searchBoxWrapper #current-line').val();
                                                alert('d ' + lineRef);
                                                replaceProductInOrder(lineRef);
                                            });

// Bind click event on the product swap to btn
                                            $(document).on("click", "#swapToSelectedProduct", function (e) {
                                                e.preventDefault();
                                                var lineRef = $('#searchBoxWrapper #current-line').val();
                                                swapProductInOrder(lineRef);
                                            });

// Bind click event on the submit new order
                                            $(document).on("click", "#createNewOrder", function (e) {
                                                e.preventDefault();
                                                $(this).attr("disabled", "disabled");
                                                var type = "swap";
                                                if ($('.product-check').css('display') == 'none') {
                                                    var type = "lost";
                                                }
                                                $('#createOrderSpinner').fadeIn(600);
                                                createNewOrder(type);

                                            });

                                            $(document).on("click", "#swap-products", function (e) {
                                                e.preventDefault();
                                                submitProductSwap();
                                            });

                                            // Bind click event to lost in post button
                                            $(document).on("click", "#lostInPostBtn", function () {
                                                $('#createOrderSpinner').hide();
                                                var firstLineRef = $('#currentLineWrap .active').attr('data-line-ref');
                                                alert('e ' + firstLineRef);
                                                var wmsWarehouseRef = $('#currentLineWrap .active').attr('data-warehouse-ref');
                                                $('#searchBoxWrapper #current-line').val(firstLineRef);
                                                $('#searchBoxWrapper #warehouse-ref').val(wmsWarehouseRef);

                                                alert('Mike');

                                                initProductAutoComplete('#freeTextLostinPost');
                                                $('body').removeClass('product-swap');
                                                $('body').addClass('lost-in-post');
                                                $('.replace-window').slideDown();
                                                /*        $('#currentLineWrap .current-line-ref').*/

                                                return false;
                                            });


                                            $('#continue-refund').on('click', function () {

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
                                                    error: function (data) {
                                                        alert('unable to complete action');
                                                    }
                                                });

                                                return false;
                                            });

                                            $('.test1').on('click', function () {

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
                                                    error: function (data) {
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
                                                    error: function (data) {
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

                                        $('#cancelRefundBtn').on('click', function () {
                                            $('.refund-window').slideUp();
                                            $('.refund-help').fadeOut();
                                            $('.orderline-refund').addClass("hide-me");
                                        });

                                        function preRefundCheck() {
                                            $('.refund-window').slideDown();
                                            $('.refund-help').fadeIn();
                                            $('.orderline-refund').removeClass("hide-me");

                                            //var orderLineTicks = $('.orderline-refund i');
                                            //orderLineTicks.addClass('pulsing');
                                        }

                                        function initProductAutoComplete(selector) {
                                            var $ele = $(selector);
                                            var channelCode = $ele.attr('data-channel');

                                            // Init autocomplete swap product finder
                                            $ele.autocomplete({
                                                minLength: 0,
                                                // Get and format data for other products on the same channel
                                                source: function (request, response) {

                                                    var pattern = new RegExp(/^[a-zA-Z0-9\-_]+/);
                                                    var arrData = {
                                                        product_code: $ele.val().toUpperCase(),
                                                        channelCode: channelCode,
                                                        _token: '{{ csrf_token() }}'
                                                    };
                                                    var strUrl = "/admin/products/getProductAutoComplete/get";

                                                    if ($ele.val().match(pattern)) {

                                                        var data = [];
                                                        $.ajax({
                                                            type: "POST",
                                                            url: strUrl,
                                                            data: arrData,
                                                            success: function (search) {
                                                                var search = search;
                                                                if (search == false) {
                                                                    //$('#order-details-update-error').html(handleAccessDenied('message')).show().delay(5000).fadeOut();
                                                                    $('.swap-window').slideUp();
                                                                    return false;
                                                                }
                                                                search = $.parseJSON(search);

                                                                if (search.results.length > 0) {

                                                                    $.each(search.results, function (ind, val) {

                                                                        data.push({
                                                                            label: val.sku + " - " + val.description + " - "
                                                                                    + val.warehouse,
                                                                            value: val.sku,
                                                                            product: {
                                                                                product_code: val.sku,
                                                                                product_title: val.description,
                                                                                product_id: val.id,
                                                                                rrp: val.price,
                                                                                freestock: val.quantity,
                                                                                warehouse: 'KW',
                                                                                image: val.url
                                                                            }
                                                                        });
                                                                    });
                                                                    response(data);
                                                                    $(".no-products").html('');
                                                                } else {
                                                                    $(".no-products").html('');
                                                                    $(".no-products").append('<h4 class="title">There are no products Found for this search</h4>');
                                                                }
                                                            }
                                                        });
                                                    } else {
                                                        $(".no-products").html('');
                                                    }
                                                },
                                                //Handle the click event on the autocomplete selection
                                                select: function (event, ui) {

                                                    console.log(ui.item.product);

                                                    $(".no-products").html('');
                                                    $('.selected-for-swap .selected-product-code').html(ui.item.product.product_code);
                                                    $('.selected-for-swap .selected-product-title').html(ui.item.product.product_title);
                                                    $('.selected-for-swap .selected-stock-lvl').html("Stock Level: " + ui.item.product.freestock);
                                                    // hidden inputs
                                                    $('.selected-for-swap .product-code').val(ui.item.product.product_code);
                                                    $('.selected-for-swap .product-title').val(ui.item.product.product_title);
                                                    $('.selected-for-swap .freestock').val(ui.item.product.freestock);
                                                    $('.selected-for-swap #warehouse-ref').val(ui.item.product.warehouse);
                                                    $('.selected-for-swap .product-rrp').val(ui.item.product.rrp);
                                                    $('.selected-for-swap .selected-image').html(
                                                            "<img src='" + ui.item.product.image + "' alt='" + ui.item.description + "' />"
                                                            );
                                                    $('.selected-for-swap').slideDown();
                                                },

                                                open: function () {
                                                    $('.ui-autocomplete').css({'position': 'fixed', 'border': 'none', 'display': 'block', 'z-index': 1000000});
                                                    $('.ui-autocomplete li').css({'margin-bottom': '1px', 'font-size': '0.8em', 'line-height': '1.4em', 'border-raduis': 'none', 'background': '#ddd', 'padding': '2px'});
                                                },
                                                close: function () {},
                                                focus: function (event, ui) {

                                                }
                                            });
                                        }

                                        function createNewOrder(type) {
                                            var strUrl = "/orders/replaceOrder";
                                            var newOrder = $('#newOrder');
                                            $.each(newOrder.children(), function (ind, val) {
                                                var value = $(val);
                                                if (value.hasClass('removed')) {
                                                    newOrder.children().eq(ind).remove();
                                                }
                                            });

                                            newOrder = newOrder.serializeArray();
                                            var customerRef = $('#order-details-content .customer-ref').text();
                                            var orderRef = $('#order-details-content .order-details').attr('data-order-ref');
                                            var dbID = $('#order-details-content .order-details').attr('data-dbid');
                                            var lastUpdated = encodeURI($('#order-details-content .order-details').attr('data-last-updated'));
                                            var delivery = $('#onlyRMADeliveryDropDown2').val();
                                            var channelCode = $('.replace-window #searchBoxWrapper #channel').val();

                                            var objXhr = $.ajax({
                                                type: "POST",
                                                url: strUrl,
                                                data: {
                                                    order: newOrder,
                                                    orderRef: orderRef,
                                                    customerRef: customerRef,
                                                    dbID: dbID,
                                                    channelCode: channelCode,
                                                    lastUpdated: lastUpdated,
                                                    delivery: delivery,
                                                    type: type
                                                },
                                                success: function (response) {
                                                    $('#createOrderSpinner').fadeOut(600);

                                                    var response = JSON.parse(response);
                                                    var strOut = "<div class='alert alert-success'>";
                                                    $.each(response.body[0], function (ind, val) {
                                                        if (ind === 'text' || ind === 'title' || ind === 'msg') {
                                                            strOut += "<p>" + val + "</p>";
                                                        }
                                                    });

                                                    strOut += '</div>';
                                                    $('.replace-window .response').html(strOut).addClass('active');

                                                    $.each(response.koms[0].details, function (responseType, val) {

                                                        $.each(val, function (dbId, detail) {

                                                            if (responseType === 'SUCCESS') {
                                                                $('.replace-window .response').append("<div class='alert alert-success'>" + detail + "</div>");
                                                            } else {

                                                                $.each(detail, function (key, value) {

                                                                    if ((key === 'generic_errors' && typeof (value) === 'object') || (key === 'extra_info' && typeof (value) === 'object')) {
                                                                        $('.replace-window .response').append("<div class='alert alert-danger'></div>");
                                                                        $.each(value, function (errorType, message) {
                                                                            $('.replace-window .response .alert-danger').append("<p>" + message + "</p>");
                                                                        });
                                                                    }
                                                                });
                                                            }
                                                        });
                                                    });
                                                }
                                            });



                                        }

                                        function replaceProductInOrder(lineRef) {
                                        
                                        alert('mike ' + lineRef);
                                        
                                            var originalProduct = $('.replace-window #currentLineWrap').find('div[data-line-ref="' + lineRef + '"]');
                                            var newProduct = $('.replace-window #currentLineWrap').find('div[data-line-ref="' + lineRef + '"]').clone();
                                            var productForSwap = $('.replace-window .selected-for-swap');
                                            var newProductCode = productForSwap.find('.product-code').val();
                                            
                                            alert(newProductCode);
                                            
                                            var newProductTitle = productForSwap.find('.product-title').val();
                                            var newProductWarehouse = productForSwap.find('#warehouse-ref').val();
                                            var newProductStatus = originalProduct.attr('data-line-status');
                                            var newProductRrp = productForSwap.find('.product-rrp').val();
                                            var newProductStdCost = productForSwap.find('.product-std-cost').val();
                                            var newOrder = $('.replace-window #newOrder');
                                            newProduct.append('<input class="kondor_product_code" name="kondor_product_code[' + lineRef + ']" type="hidden" value="' + newProductCode + '" />');
                                            newProduct.append('<input class="customer_product_title" name="customer_product_title[' + lineRef + ']" type="hidden" value="' + newProductTitle + '" />');
                                            newProduct.append('<input class="wms_warehouse_ref" name="wms_warehouse_ref[' + lineRef + ']"' + ' type="hidden"' +
                                                    ' value="' + newProductWarehouse + '" />');
                                            newProduct.append('<input class="rrp" name="rrp[' + lineRef + ']" type="hidden" value="' + newProductRrp + '" />');
                                            newProduct.append('<input class="stdCost" name="stdCost[' + lineRef + ']" type="hidden" value="' + newProductStdCost + '" />');
                                            newProduct.append('<input class="lineStatus" name="lineStatus[' + lineRef + ']" type="hidden" value="' + newProductStatus + '" />');
                                            newProduct.removeClass('active').attr('data-product-code', newProductCode).attr('data-original-product-code', originalProduct.attr('data-product-code'));
                                            newProduct.find('.product-code').html(newProductCode);
                                            newProduct.find('.product-title').html(newProductTitle);
                                            var swappedTitle = originalProduct.find('.product-code');
                                            $('.replace-window #freeTextLostinPost').val('');
                                            //~BR - lets draw the drop down - this is messy as, but without recoding the whole thing, I need to allow a Qty to be selected for the Line
                                            var Quantity = originalProduct.attr('data-line-quantity');
                                            
                                            alert(Quantity);
                                            var qtyDropdownHtml = '<br /><div class="col-sm-7 input-group input-group-sm pull-right">\n' +
                                                    '            <span class="input-group-addon order-details-label">Swap Quantity</span>\n' +
                                                    '        <select class="form-control quantity" name="quantity[' + lineRef + ']">';

                                            for (var qtyCounter = 1; qtyCounter <= Quantity; qtyCounter++) {
                                                if (Number(qtyCounter) === Number(Quantity)) {
                                                    qtyDropdownHtml += '<option value="' + qtyCounter + '" selected>' + qtyCounter + '</option>';
                                                } else {
                                                    qtyDropdownHtml += '<option value="' + qtyCounter + '">' + qtyCounter + '</option>';
                                                }
                                            }

                                            qtyDropdownHtml += '</select>' +
                                                    '        </div>';

                                            newProduct.append(qtyDropdownHtml);
                                            newProduct.appendTo('.replaced-products');
                                            swappedTitle.html(swappedTitle.text() + '<i style="margin:0 0.5em;" class="fa fa-hand-o-right" aria-hidden="true"></i>' + newProductCode);
                                            $('.replace-window .swap-line #saveProductReplacementWrapper').show(500);
                                            $('.selected-for-swap').slideUp();
                                        }

                                        function swapProductInOrder(lineRef) {
                                            var originalProduct = $('.swap-window #currentLineWrap').find('div[data-line-ref="' + lineRef + '"]');
                                            var newProduct = $('.swap-window #currentLineWrap').find('div[data-line-ref="' + lineRef + '"]').clone();
                                            var productForSwap = $('.swap-window .selected-for-swap');
                                            var newproductCode = productForSwap.find('.product-code').val();
                                            var newproductTitle = productForSwap.find('.product-title').val();
                                            
                                            alert('f ' + newproductTitle);

                                            newProduct.removeClass('active').attr('data-product-code', newproductCode).attr('data-line-ref', lineRef).attr('data-original-product-code', originalProduct.attr('data-product-code'));
                                            newProduct.find('.product-code').html(newproductCode);
                                            newProduct.find('.product-title').html(newproductTitle);
                                            newProduct.appendTo('.swapped-products');
                                            var swappedTitle = originalProduct.find('.product-code');
                                            swappedTitle.html(swappedTitle.text() + '<i style="margin:0 0.5em;" class="fa fa-hand-o-right" aria-hidden="true"></i>' + newproductCode);
                                            $('.swap-window .swap-line #saveProductReplacementWrapper').show(500);
                                            $('.selected-for-swap').slideUp();
                                        }

                                        function  submitProductSwap() {
                                            var productsForSwap = $('.swapped-products');
                                            var arrData = [];
                                            var strUrl = "/product/inOrderSwap";

                                            $('.swap-window #newOrder .fa-refresh').show();

                                            // Format data for update
                                            $.each(productsForSwap.children(), function (ind, value) {
                                                var newProductCode = $(value).attr('data-product-code');
                                                var lineRef = $(value).attr('data-line-ref');
                                                alert(lineRef);
                                                // this needs to be the lines form
                                                var lines = $('#order-details-line-container');
                                                var updateLine = lines.find('div[data-line-ref="' + lineRef + '"]');
                                                updateLine.find('.update-kondor-product-code').val(newProductCode);
                                                updateLine.find('.update-customer-product-code').val("");
                                                updateLine.find('[name="' + lineRef + '-line_status"]').append('<option value="2">Waiting Import</option>').val("2");
                                            });

                                            // Send update
                                            $.ajax({
                                                type: "POST",
                                                url: strUrl,
                                                data: {
                                                    dbid: $('.order-details').attr('data-dbid')
                                                },
                                                success: function (response) {
                                                    $('.swap-window #newOrder .fa-refresh').hide();
                                                    var response = JSON.parse(response);

                                                    if (response.http_code === 201 || response.http_code === 200) {
                                                        $.each(response.details, function (responseType, val) {

                                                            $.each(val, function (dbId, detail) {

                                                                if (responseType === 'SUCCESS') {
                                                                    $('.swap-window .response').append("<div class='alert alert-success'>" + detail + "</div>");
                                                                } else {

                                                                    $.each(detail, function (key, value) {

                                                                        if ((key === 'generic_errors' && typeof (value) === 'object') || (key === 'extra_info' && typeof (value) === 'object')) {
                                                                            $('.swap-window .response').append("<div class='alert alert-danger'></div>");
                                                                            $.each(value, function (errorType, message) {
                                                                                $('.swap-window .response .alert-danger').append("<p>" + message + "</p>");
                                                                            });
                                                                        }
                                                                    });
                                                                }
                                                            });
                                                        });
                                                    }
                                                    $('.swap-window .response').slideDown();
                                                    $('.swap-window #swap-products').hide();
                                                    $('.swap-window #cancel-swap').html('Close');
                                                    $('#SaveButtonContainer #SaveOrder').attr('disabled', true);
                                                }
                                            });
                                        }
</script>
@endsection
