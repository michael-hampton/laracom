@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">

    @include('layouts.errors-and-messages')
    <!-- Default box -->
   
    <div class="box">
        <div class="box-body">
            <h2>Backorders</h2>

            <!-- search form -->
            <div class="col-lg-12">
                <form action="{{ route('admin.orders.search') }}" method="post" id="admin-search">

                    {{ csrf_field() }}

                    <div class="row">
                        <div class="pull-left col-lg-3">
                            <div class="input-group">
                                <input type="text" name="q" class="form-control" placeholder="Customer Ref" value="{{ old('q')}}">
                            </div>
                        </div>

                        <div class="pull-left col-lg-3">
                            <div class="input-group">
                                <input type="text" name="name" class="form-control" placeholder="Customer Name" value="{{ old('q')}}">
                            </div>
                        </div>

                        <div class="pull-left col-lg-3">
                            <div class="input-group">
                                <input type="text" name="email" class="form-control" placeholder="Customer Email" value="{{ old('email')}}">
                            </div>
                        </div>

                        <div class="pull-left col-lg-3">
                            <div class="input-group">
                                <input type="text" name="voucher_code" class="form-control" placeholder="Voucher Code" value="{{ old('voucher_code')}}">
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-top: 12px;">
                        <div class="pull-left col-lg-3">
                            <div class="input-group">
                                <input type="text" name="product_name" class="form-control" placeholder="Product Name" value="{{ old('product_name')}}">
                            </div>
                        </div>

                        <div class="pull-left col-lg-2">
                            @if(!$channels->isEmpty())
                            <div class="form-group">
                                <select name="channel" id="channel" class="form-control select2">
                                    <option value="">Channel</option>
                                    @foreach($channels as $channel)
                                    <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        <div class="pull-left col-lg-2">
                            @if(!$statuses->isEmpty())
                            <div class="form-group">
                                <select name="status" id="status" class="form-control select2">
                                    <option value="">Status</option>
                                    @foreach($statuses as $status)
                                    <option @if(old('$status') == $status->id) selected="selected" @endif value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>
                    <span class="input-group-btn">
                        <button type="submit" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i> Search </button>
                    </span>
                </form>
            </div>
        <!-- /.box-body -->
        <div class="box-footer">
            {{ $orders->links() }}
        </div>
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
                            <input type="checkbox" class="cb" name="services[]" value="{{ $item->id }}">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</section>
<!-- /.content -->
@endsection

@section('js')
<script type="text/javascript">
    $(document).ready(function () {
        
        $('.do-allocation').on('click', function () {
            
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
                url: '/admin/orderLine/allocateStock',
                data: {
                    order_id: orderId,
                    status: status,
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

