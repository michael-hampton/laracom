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
                    <form action="{{ route('admin.orders.search') }}" method="post" id="admin-search">

                        {{ csrf_field() }}


                        <div style="margin-bottom:10px;">
                            <div class="input-group">
                                <input type="text" name="customer_ref" class="form-control" placeholder="Customer Ref" value="{{ old('q')}}">
                            </div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <div class="input-group">
                                <input type="text" name="customer_name" class="form-control" placeholder="Customer Name" value="{{ old('q')}}">
                            </div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <div class="input-group">
                                <input type="text" name="customer_email" class="form-control" placeholder="Customer Email" value="{{ old('email')}}">
                            </div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <div class="input-group">
                                <input type="text" name="voucher_code" class="form-control" placeholder="Voucher Code" value="{{ old('voucher_code')}}">
                            </div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <div class="input-group">
                                <input type="text" name="product_name" class="form-control" placeholder="Product Name" value="{{ old('product_name')}}">
                            </div>
                        </div>

                        <div class="pull-left">
                            @if(!$channels->isEmpty())
                            <div style="margin-bottom:10px;">
                                <select name="order_channel" id="channel" class="form-control select2">
                                    <option value="">Channel</option>
                                    @foreach($channels as $channel)
                                    <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        <div class="pull-left">
                            @if(!$couriers->isEmpty())
                            <div style="margin-bottom:10px;">
                                <select name="courier[]" multiple='multiple' id="courier" class="form-control select2">
                                    @foreach($couriers as $courier)
                                    <option @if(old('courier') == $courier->id) selected="selected" @endif value="{{ $courier->id }}">{{ $courier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        <div style="margin-bottom:10px;">
                            @if(!$statuses->isEmpty())
                            <div class="form-group">
                                <select name="order_status" id="status" class="form-control select2">
                                    <option value="">Status</option>
                                    @foreach($statuses as $status)
                                    <option @if(old('$status') == $status->id) selected="selected" @endif value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        <span class="input-group-btn">
                            <button type="submit" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i> Search </button>
                        </span>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="box">
        <div class="box-body">
            @if($orders)
            <table class="table">
                <thead>
                    <tr>
                        <td class="col-md-1">#</td>
                        <td class="col-md-3">Date</td>
                        <td class="col-md-3">Customer</td>
                        <td class="col-md-2">Courier</td>
                        <td class="col-md-2">Total</td>
                        <td class="col-md-1">Status</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                    <tr style="background-color: {{ $order->is_priority == 1 ? '#fffb9d' : '' }}">
                        <td>{{$order->id}}</td>
                        <td><a title="Show order" href="{{ route('admin.orders.show', $order->id) }}">{{ date('M d, Y h:i a', strtotime($order->created_at)) }}</a></td>
                        <td>{{$order->customer->name}}</td>
                        <td>{{ $order->courier->name }}</td>
                        <td>
                            <span class="label @if($order->total != $order->total_paid) label-danger @else label-success @endif">Php {{ $order->total }}</span>
                        </td>
                        <td><p class="text-center" style="color: #ffffff; background-color: {{ $order->status->color }}">{{ $order->status->name }}</p></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif;
        </div>



    </div>
    </div>
    

    <div class="box-footer col-lg-12">
        {{ $orders->links() }}
    </div>

</section>
<!-- /.content -->
@endsection

@section('js')
<script type="text/javascript">
    $(document).ready(function () {
    
      $('.Search').on('click', function (e) {
            href = $('#admin-search').attr('action');
            var formdata = $('#admin-search').serialize();
            $.ajax({
                type: "POST",
                url: href,
                data: formdata,
                success: function (response) {
                    $('.search-results').html(response);
                }
            });
        });
        
        $('.Search').click();
});
</script>

@endsection
