@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <div class="box">
        <form action="{{ route('admin.courier-rates.update', $courier->id) }}" method="post" class="form">
            <div class="box-body">
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="put">
                <div class="form-group">
                    <label for="courier">Courier</label>
                    <select name="courier" id="courier" class="form-control select2">
                        <option value="">--Select--</option>
                        @foreach($couriers as $objCourier)
                        @if($objCourier->id == $courier->courier)
                        <option selected="selected" value="{{ $objCourier->id }}">{{ $objCourier->name }}</option>
                        @else
                        <option value="{{ $objCourier->id }}">{{ $objCourier->name }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="channel">Channel</label>
                    <select name="channel" id="channel" class="form-control select2">
                        <option value="">--Select--</option>
                        @foreach($channels as $channel)
                        @if($channel->id == $courier->channel)
                        <option selected="selected" value="{{ $channel->id }}">{{ $channel->name }}</option>
                        @else
                        <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="range_from">Range From</label>
                    <div class="input-group">
                        <input type="text" name="range_from" id="range_from" placeholder="Range From" class="form-control" value="{{ $courier->range_from }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="range_to">Range To</label>
                    <div class="input-group">
                        <input type="text" name="range_to" id="range_to" placeholder="Range To" class="form-control" value="{{ $courier->range_to }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="cost">Cost</label>
                    <div class="input-group">
                        <input type="text" name="cost" id="cost" placeholder="Cost" class="form-control" value="{{ $courier->cost }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="country">Country </label>
                    <select name="country" id="country" class="form-control">
                        @foreach($countries as $country)
                        @if($country->id == $courier->country)
                        <option selected="selected" value="{{ $country->id }}">{{ $country->name }}</option>
                        @else
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <!--                <div class="form-group">
                                    @include('admin.shared.status-select', ['status' => $courier->status])
                                </div>-->
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <div class="btn-group">
                    <a href="{{ route('admin.courier-rates.index') }}" class="btn btn-default">Back</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
    <!-- /.box -->

</section>
<!-- /.content -->
@endsection
