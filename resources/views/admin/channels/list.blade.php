@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <!-- Default box -->
    @if($channels)
    <div class="box">
        <div class="box-body">
            <h2>Channels</h2>
            @include('layouts.search', ['route' => route('admin.channels.index')])
            <table class="table">
                <thead>
                    <tr>
                        <td class="col-md-2">Name</td>
                        <td class="col-md-1">Has Priority</td>
                        <td class="col-md-1">Allocate on Order</td>
                        <td class="col-md-1">Backorders Enabled</td>
                        <td class="col-md-1">Send Received Email</td>
                        <td class="col-md-1">Send Dispatched Email</td>
                        <td class="col-md-1">Logo</td>
                        <td class="col-md-1">Status</td>
                        <td class="col-md-3">Actions</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($channels as $channel)
                    <tr>
                        <td>
                            {{ $channel->name }} <br>
                            <a href="{{ route('admin.channel-prices.index', $channel->name) }}">Products</a> | 
                            <a href="{{ route('admin.vouchers.getByChannel', $channel->name) }}">Vouchers</a>
                        
                        </td>
                        <td>{{ $channel->has_priority == 1 ? 'yes' : 'no' }}</td>
                        <td>{{ $channel->allocate_on_order === 1 ? 'yes' : 'no' }}</td>
                        <td>{{ $channel->backorders_enabled === 1 ? 'yes' : 'no' }}</td>
                        <td>{{ $channel->send_received_email === 1 ? 'yes' : 'no' }}</td>
                        <td>{{ $channel->send_dispatched_email === 1 ? 'yes' : 'no' }}</td>
                        <td><img style="width:50px;" src="/storage/{{ $channel->cover }}"></td>
                        <td>@include('layouts.status', ['status' => $channel->status])</td>
                        <td>
                            <form action="{{ route('admin.channels.destroy', $channel->id) }}" method="post" class="form-horizontal">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="delete">
                                <div class="btn-group">
                                    <a href="{{ route('admin.channels.edit', $channel->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                    <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Delete</button>
<!--                                    <a href="{{ route('admin.channel-prices.index', $channel->name) }}" class="btn btn-primary btn-sm">View Products</a>-->
                                </div>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($channels instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="row">
                <div class="col-md-12">
                    <div class="pull-left">{{ $channels->links() }}</div>
                </div>
            </div>
            @endif
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
    @else
    <div class="box">
        <div class="box-body"><p class="alert alert-warning">No channels found.</p></div>
    </div>
    @endif
</section>
<!-- /.content -->
@endsection