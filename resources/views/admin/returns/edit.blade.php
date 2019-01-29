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

                    <input type="checkbox" name="lines[{{$returnLine->id}}][return]">

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
                        <option  @if($customer->id == $return->customer) selected="selected" @endif value="{{ $customer->id }}">{{ $customer->name }}</option>
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

    <h2>Messages</h2>

    <div class="existing-messages col-lg-12" style="margin-bottom: 12px; border: 1px solid #CCC;">

        @foreach($messages as $message)
        <div style="border-bottom: 1px #CCC dotted; padding:6px;" class="col-lg-12">

            <div class="col-lg-4 pull-right">
                {{$message->created_at}}
            </div>

            <div class="col-lg-8 pull-right">
                {{$message->subject}}
            </div>
            {{$message->body}}<br>
        </div>
        @endforeach
    </div>

    <form id='messageForm'>

        {{ csrf_field() }}

        <input type='hidden' id='order_id' name='order_id' value="{{$order->id}}" class='form-control'>
        <input type='hidden' id='message_type' name='message_type' value="2" class='form-control'>
        <input type="hidden" name="thread_id" value="<?= (isset($messages[0]) ? $messages[0]->thread_id : '') ?>">
        <input type="hidden" id='email_address' name="email_address" value="{{$order->customer->email}}">

        <div class="form-group">
            <label>Subject</label> 
            <input type='text' id='subject' name='subject' class='form-control'>
        </div>

        <div class="form-group">
            <label>Comment</label> 
            <textarea id='comment' name='message' class='form-control'></textarea>
        </div>

        <button class="btn btn-primary saveMessage">Save</button>
    </form>

</section>
<!-- /.content -->
@endsection
@section('js')
<script type="text/javascript">
    $(document).ready(function () {
        $('.saveMessage').on('click', function (e) {

            e.preventDefault();

            var formdata = $('#messageForm').serialize();

            $.ajax({
                type: "POST",
                url: '/admin/message/store',
                data: formdata,
                success: function (response) {

                    if (response.http_code === 400) {

                        $('.modal-body').prepend("<div class='alert alert-danger'>Unable to save message</div>");


                    } else {

                        $('.modal-body').prepend("<div class='alert alert-success'>Message was saved successfully</div>");
                        location.reload();
                    }
                }
            });

        });
    });

</script>
@endsection

