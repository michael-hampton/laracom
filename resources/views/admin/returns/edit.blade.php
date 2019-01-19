@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <div class="box">
        <form action="{{ route('admin.returns.update', $return->id) }}" method="post" class="form" enctype="multipart/form-data">
            <div class="box-body">
                {{ csrf_field() }}

                <input type="hidden" name="_method" value="put">

                @foreach($returnLines as $returnLine)

                <div class='form-inline' style="margin-bottom: 12px;">

                    {{$returnLine->quantity}}

                    <div class="pull-left col-lg-2">
                        {{ $items[$returnLine->line_id]->name }}
                    </div>

                    <div class="pull-left col-lg-2">
                        {{ $items[$returnLine->line_id]->sku }}
                    </div>

                    <div class="pull-left col-lg-2">
                        {{ $items[$returnLine->line_id]->price }}
                    </div>

                    <div class="pull-left col-lg-1">
                        {{ $items[$returnLine->line_id]->quantity }}
                    </div>

                    <div class="form-group" style="margin-right:10px;">
                        <label class='sr-only' for="address_2">Quantity</label>

                        <select name="lines[{{$returnLine->id}}][quantity]" id="quantity" class="form-control">
                            @for ($i = 1; $i <= $items[$returnLine->line_id]->quantity; $i++)
                            <option value="{{ $i }}" @if($i == $returnLine->quantity) selected="selected" @endif>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="form-group" style="margin-right:10px;">
                        <label class='sr-only' for="address_2">reason </label>
                        <select name="lines[{{$returnLine->id}}][reason]" id="reason" class="form-control">
                            <option value="">Select Reason</option>
                            @foreach($reasons as $reason)
                            <option value="{{ $reason }}" @if($reason == $returnLine->reason) selected="selected" @endif>{{ $reason }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endforeach

                <div class="form-group">
                    <label for="alias">Condition <span class="text-danger">*</span></label>
                    <select name="item_condition" id="condition" class="form-control">
                        @foreach($conditions as $condition)
                        <option @if($condition == $return->item_condition) selected="selected" @endif  value="{{ $condition }}">{{ $condition }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="address_1">Resolution <span class="text-danger">*</span></label>
                    <select name="resolution" id="resolution" class="form-control">
                        @foreach($resolutions as $resolution)
                        <option @if($resolution == $return->resolution) selected="selected" @endif  value="{{ $resolution }}">{{ $resolution }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="customer">Customer<span class="text-danger">*</span></label>
                    <select name="customer" id="customer" class="form-control">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                        <option if($customer->id == $return->customer) selected="selected" @endif value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="address_2">Status </label>
                    <select name="status" id="status" class="form-control">
                        @foreach($statuses as $status)
                        <option @if($status->id == $return->status) selected="selected" @endif  value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <div class="btn-group">
                    <a href="{{ route('admin.refunds.index') }}" class="btn btn-default">Back</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
    <!-- /.box -->

</section>
<!-- /.content -->
@endsection
@section('js')
<script type="text/javascript">
    $(document).ready(function () {
        $('#province_id').change(function () {
            var provinceId = $(this).val();
            $.ajax({
                url: '/api/v1/country/169/province/' + provinceId + '/city',
                contentType: 'json',
                success: function (data) {
                    var html = '<label for="city_id">City </label>';
                    html += '<select name="city_id" id="city_id" class="form-control">';
                    $(data.data).each(function (idx, v) {
                        html += '<option value="' + v.id + '">' + v.name + '</option>';
                    });
                    html += '</select>';
                    $('#cities').html(html).show();
                },
                errors: function (data) {
                    console.log(data);
                }
            });
        });
    });
</script>
@endsection
