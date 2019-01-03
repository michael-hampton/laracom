@extends('layouts.admin.app')

<?php

function buildcheckBox($value, $label) {

    $checked = $value == 1 ? 'checked' : '';

    echo '<input type="checkbox" ' . $checked . ' class="test" id="' . $label . '">';
}
?>



@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap2/bootstrap-switch.min.css" rel="stylesheet" type="text/css">
    <div class="box">
        <form id="channelForm" channel-id="{{ $channel->id }}" action="{{ route('admin.channels.update', $channel->id) }}" method="post" class="form" enctype="multipart/form-data">
            <div class="box-body">
                <div class="row">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="put">
                    <div class="col-md-8">
                        <h2>{{ ucfirst($channel->name) }}</h2>
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" placeholder="Name" class="form-control" value="{{ $channel->name ?: old('name') }}">
                        </div>
                        <div class="form-group">
                            <label for="description">Description </label>
                            <textarea class="form-control ckeditor" name="description" id="description" rows="5" placeholder="Description">{{ $channel->description ?: old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            @if(isset($channel->cover))
                            <div class="col-md-3">
                                <div class="row">
                                    <img src="{{ asset("storage/$channel->cover") }}" alt="" class="img-responsive"> <br />
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="row"></div>
                        <div class="form-group">
                            <label for="cover">Cover </label>
                            <input type="file" name="cover" id="cover" class="form-control">
                        </div>

                        <div class="box-footer">
                            <div class="btn-group">
                                <a href="{{ route('admin.channels.index') }}" class="btn btn-default">Back</a>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status </label>
                            <select name="status" id="status" class="form-control">
                                <option value="0" @if($channel->status == 0) selected="selected" @endif>Disable</option>
                                <option value="1" @if($channel->status == 1) selected="selected" @endif>Enable</option>
                            </select>
                        </div>
                        </form>

                        <div class="form-group">
                            <label for="status">Has Priority </label>
                            {{buildCheckbox($channel->has_priority, 'has_priority')}}
                        </div>

                        <div class="form-group">
                            <label for="status">Allocate On Order </label>
                            {{buildCheckbox($channel->allocate_on_order, 'allocate_on_order')}}
                        </div>
                        <div class="form-group">
                            <label for="status">Backorders enabled </label>
                            {{buildCheckbox($channel->backorders_enabled, 'backorders_enabled')}}
                        </div>
                        <div class="form-group">
                            <label for="status">Send Order Received Email </label>
                            {{buildCheckbox($channel->send_received_email, 'send_received_email')}}
                        </div>
                        <div class="form-group">
                            <label for="status">Send Dispatched Email </label>
                            {{buildCheckbox($channel->send_dispatched_email, 'send_dispatched_email')}}
                        </div>

                        <div class="form-group">
                            <label for="status">Strict validation </label>
                            {{buildCheckbox($channel->strict_validation, 'strict_validation')}}
                        </div>

                        <div class="form-group">
                            <label for="status">Partial Shipment </label>
                            {{buildCheckbox($channel->partial_shipment, 'partial_shipment')}}
                        </div>


                    </div>

                </div>
            </div>
            <!-- /.box-body -->

    </div>
    <!-- /.box -->

</section>



<!-- /.content -->
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.js" data-turbolinks-track="true"></script>


<script type="text/javascript">


$(document).ready(function () {

    $('.test').bootstrapSwitch();

    $('.test').on('switchChange.bootstrapSwitch', function () {

        if ($(this).bootstrapSwitch('state')) {
            var val = 1;
            $(this).val("1");
        } else {
            var val = 0;
            $(this).val("0");
        }

        var id = $(this).attr('id');
        var channelId = $("#channelForm").attr("channel-id");

        $.ajax({
            type: "POST",
            url: '/admin/channels/saveChannelAttribute',
            data: {
                channelId: channelId,
                id: id,
                value: val,
                _token: '{{ csrf_token() }}'
            },
            success: function (msg) {
                alert(msg);
            }
        });
    });
});
</script>
@endsection

