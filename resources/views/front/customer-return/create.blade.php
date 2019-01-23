
@extends('layouts.front.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <div class="box">
        <form action="{{ route('customer-returns.store') }}" method="post" class="form" enctype="multipart/form-data">
            <div class="box-body">
                {{ csrf_field() }}

                @foreach($items as $item)
                <div class='form-inline' style="margin-bottom: 12px;">

                    <div class="pull-left col-lg-2">
                        {{ $item->name }}
                    </div>

                    <div class="pull-left col-lg-2">
                        {{ $item->sku }}
                    </div>

                    <div class="pull-left col-lg-2">
                        {{ $item->price }}
                    </div>

                    <div class="pull-left col-lg-1">
                        {{ $item->quantity }}
                    </div>

                    <input type="hidden" name="lines[{{$item->id}}][line_id]" value="{{ $item->id }}">

                    <input type="checkbox" name="lines[{{$item->id}}][return]">

                    <div class="form-group" style="margin-right:10px;">
                        <label class='sr-only' for="address_2">Quantity</label>
                        <select name="lines[{{$item->id}}][quantity]" id="quantity" class="form-control">
                            <?php
                            for ($x = 1; $x <= $item->quantity; $x++) {
                                echo '<option value="' . $x . '">' . $x . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-right:10px;">
                        <label class='sr-only' for="address_2">reason </label>
                        <select name="lines[{{$item->id}}][reason]" id="reason" class="form-control">
                            <option value="">Select Reason</option>
                            @foreach($reasons as $reason)
                            <option value="{{ $reason }}">{{ $reason }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endforeach

                <input type="hidden" name="order_id" id="order_id"  value="{{ $order->id }}">
                <input type="hidden" name="customer" id="customer"  value="{{auth()->user()->id}}">



                <div class="form-group">
                    <label for="alias">Condition <span class="text-danger">*</span></label>
                    <select name="item_condition" id="condition" class="form-control">
                        <option value="">Select Condition</option>
                        @foreach($conditions as $condition)
                        <option value="{{ $condition }}">{{ $condition }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="address_1">Resolution <span class="text-danger">*</span></label>
                    <select name="resolution" id="resolution" class="form-control">
                        <option value="">Select Resolution</option>
                        @foreach($resolutions as $resolution)
                        <option value="{{ $resolution }}">{{ $resolution }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="address_2">Status </label>
                    <select name="status" id="status" class="form-control" disabled="disabled">
                        @foreach($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <div class="btn-group">
                    <a href="{{ route('admin.refunds.index') }}" class="btn btn-default">Back</a>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </div>
        </form>
    </div>

    <div class="col-lg-12">
        <h2>Terms and Conditions</h2>
        {{$terms}}
    </div>
    <!-- /.box -->

</section>
<!-- /.content -->
@endsection
