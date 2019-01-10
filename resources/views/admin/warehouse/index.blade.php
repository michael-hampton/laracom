@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">

    @include('layouts.errors-and-messages')
    <!-- Default box -->

    <!--    <div class="box">
            <div class="box-body">
                <h2>Backorders</h2>
    
                 search form 
                <div class="col-lg-12">
                    <form action="{{ route('admin.orderLine.search') }}" method="post" id="admin-search">
    
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
    
                            <input type="hidden" id="status" name="status" value="11">
                            <input type="hidden" id="module" name="module" value="backorders">
                        </div>
                        <span class="input-group-btn">
                            <button type="submit" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i> Search </button>
                        </span>
                    </form>
                </div>
                 /.box-body 
    
            </div>-->
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
                <th class="col-md-2">Picklist Ref</th>
                <th class="col-md-2">Bin</th>
                <th class="col-md-2">Tote</th>
                <th class="col-md-2">Price</th>
                <th class="col-md-2">Status</th>
                <th class="col-md-2">Actions</th>
                </thead>
                <tbody>

                    @foreach($items as $item)

                    <?php
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
                        <td>{{ $item->quantity }}</td>
                        <td>{{$item->picklist_ref}}</td>
                        <td>A</td>
                        <td>{{$item->tote}}</td>
                        <td>{{ $item->product_price }}</td>
                        <td>{{ $item->status }}</td>

                        <td>
                            @if($item->status === 5):
                            <button class="pick" order-id="{{ $item->order_id }}" line-id="{{ $item->id }}">
                                Pick
                            </button>

                            @elseif($item->status === 16):
                            <button class="dispatch" order-id="{{ $item->order_id }}" line-id="{{ $item->id }}">
                                Dispatch
                            </button>

                            @elseif($item->status === 15):
                            <button class="pack" order-id="{{ $item->order_id }}" line-id="{{ $item->id }}">
                                Pack
                            </button>
                            @endif;
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="box-footer">
        <div class="btn-group pull-right">
            <button type="button" class="btn btn-primary do-allocation">Allocate</button>
        </div>

        {{ $items->links() }}
    </div>

</section>
<!-- /.content -->
@endsection

@section('js')
<script type="text/javascript">
    $(document).ready(function () {

        $('.pick').on('click', function () {

            var orderId = $(this).attr('order-id');
            var lineId = $(this).attr('line-id');

            $.ajax({
                type: "POST",
                url: '/admin/warehouse/pickOrder',
                data: {
                    orderId: orderId,
                    lineId: lineId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (msg) {
                    alert(msg);
                }
            });
            return false;
        });

        $('.pack').on('click', function () {

            var orderId = $(this).attr('order-id');
            var lineId = $(this).attr('line-id');

            $.ajax({
                type: "POST",
                url: '/admin/warehouse/packOrder',
                data: {
                    orderId: orderId,
                    lineId: lineId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (msg) {
                    alert(msg);
                }
            });
            return false;
        });

        $('.dispatch').on('click', function () {

            var orderId = $(this).attr('order-id');
            var lineId = $(this).attr('line-id');

            $.ajax({
                type: "POST",
                url: '/admin/warehouse/dispatchOrder',
                data: {
                    orderId: orderId,
                    lineId: lineId,
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

