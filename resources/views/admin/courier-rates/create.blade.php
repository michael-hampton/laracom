

@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <div class="box">
        <form action="{{ route('admin.courier-rates.store') }}" method="post" class="form">
            <div class="box-body">
                {{ csrf_field() }}

                <div class="form-group">
                    <label for="courier">Courier</label>
                    <select name="courier" id="courier" class="form-control select2">
                        <option value="">--Select--</option>
                        @foreach($couriers as $courier)
                        <option @if(old('courier') == $courier->id) selected="selected" @endif value="{{ $courier->id }}">{{ $courier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="channel">Channel</label>
                    <select name="channel" id="channel" class="form-control select2">
                        <option value="">--Select--</option>
                        @foreach($channels as $channel)
                        <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="range_from">Range From</label>
                    <div class="input-group">
                        <input type="text" name="range_from" id="range_from" placeholder="Range From" class="form-control" value="{{ old('range_from') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="range_to">Range To</label>
                    <div class="input-group">
                        <input type="text" name="range_to" id="range_to" placeholder="Range To" class="form-control" value="{{ old('range_to') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="cost">Cost</label>
                    <div class="input-group">
                        <input type="text" name="cost" id="cost" placeholder="Cost" class="form-control" value="{{ old('cost') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="country">Country </label>
                    <select name="country" id="country" class="form-control">
                        @foreach($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <div class="btn-group">
                    <a href="{{ route('admin.courier-rates.index') }}" class="btn btn-default">Back</a>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </div>
        </form>
    </div>
    <!-- /.box -->

</section>
<!-- /.content -->
@endsection
