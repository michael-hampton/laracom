
@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <div class="box">
        <form action="{{ route('admin.returns.store') }}" method="post" class="form" enctype="multipart/form-data">
            <div class="box-body">
                {{ csrf_field() }}
                
                @foreach($items as $item)
                <div class='form-inline'>
                <div class="form-group">
                        <label class='sr-only' for="address_2">Quantity</label>
                        <input type="text" name="lines[{{$item->id}}][quantity]" id="quantity" placeholder="Quantity" class="form-control" value="{{ old('quantity') }}">
                    </div>
                    
                    <div class="form-group">
                        <label class='sr-only' for="address_2">reason </label>
                        <select name="lines[{{$item->id}}][reason]" id="reason" class="form-control">
                                        @foreach($reason as $reason)
                                <option value="{{ $reason }}">{{ $reason }}</option>
                            @endforeach
                    </select>
                    </div>
                    </div>
                    @endforeach

                <input type="hidden" name="order_id" id="order_id"  value="1">
                
                <div class="form-group">
                    <label for="alias">Condition <span class="text-danger">*</span></label>
                    <select name="condition" id="condition" class="form-control">
                                        @foreach($conditions as $condition)
                                <option value="{{ $condition }}">{{ $condition }}</option>
                            @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="address_1">Resolution <span class="text-danger">*</span></label>
                    <select name="resolution" id="resolution" class="form-control">
                                        @foreach($resolutions as $resolution)
                                <option value="{{ $resolution }}">{{ $resolution }}</option>
                            @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="address_2">Status </label>
                    <select name="status" id="status" class="form-control">
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
    <!-- /.box -->

</section>
<!-- /.content -->
@endsection
