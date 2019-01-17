@extends('layouts.admin.app')

@section('content')

@include('layouts.errors-and-messages')

<style>
    /* Tabs */
    .tabs-container .panel-body {
        background: #fff;
        border: 1px solid #e7eaec;
        border-radius: 2px;
        padding: 20px;
        position: relative;
    }
    .tabs-container .nav-tabs > li.active > a,
    .tabs-container .nav-tabs > li.active > a:hover,
    .tabs-container .nav-tabs > li.active > a:focus {
        border: 1px solid #e7eaec;
        border-bottom-color: transparent;
        background-color: #fff;
    }
    .tabs-container .nav-tabs > li {
        float: left;
        margin-bottom: -1px;
    }
    .tabs-container .tab-pane .panel-body {
        border-top: none;
    }
    .tabs-container .nav-tabs > li.active > a,
    .tabs-container .nav-tabs > li.active > a:hover,
    .tabs-container .nav-tabs > li.active > a:focus {
        border: 1px solid #e7eaec;
        border-bottom-color: transparent;
    }
    .tabs-container .nav-tabs {
        border-bottom: 1px solid #e7eaec;
    }
    .tabs-container .tab-pane .panel-body {
        border-top: none;
    }
    .tabs-container .tabs-left .tab-pane .panel-body,
    .tabs-container .tabs-right .tab-pane .panel-body {
        border-top: 1px solid #e7eaec;
    }
    .tabs-container .tabs-below > .nav-tabs,
    .tabs-container .tabs-right > .nav-tabs,
    .tabs-container .tabs-left > .nav-tabs {
        border-bottom: 0;
    }
    .tabs-container .tabs-left .panel-body {
        position: static;
    }
    .tabs-container .tabs-left > .nav-tabs,
    .tabs-container .tabs-right > .nav-tabs {
        width: 20%;
    }
    .tabs-container .tabs-left .panel-body {
        width: 80%;
        margin-left: 20%;
    }
    .tabs-container .tabs-right .panel-body {
        width: 80%;
        margin-right: 20%;
    }
    .tabs-container .tab-content > .tab-pane,
    .tabs-container .pill-content > .pill-pane {
        display: none;
    }
    .tabs-container .tab-content > .active,
    .tabs-container .pill-content > .active {
        display: block;
    }
    .tabs-container .tabs-below > .nav-tabs {
        border-top: 1px solid #e7eaec;
    }
    .tabs-container .tabs-below > .nav-tabs > li {
        margin-top: -1px;
        margin-bottom: 0;
    }
    .tabs-container .tabs-below > .nav-tabs > li > a {
        -webkit-border-radius: 0 0 4px 4px;
        -moz-border-radius: 0 0 4px 4px;
        border-radius: 0 0 4px 4px;
    }
    .tabs-container .tabs-below > .nav-tabs > li > a:hover,
    .tabs-container .tabs-below > .nav-tabs > li > a:focus {
        border-top-color: #e7eaec;
        border-bottom-color: transparent;
    }
    .tabs-container .tabs-left > .nav-tabs > li,
    .tabs-container .tabs-right > .nav-tabs > li {
        float: none;
    }
    .tabs-container .tabs-left > .nav-tabs > li > a,
    .tabs-container .tabs-right > .nav-tabs > li > a {
        min-width: 124px;
        margin-right: 0;
        margin-bottom: 3px;
    }
    .tabs-container .tabs-left > .nav-tabs {
        float: left;
        margin-right: 19px;
    }
    .tabs-container .tabs-left > .nav-tabs > li > a {
        margin-right: -1px;
        -webkit-border-radius: 4px 0 0 4px;
        -moz-border-radius: 4px 0 0 4px;
        border-radius: 4px 0 0 4px;
    }
    .tabs-container .tabs-left > .nav-tabs a.active,
    .tabs-container .tabs-left > .nav-tabs a.active:hover,
    .tabs-container .tabs-left > .nav-tabs a.active:focus {
        border-color: #e7eaec transparent #e7eaec #e7eaec;
    }
    .tabs-container .tabs-right > .nav-tabs {
        float: right;
        margin-left: 19px;
    }
    .tabs-container .tabs-right > .nav-tabs > li > a {
        margin-left: -1px;
        -webkit-border-radius: 0 4px 4px 0;
        -moz-border-radius: 0 4px 4px 0;
        border-radius: 0 4px 4px 0;
    }
    .tabs-container .tabs-right > .nav-tabs a.active,
    .tabs-container .tabs-right > .nav-tabs a.active:hover,
    .tabs-container .tabs-right > .nav-tabs a.active:focus {
        border-color: #e7eaec #e7eaec #e7eaec transparent;
        z-index: 1;
    }
    .tabs-container .tabs-right > .nav-tabs li {
        z-index: 1;
    }
    .nav-tabs .nav-link:not(.active):focus,
    .nav-tabs .nav-link:not(.active):hover {
        border-color: transparent;
    }
    @media (max-width: 767px) {
        .tabs-container .nav-tabs > li {
            float: none !important;
        }
        .tabs-container .nav-tabs > li.active > a {
            border-bottom: 1px solid #e7eaec !important;
            margin: 0;
        }
    }
