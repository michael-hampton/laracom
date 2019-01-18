@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">

    @include('layouts.errors-and-messages')

    <form action="{{ route('admin.courier-rates.search') }}" method="post" id="admin-search">
        
         {{ csrf_field() }}

        <div class="row">
            <div class="form-group col-lg-2">
                <label for="channel">Channel</label>
                <select name="channel" id="channel" class="form-control select2">
                    <option value="">--Select--</option>
                    @foreach($channels as $channel)
                    <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-lg-2">
                <label for="country">Country </label>
                <select name="country" id="country" class="form-control">
                    <option value="">--Select--</option>
                    @foreach($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </select>
            </div>
            <button style="margin-top:26px;" type="submit" class="btn btn-primary">Search</button>
            
        </div>

    </form>
    
    <div class="box">
        <form action="{{ route('admin.courier-rates.store') }}" method="post" class="form">
            <div class="box-body">
                {{ csrf_field() }}
                
                <div class='inline-form'>
                <div class="form-group col-lg-3">
                    <label for="courier">Courier</label>
                    <select name="courier" id="courier" class="form-control select2">
                        <option value="">--Select--</option>
                        @foreach($couriers as $courier)
                        <option @if(old('courier') == $courier->id) selected="selected" @endif value="{{ $courier->id }}">{{ $courier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- <div class="form-group">
                    <label class='sr-only' for="channel">Channel</label>
                    <select name="channel" id="channel" class="form-control select2">
                        <option value="">--Select--</option>
                        @foreach($channels as $channel)
                        <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div> -->

                <div class="form-group col-lg-2">
                    <label class='sr-only' for="range_from">Range From</label>
                    <div class="input-group">
                        <input type="text" name="range_from" id="range_from" placeholder="Range From" class="form-control" value="{{ old('range_from') }}">
                    </div>
                </div>

                <div class="form-group col-lg-2">
                    <label class='sr-only' for="range_to">Range To</label>
                    <div class="input-group">
                        <input type="text" name="range_to" id="range_to" placeholder="Range To" class="form-control" value="{{ old('range_to') }}">
                    </div>
                </div>

                <div class="form-group col-lg-2">
                    <label class='sr-only' for="cost">Cost</label>
                    <div class="input-group">
                        <input type="text" name="cost" id="cost" placeholder="Cost" class="form-control" value="{{ old('cost') }}">
                    </div>
                </div>

                <div class="form-group col-lg-3">
                    <label class='sr-only' for="country">Country </label>
                    <select name="country" id="country" class="form-control">
                        @foreach($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <div class="btn-group">
                    <a href="{{ route('admin.courier-rates.index') }}" class="btn btn-default">Back</a>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </div>

    <!-- Default box -->
    @if($couriers)
    <div class="box">
        <div class="box-body">
            <h2> <i class="fa fa-truck"></i> Couriers</h2>
            <table class="table">
                <thead>
                    <tr>
                        <td class="col-md-2">Courier</td>
                        <td class="col-md-2">Country</td>
                        <td class="col-md-2">Channel</td>
                        <td class="col-md-2">From</td>
                        <td class="col-md-1">To</td>
                        <td class="col-md-1">Cost</td>
                        <td class="col-md-3">Actions</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($couriers as $courier)
                    <tr>
                        <td>{{ $courier->courier_name }}</td>
                        <td>{{ $courier->country }}</td>
                        <td>{{ $courier->channel_name }}</td>
                        <td>{{ $courier->range_from }}</td>
                        <td>{{ $courier->range_to }}</td>
                        <td>{{ $courier->cost }}</td>
<!--                                <td>
                            @include('layouts.status', ['status' => $courier->is_free])
                        </td>-->
                        <!-- <td>@include('layouts.status', ['status' => $courier->status])</td> -->
                        <td>
                            <form action="{{ route('admin.courier-rates.destroy', $courier->id) }}" method="post" class="form-horizontal">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="delete">
                                <div class="btn-group">
                                    <a href="{{ route('admin.couriers.edit', $courier->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                    <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Delete</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /.box -->
    @endif

</section>
<!-- /.content -->
@endsection
