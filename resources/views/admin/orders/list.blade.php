@extends('layouts.admin.app')

@section('content')

@include('layouts.errors-and-messages')
<!-- Main content -->
<section class="content">
    <div class="col-lg-3">
        <div class="box">
            <div class="box-body">
                <h2>Orders</h2>
                <i style="font-size: 30px; cursor: pointer;" href="{{ route('admin.orders.export') }}" class="fa fa-cloud-download Export"></i>
                <i href="{{ route('admin.orders.importCsv') }}" style="font-size: 30px; cursor: pointer;" class="fa fa-cloud-upload Import"></i>

                <!-- search form -->
                <div class="col-lg-12">
                    <form action="{{ route('admin.orders.search') }}" method="post" id="admin-search">

                        <input type="hidden" name="page" id="page" value="1">

                        {{ csrf_field() }}


                        <div style="margin-bottom:10px;">
                            <div class="input-group">
                                <input type="text" name="customer_ref" class="form-control" placeholder="Customer Ref" value="{{ old('q')}}">
                            </div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <div class="input-group">
                                <input type="text" name="customer_name" class="form-control" placeholder="Customer Name" value="{{ old('q')}}">
                            </div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <div class="input-group">
                                <input type="text" name="customer_email" class="form-control" placeholder="Customer Email" value="{{ old('email')}}">
                            </div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <div class="input-group">
                                <input type="text" name="voucher_code" class="form-control" placeholder="Voucher Code" value="{{ old('voucher_code')}}">
                            </div>
                        </div>

                        <div style="margin-bottom:10px;">
                            <div class="input-group">
                                <input type="text" name="product_name" class="form-control" placeholder="Product Name" value="{{ old('product_name')}}">
                            </div>
                        </div>

                        <div class="pull-left">
                            @if(!$channels->isEmpty())
                            <div style="margin-bottom:10px;">
                                <select name="order_channel" id="channel" class="form-control select2">
                                    <option value="">Channel</option>
                                    @foreach($channels as $channel)
                                    <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        <div class="pull-left">
                            @if(!$couriers->isEmpty())
                            <div style="margin-bottom:10px;">
                                <select name="courier[]" multiple='multiple' id="courier" class="form-control select2">
                                    @foreach($couriers as $courier)
                                    <option @if(old('courier') == $courier->id) selected="selected" @endif value="{{ $courier->id }}">{{ $courier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        <div style="margin-bottom:10px;">
                            @if(!$statuses->isEmpty())
                            <div class="form-group">
                                <select name="order_status" id="status" class="form-control select2">
                                    <option value="">Status</option>
                                    @foreach($statuses as $status)
                                    <option @if(old('$status') == $status->id) selected="selected" @endif value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        <span class="">
                            <button type="button" id="search-btn" class="btn btn-flat Search btn-success"><i class="fa fa-search"></i> Search </button>
                        </span>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-9 search-results">
        <div id="loading" ></div>

    </div>
</section>
<!-- /.content -->
@endsection

<div class="modal inmodal" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
    
</div>


@section('js')
<script type="text/javascript">

    $(document).ready(function () {

        loadPagination();

        $('.Import').off();
        $('.Import').on('click', function (e) {
            href = $(this).attr('href');

            $.ajax({
                type: "GET",
                url: href,
                success: function (response) {
                    $('#importModal').html(response);
                    $('#importModal').modal('show');
                }
            });
        });

        $('.Export').off();
        $('.Export').on('click', function (e) {
            href = $(this).attr('href');
            var formdata = $('#admin-search').serialize();

            $.ajax({
                type: "POST",
                url: href,
                data: formdata,
                success: function (response) {
                    exportCSVFile(response, 'orders');
                }
            });
        });

        $('.Search').off();
        $('.Search').on('click', function (e) {
            href = $('#admin-search').attr('action');
            $('.search-results').html('<div id="loading" ></div>');

            $('.Search').text('Loading...');
            $('.Search').prop('disabled', true);
            var formdata = $('#admin-search').serialize();
            $.ajax({
                type: "POST",
                url: href,
                data: formdata,
                success: function (response) {
                    $('.Search').html('<i class="fa fa-search"></i> Search');
                    $('.Search').prop('disabled', false);
                    $('.search-results').html(response);
                }
            });
        });

        $('.Search').click();
    });
</script>

@endsection
