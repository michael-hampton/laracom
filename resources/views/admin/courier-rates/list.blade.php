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
