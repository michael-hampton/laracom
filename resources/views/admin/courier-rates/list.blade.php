@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">

    @include('layouts.errors-and-messages')

    <form action="{{ route('admin.courier-rates.search') }}" method="post" id="admin-search">

        {{ csrf_field() }}

        <div class="row">
            <div class="form-group col-lg-2">
                <label for="channel">Channel</label>
                <select name="channel" id="channel" class="form-control select2">
                    @foreach($channels as $channel)
                    <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-lg-2">
                <label for="country">Country </label>
                <select name="country" id="country" class="form-control">
                    <option value="">--Select--</option>
                    @foreach($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </select>
            </div>
            <button style="margin-top:26px;" type="button" class="btn btn-primary Search">Search</button>

        </div>

    </form>

    <div class="box">
        <form action="{{ route('admin.courier-rates.store') }}" method="post" id="shippingForm" class="form">
            <div class="box-body">
                {{ csrf_field() }}

                <div class='inline-form'>
                    <div class="form-group col-lg-3">
                        <label for="courier" class="sr-only">Courier</label>
                        <select name="courier" id="courier" class="form-control select2">
                            <option value="">--Select--</option>
                            @foreach($couriers as $courier)
                            <option @if(old('courier') == $courier->id) selected="selected" @endif value="{{ $courier->id }}">{{ $courier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-lg-2">
                        <label class='sr-only' for="range_from">Range From</label>
                        <div class="input-group">
                            <input type="text" name="range_from" id="range_from" placeholder="Range From" class="form-control" value="{{ old('range_from') }}">
                        </div>
                    </div>

                    <div class="form-group col-lg-2">
                        <label class='sr-only' for="range_to">Range To</label>
                        <div class="input-group">
                            <input type="text" name="range_to" id="range_to" placeholder="Range To" class="form-control" value="{{ old('range_to') }}">
                        </div>
                    </div>

                    <div class="form-group col-lg-2">
                        <label class='sr-only' for="cost">Cost</label>
                        <div class="input-group">
                            <input type="text" name="cost" id="cost" placeholder="Cost" class="form-control" value="{{ old('cost') }}">
                        </div>
                    </div>

                    <div class="form-group col-lg-3">
                        <label class='sr-only' for="country">Country </label>
                        <select name="country" id="country" class="form-control">
                            @foreach($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <div class="btn-group">
                    <a href="{{ route('admin.courier-rates.index') }}" class="btn btn-default">Back</a>
                    <button type="submit" class="btn btn-primary Create">Create</button>
                </div>
            </div>
        </form>

        <div class="search-results">

        </div>

</section>
<!-- /.content -->
@endsection

@section('js')
<script type="text/javascript">

    $(document).ready(function () {
        $('.Search').on('click', function (e) {

            href = $('#admin-search').attr('action');
            var formdata = $('#admin-search').serialize();

            $.ajax({
                type: "POST",
                url: href,
                data: formdata,
                success: function (response) {
                    $('.search-results').html(response);
                }
            });
        });


        $('.Search').click();

        $('#shippingForm').on('submit', function (e) {

            e.preventDefault();

            var channel = $('#channel').val();

            if ($.trim(channel) === '') {
                alert('You must select a channel');
                return false;
            }

            href = $(this).attr('action');
            var formdata = $(this).serialize() + '&' + $.param({'channel': channel});



            $.ajax({
                type: "POST",
                url: href,
                data: formdata,
                success: function (response) {
                    if (response.http_code === 400) {

                        $('.content').prepend("<div class='alert alert-danger'>Unable to save shipping rate</div>");


                    } else {
                        $('.content').prepend("<div class='alert alert-success'>Shipping rate saved successfully</div>");
                        $('.Search').click();

                    }
                }
            });
        });

        $(document).on("click", "#updateCourierRates", function () {

            var channel = $('#channel').val();

            if ($.trim(channel) === '') {
                alert('You must select a channel');
                return false;
            }

            $('.channel').val(channel);

            var formdata = $('#editCourierRateForm').serialize();

            $.ajax({
                type: "POST",
                url: '/admin/courier-rates/update',
                data: formdata,
                success: function (response) {
                    if (response.http_code === 400) {

                        $('.content').prepend("<div class='alert alert-danger'>Unable to save shipping rate</div>");


                    } else {
                        $('.content').prepend("<div class='alert alert-success'>Shipping rate saved successfully</div>");
                        $('.Search').click();
                    }
                }
            });
        });
    });

</script>
@endsection;
