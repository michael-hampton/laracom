@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <!-- Default box -->
    <div class="box">

        @include('admin.shared.box-header-box-tools', ['boxTitle' => "My Stores"])

        <button type="button" class="btn btn-primary AddChannel">+</button>

        <div class="box-body">

            @if (isset($channels) && count($channels) > 0)
            @foreach ( $channels as $channel )
            @include('admin.shared.channels-cards', ['channel' => $channel])
            @endforeach
            @else
            <h4>No Assigned Channel Yet</h4>
            @endif

        </div>

        <!-- /.box-footer-->
    </div>
</section>
<!-- /.content -->
@endsection

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Edit Product</h4>
            </div>

            <div class="modal-body">

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary saveNewChannel">Save</button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.js" data-turbolinks-track="true"></script>

<script type="text/javascript">
    $('.saveNewChannel').on('click', function (e) {
        e.preventDefault();
        $('.modal-body .alert-danger').remove();
        var formdata = new FormData($('#NewChannelForm')[0]);
        formdata.append('cover', $('#cover')[0].files[0]);
        var href = $('#NewChannelForm').attr('action');
        $.ajax({
            type: "POST",
            url: href,
            data: formdata,
            processData: false, // tell jQuery not to process the data
            contentType: false, // tell jQuery not to set contentType
            success: function (response) {
                var obj = jQuery.parseJSON(response);
                if (obj.http_code == 400) {
                    $('.modal-body').prepend("<div class='alert alert-danger'></div>");
                    $.each(obj.errors, function (key, value) {
                        $('.modal-body .alert-danger').append("<p>" + value + "</p>");
                    });
                } else {
                    $('.modal-body').prepend("<div class='alert alert-success'>Product has been updated successfully</div>");
                }
            }
        });
    });
    $(document).on('click', '.AddChannel', function (e) {
        e.preventDefault();
//var href = $(this).attr("href");
        $.ajax({
            type: "GET",
            url: '/admin/channels/create',
            success: function (response) {
                $('#myModal').find('.modal-body').html(response);
                $('#myModal').modal('show');
            }
        });
    });
</script>
@endsection