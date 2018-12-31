@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">

    @include('layouts.errors-and-messages')
    <!-- Default box -->
    @if($orders)
    <div class="box">
        <div class="box-body">
            <h2>Orders</h2>

            <!-- search form -->
            <div class="col-lg-12">
                <form action="{{ route('admin.orders.search') }}" method="post" id="admin-search">

                    {{ csrf_field() }}

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

                    <span class="input-group-btn">
                        <button type="submit" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i> Search </button>
                    </span>
                </form>
            </div>



            <table class="table">
                <thead>
                    <tr>
                        <td class="col-md-3">Date</td>
                        <td class="col-md-3">Customer</td>
                        <td class="col-md-2">Courier</td>
                        <td class="col-md-2">Total</td>
                        <td class="col-md-2">Status</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                    <tr style="background-color: {{ $order->is_priority == 1 ? '#fffb9d' : '' }}">
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
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
            {{ $orders->links() }}
        </div>
    </div>
    <!-- /.box -->
    @endif

</section>
<!-- /.content -->
@endsection