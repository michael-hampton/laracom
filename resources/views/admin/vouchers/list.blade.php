@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')

    <!-- Default box -->


    <div class="col-lg-6">

        @if($vouchers)
        <div class="box">
            <div class="box-body">
                <h2>Vouchers
                    <button type="button" class="btn btn-primary AddVoucher">+</button>
                </h2>
                @include('layouts.search', ['route' => route('admin.vouchers.index')])
                <table class="table">
                    <thead>
                        <tr>
                            <td class="col-md-2">Start Date</td>
                            <td class="col-md-2">Expiry Date</td>
                            <td class="col-md-1">Status</td>
                            <td class="col-md-1">Channel</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vouchers as $voucher)
                        <tr class='clickable-row' data-href="{{ route('admin.vouchers.edit', $voucher->id) }}">
                            <td>{{ date('d-m-Y', strtotime($voucher->start_date)) }}</td>
                            <td>{{ date('d-m-Y', strtotime($voucher->expiry_date)) }}</td>
                            <td>@include('layouts.status', ['status' => $voucher->status])</td>
                            <td>{{ $voucher->channel_name }}</td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($vouchers instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                <div class="row">
                    <div class="col-md-12">
                        <div class="pull-left">{{ $vouchers->links() }}</div>
                    </div>
                </div>
                @endif
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->

        @else
        <div class="box">
            <div class="box-body"><p class="alert alert-warning">No vouchers found.</p></div>
        </div>
        @endif
    </div>

    <div id="content-div">

    </div>


</section>
<!-- /.content -->
@endsection

<div class="modal inmodal" id="newVoucherModal" tabindex="-1" role="dialog" aria-hidden="true">
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
                <button type="button" class="btn btn-primary saveNewVoucher">Save changes</button>
            </div>
        </div>
    </div>
</div>

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
                <button type="button" class="btn btn-primary UpdateChannel">Save changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="voucherCodeModal" tabindex="-1" role="dialog" aria-hidden="true">
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
                <button type="button" class="btn btn-primary saveCode">Save changes</button>
            </div>
        </div>
    </div>
</div>

@section('css')
<style type="text/css">
    .selectedItem {
        background-color: #FFFF66;
    }
</style>
@endsection


@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>

<script type="text/javascript">
$(document).ready(function () {

    $('.AddVoucher').on('click', function (e) {

        $.ajax({
            type: "GET",
            url: '/admin/vouchers/create',
            success: function (response) {

                $('#newVoucherModal').find('.modal-body').html(response);
                $('#newVoucherModal').modal('show');
            }
        });
    });

    $('.saveNewVoucher').on('click', function (e) {

        e.preventDefault();
        $('.modal-body .alert-danger').remove();
        $('.saveNewVoucher').prop('disabled', true);

        if ($('.uploaded-product').length > 0) {
            var uploadedProductCodes = $(".uploaded-product").map(function () {
                return $(this).html();
            }).get().join(', ');

            $('#uploadedProductCodes').val(uploadedProductCodes);
        }

        //var formdata = $('#NewVoucherForm').serialize();
        var formdata = new FormData($('#NewVoucherForm')[0]);
        var href = $('#NewVoucherForm').attr('action');

        $.ajax({
            type: "POST",
            url: href,
            processData: false,
            contentType: false,
            data: formdata,
            success: function (response) {
                if (response.http_code == 400) {
                    $('.modal-body').prepend("<div class='alert alert-danger'></div>");
                    $.each(response.errors, function (key, value) {
                        $('.modal-body .alert-danger').append("<p>" + value + "</p>");
                    });
                } else {

                    $('.modal-body').prepend("<div class='alert alert-success'>Voucher has been created successfully</div>");

                    $('.modal-body .alert-success').append('<a href="' + response.filename + '">Download</a>');

                    if (response.import_result != undefined) {

                        $('.modal-body .alert-success').append('<p>' + response.import_result.added + ' voucher codes were imported</p>');
                        if (response.import_result.duplicates != undefined) {

                            $('.modal-body .alert-success').append('<p>The following voucher codes were duplicates and could not be added</p><ul>');
                            $.each(response.import_result.duplicates, function (key, value) {

                                $('.modal-body .alert-success').append('<li>' + value + '</li>');
                            });

                            $('.modal-body .alert-success').append('</ul>');
                        }
                    }

                    if (response.product_result != undefined) {

                        if (response.product_result.not_found != undefined) {
                            $('.modal-body .alert-success').append('<p>The following product codes could not be found</p><ul>');

                            $.each(response.product_result.not_found, function (key, value) {

                                $('.modal-body .alert-success').append('<li>' + value + '</li>');
                            });
                        }

                        if (response.product_result.product_ids != undefined) {

                            $('.modal-body .alert-success').append('<p>The following product codes have been added</p><ul>');

                            $.each(response.product_result.product_ids, function (key, value) {

                                $('.modal-body .alert-success').append('<li>' + value + '</li>');
                            });
                        }
                    }



                }

                $('.saveNewVoucher').prop('disabled', false);
            }
        });
    });

    $(".clickable-row").click(function () {

        var $this = $(this);
        $.ajax({
            type: "GET",
            url: $(this).data("href"),
            success: function (response) {

                $('#content-div').html(response);
                $('.clickable-row').removeClass('selectedItem');
                $this.addClass('selectedItem');
            }
        });

        return false;

        window.location = $(this).data("href");
    });
});
</script>

@endsection
