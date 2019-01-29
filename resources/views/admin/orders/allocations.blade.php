@extends('layouts.admin.app')

@section('content')

@include('layouts.errors-and-messages')

<!-- Main content -->
<section class="content">
    <div class="col-lg-3">
        <div class="box">
            <div class="box-body">
                <h2>Orders</h2>

                <!-- search form -->
                <div class="col-lg-12">
                    <form action="{{ route('admin.orderLine.search') }}" method="post" id="admin-search">

                        {{ csrf_field() }}

                        <input type="hidden" name="page" id="page" value="1">


                        <div style="margin-bottom: 10px;">
                            <div class="input-group" style="width:100%">
                                <input type="text" name="customer_ref" class="form-control" placeholder="Customer Ref" value="{{ old('q')}}">
                            </div>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <div class="input-group" style="width:100%">
                                <input type="text" name="customer_name" class="form-control" placeholder="Customer Name" value="{{ old('q')}}">
                            </div>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <div class="input-group" style="width:100%">
                                <input type="text" name="customer_email" class="form-control" placeholder="Customer Email" value="{{ old('email')}}">
                            </div>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <div class="input-group" style="width:100%">
                                <input type="text" name="product_name" class="form-control" placeholder="Product Name" value="{{ old('product_name')}}">
                            </div>
                        </div>


                        <div style="margin-bottom: 10px;">
                            @if(!$channels->isEmpty())
                            <div class="form-group">
                                <select name="order_channel" id="channel" class="form-control select2">
                                    <option value="">Channel</option>
                                    @foreach($channels as $channel)
                                    <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        <div style="margin-bottom: 10px;">
                            @if(!$couriers->isEmpty())
                            <div class="form-group">
                                <select name="line_courier[]" multiple='multiple' id="courier" class="form-control select2">
                                    @foreach($couriers as $courier)
                                    <option @if(old('courier') == $courier->id) selected="selected" @endif value="{{ $courier->id }}">{{ $courier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif


                            <span class="input-group-btn">
                                <button type="button" id="search-btn" class="btn btn-flat Search"><i class="fa fa-search"></i> Search </button>
                            </span>

                            <input type="hidden" id="status" name="line_status" value="14">
                            <input type="hidden" id="module" name="module" value="allocations">
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-9 search-results">

        <img class="loader" src="{{url('/images/loading.gif')}}" alt="Loading"/>

    </div>


    <div class="box-footer col-lg-12">

        <div class="btn-group pull-right">
            <button type="button" class="btn btn-primary do-allocation">Allocate</button>
        </div>

        <div class='checkbox-count'></div>

        {{ $items->links() }}

    </div>

</section>
<!-- /.content -->
@endsection

@section('css')
<style type="text/css">
    .table-warning, .table-warning>td, .table-warning>th {
        background-color: #ffeeba;
    }
</style>
@endsection

@section('js')
<script type="text/javascript">
    $(document).ready(function () {

        loadPagination();

        $('.Search').on('click', function (e) {
            href = $('#admin-search').attr('action');
            $('.Search').text('Loading...');
            $('.Search').prop('disabled', true);
            $('.search-results').html('<img class="loader" src="{{url('/images/loading.gif')}}" alt="Loading"/>');
            var formdata = $('#admin-search').serialize();
            $.ajax({
                type: "POST",
                url: href,
                data: formdata,
                success: function (response) {
                $('.Search').text('Search');
            $('.Search').prop('disabled', false);
                    $('.search-results').html(response);
                }
            });
        });

        $('.Search').click();

        $('.uncheck').click(function () {
            var checkboxes = $('.cb');
            $('.cb').prop('checked', !checkboxes.prop('checked'));
        });
        
        $(document).on('change', '.cb', function() {
        
            var numberOfChecked = $('.cb:checked').length;
            var totalCheckboxes = $('.cb').length;
            $('.checkbox-count').html(numberOfChecked + ' / ' + totalCheckboxes);
        });

        $('.do-allocation').on('click', function () {

            if ($('.cb:checked').length == 0)
            {
                alert('Please select atleast one checkbox');
                return false;
            }
            var cb = {};
            $.each($('.cb:checked'), function () {

                var orderId = $(this).attr('order-id');

                if (cb[orderId] === undefined) {
                    cb[orderId] = [];
                }

                $(this).parent().parent().addClass('toBeRemoved');

                cb[orderId].push($(this).val());
            });

            $.ajax({
                type: "POST",
                url: '/admin/orderLine/doAllocation',
                data: {
                    lineIds: cb,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {

                    if (response.http_code === 400) {

                        $('.content').prepend("<div class='alert alert-danger'></div>");

                        $.each(response.FAILURES, function (lineId, val) {

                            $('.content .alert-danger').append("<p> Line Id: " + lineId + " " + val + "</p>");

                        });
                    } else {
                        $('.content').prepend("<div class='alert alert-success'></div>");

                        $.each(response.SUCCESS, function (lineId, val) {

                            $('.content .alert-success').append("<p>" + val + "</p>");

                        });

                        $('.toBeRemoved').remove();

                    }

                }
            });
            return false;
        });
    });
</script>
@endsection;
