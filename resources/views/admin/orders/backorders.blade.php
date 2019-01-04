@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">

    @include('layouts.errors-and-messages')
    <!-- Default box -->
    @if($orders)
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
    @endif

</section>
<!-- /.content -->
@endsection
