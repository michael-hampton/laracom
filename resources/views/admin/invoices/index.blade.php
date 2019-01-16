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
                    <form action="" method="post" id="admin-search">

                        {{ csrf_field() }}


 


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

                        


                            <span class="input-group-btn">
                                <button type="submit" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i> Search </button>
                            </span>

                            <input type="hidden" id="status" name="line_status" value="14">
                            <input type="hidden" id="module" name="module" value="allocations">
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        

                
                
                <ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Home</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Profile</a>
  </li>
</ul>
<div class="tab-content" id="myTabContent">
  <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
  
  <a href="#" class="uncheck">Uncheck</a>
  <table class="table">
                    <thead>
                    <th class="col-md-2">Order Id</th>
                    <th class="col-md-2">Channel</th>
                    <th class="col-md-2">Voucher Amount</th>
                    <th class="col-md-2">Delivery</th>
                    <th class="col-md-2">Actions</th>
                    
                    </thead>
                    <tbody>


                        @foreach($orders as $order)


                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>
                                {{ $order->channel->name }}
                            </td>
                           

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
  
  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
  <table class="table">
                    <thead>
                    <th class="col-md-2">Order Id</th>
                    <th class="col-md-2">Channel</th>
                    <th class="col-md-2">Voucher Amount</th>
                    <th class="col-md-2">Delivery</th>
                    <th class="col-md-2">Actions</th>
                    </thead>
                    <tbody>


                        @foreach($invoiced as $order)


                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>
                                {{ $order->channel->name }}
                            </td>
                           

                            <td>

                               
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
  </div>
  </div>



    

</section>
<!-- /.content -->
@endsection

@section('js')
<script type="text/javascript">
    $(document).ready(function () {

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