</style>

<!-- Main content -->
<section class="content">
    <div class="col-lg-3">
        <div class="box">
            <div class="box-body">
                <form action="/admin/invoices/index" method="get" id="admin-search">

                    @if(!$channels->isEmpty())
                    <div class="form-group">
                        <select name="channel" id="channel" class="form-control select2">
                            <option value="">Channel</option>
                            @foreach($channels as $channel)
                            <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <span class="input-group-btn">
                        <button type="submit" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i> Search </button>
                    </span>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-9">

        <div class="box">
            <div class="box-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li><a class="nav-link active" data-toggle="tab" href="#tab-1">Waiting to be Invoiced</a></li>
                    <li><a class="nav-link" data-toggle="tab" href="#tab-2">Invoiced</a></li>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" id="tab-1" class="tab-pane active">
                        <div class="panel-body">

                            <a href="#" class="uncheck">Uncheck</a>
                            <table class="table">
                                <thead>
                                <th class="col-md-2">Order Id</th>
                                <th class="col-md-2">Channel</th>
                                <th class="col-md-2">Voucher Amount</th>
                                <th class="col-md-2">Delivery</th>
                                <th class="col-md-2">Total</th>
                                <th class="col-md-2">Actions</th>

                                </thead>
                                <tbody>


                                    @foreach($orders as $order)


                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td> {{ $order->channel->name }}</td>
                                        <td> {{ $order->discounts }}</td>
                                        <td> {{ $order->total_shipping }}</td>
                                        <td> {{ $order->total }}</td>
                                        <td>
                                            <input type="checkbox" checked="checked" class="cb" name="services[]" order-id="{{ $order->id }}" value="{{ $order->id }}">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="box-footer col-lg-12">

                                <div class="btn-group pull-right">
                                    <button type="button" class="btn btn-primary do-allocation">Invoice</button>
                                </div>

                                <div class='checkbox-count'></div>



                            </div>
                        </div>
                    </div>

                    <div role="tabpanel" id="tab-2" class="tab-pane">
                        <div class="panel-body">

                            <table class="table">
                                <thead>
                                <th class="col-md-2">Order Id</th>
                                <th class="col-md-2">Channel</th>
                                <th class="col-md-2">Voucher Amount</th>
                                <th class="col-md-2">Delivery</th>
                                <th class="col-md-2">Total Invoiced</th>
                                </thead>
                                <tbody>


                                    @foreach($invoiced as $order)


                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->channel->name }}</td>
                                        <td>{{ $order->discounts }}</td>
                                        <td>{{ $order->total_shipping }}</td>
                                        <td>{{ $order->amount_invoiced }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
@endsection

@section('js')
<script type="text/javascript">
    $(document).ready(function () {
    
    $('#channel').on('change', function () {
         window.location.href = '/admin/invoice/index'+$(this).val();
    });

        $('.uncheck').click(function () {
            var checkboxes = $('.cb');
            $('.cb').prop('checked', !checkboxes.prop('checked'));
        });

        $('.cb').change(function () {
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
            var cb = [];
            $.each($('.cb:checked'), function () {

                var orderId = $(this).attr('order-id');

                $(this).parent().parent().addClass('toBeRemoved');

                cb.push($(this).val());
            });

            $.ajax({
                type: "POST",
                url: '/admin/invoice/invoiceOrder',
                data: {
                    orderIds: cb,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    var response = JSON.parse(response);
                    
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
