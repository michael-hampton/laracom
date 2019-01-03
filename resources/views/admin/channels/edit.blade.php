@extends('layouts.admin.app')

<?php

function buildcheckBox($value, $label) {

    $checked = $value == 1 ? 'checked' : '';

    echo '<div class="switch">
    <div class="onoffswitch">
        <input type="checkbox" ' . $checked . ' class="onoffswitch-checkbox" id="' . $label . '">
        <label class="onoffswitch-label" for="' . $label . '">
            <span class="onoffswitch-inner"></span>
            <span class="onoffswitch-switch"></span>
        </label>
    </div>
</div>';
}
?>

<style>
    /* SWITCHES */
    .onoffswitch {
        position: relative;
        width: 54px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
    .onoffswitch-checkbox {
        display: none;
    }
    .onoffswitch-label {
        display: block;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid #1AB394;
        border-radius: 3px;
    }
    .onoffswitch-inner {
        display: block;
        width: 200%;
        margin-left: -100%;
        -moz-transition: margin 0.3s ease-in 0s;
        -webkit-transition: margin 0.3s ease-in 0s;
        -o-transition: margin 0.3s ease-in 0s;
        transition: margin 0.3s ease-in 0s;
    }
    .onoffswitch-inner:before,
    .onoffswitch-inner:after {
        display: block;
        float: left;
        width: 50%;
        height: 16px;
        padding: 0;
        line-height: 16px;
        font-size: 10px;
        color: white;
        font-family: Trebuchet, Arial, sans-serif;
        font-weight: bold;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
    }
    .onoffswitch-inner:before {
        content: "ON";
        padding-left: 7px;
        background-color: #1AB394;
        color: #FFFFFF;
    }
    .onoffswitch-inner:after {
        content: "OFF";
        padding-right: 7px;
        background-color: #FFFFFF;
        color: #919191;
        text-align: right;
    }
    .onoffswitch-switch {
        display: block;
        width: 18px;
        margin: 0;
        background: #FFFFFF;
        border: 2px solid #1AB394;
        border-radius: 3px;
        position: absolute;
        top: 0;
        bottom: 0;
        right: 36px;
        -moz-transition: all 0.3s ease-in 0s;
        -webkit-transition: all 0.3s ease-in 0s;
        -o-transition: all 0.3s ease-in 0s;
        transition: all 0.3s ease-in 0s;
    }
    .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
        margin-left: 0;
    }
    .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
        right: 0;
    }
</style>

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <div class="box">
        <form action="{{ route('admin.channels.update', $channel->id) }}" method="post" class="form" enctype="multipart/form-data">
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

                        {{buildCheckbox($channel->has_priority, 'has_priority')}}

                        <!--                        <div class="form-group">
                                                    <label for="status">Has Priority </label>
                                                    <select name="has_priority" id="has_priority" class="form-control">
                                                        <option value="1" {{ $channel->has_priority === 1 ? 'selected="selected"' : '' }}>Yes</option>
                                                        <option value="0" {{ $channel->has_priority === 0 ? 'selected="selected"' : '' }}>No</option>
                                                    </select>
                                                </div>-->

                        <div class="form-group">
                            <label for="status">Allocate On Order </label>
                            <select name="allocate_on_order" id="allocate_on_order" class="form-control">
                                <option value="1" {{ $channel->allocate_on_order === 1 ? 'selected="selected"' : '' }}>Yes</option>
                                <option value="0" {{ $channel->allocate_on_order === 0 ? 'selected="selected"' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Backorders enabled </label>
                            <select name="backorders_enabled" id="backorders_enabled" class="form-control">
                                <option value="1" {{ $channel->backorders_enabled === 1 ? 'selected="selected"' : '' }}>Yes</option>
                                <option value="0" {{ $channel->backorders_enabled === 0 ? 'selected="selected"' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Send Order Received Email </label>
                            <select name="send_received_email" id="send_received_email" class="form-control">
                                <option value="1" {{ $channel->send_received_email === 1 ? 'selected="selected"' : '' }}>Yes</option>
                                <option value="0" {{ $channel->send_received_email === 0 ? 'selected="selected"' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Send Dispatched Email </label>
                            <select name="send_dispatched_email" id="send_dispatched_email" class="form-control">
                                <option value="1" {{ $channel->send_dispatched_email === 1 ? 'selected="selected"' : '' }}>Yes</option>
                                <option value="0" {{ $channel->send_dispatched_email === 0 ? 'selected="selected"' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status">Send Dispatched Email </label>
                            <select name="send_dispatched_email" id="send_dispatched_email" class="form-control">
                                <option value="1" {{ $channel->send_dispatched_email === 1 ? 'selected="selected"' : '' }}>Yes</option>
                                <option value="0" {{ $channel->send_dispatched_email === 0 ? 'selected="selected"' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status">Strict validation </label>
                            <select name="strict_validation" id="send_dispatched_email" class="form-control">
                                <option value="1" {{ $channel->send_dispatched_email === 1 ? 'selected="selected"' : '' }}>Yes</option>
                                <option value="0" {{ $channel->send_dispatched_email === 0 ? 'selected="selected"' : '' }}>No</option>
                            </select>
                        </div>
                        
                         <div class="form-group">
                            <label for="status">Partial Shipment </label>
                            <select name="partial_shipment" id="send_dispatched_email" class="form-control">
                                <option value="1" {{ $channel->send_dispatched_email === 1 ? 'selected="selected"' : '' }}>Yes</option>
                                <option value="0" {{ $channel->send_dispatched_email === 0 ? 'selected="selected"' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status">Status </label>
                            <select name="status" id="status" class="form-control">
                                <option value="0" @if($channel->status == 0) selected="selected" @endif>Disable</option>
                                <option value="1" @if($channel->status == 1) selected="selected" @endif>Enable</option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <div class="btn-group">
                    <a href="{{ route('admin.channels.index') }}" class="btn btn-default">Back</a>
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
    
    ('.test').bootstrapSwitch();

$('.test').on('switchChange.bootstrapSwitch', function () {

    if ($('input#isAdmin').bootstrapSwitch('state')) {
        alert($(this).attr('id') + 'I am true');
         $(this).val("1"); 
  	} else {
    	alert($(this).attr('id') + 'I am false');
       $(this).val("0"); 
    }
});

var arr = {};
$('.test').each(function(){
    arr.push({
         $(this).attr('id'): $(this).val()              
     });
});

console.log(arr);
        
        /*$(".onoffswitch-inner").off();
         $(".onoffswitch-inner").click(function() {
             alert('Mike');
         });*/
    });
</script>
@endsection

