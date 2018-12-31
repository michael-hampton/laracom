@extends('layouts.admin.app')

@section('content')
    <!-- Main content -->
    <section class="content">
        @include('layouts.errors-and-messages')
        <div class="box">
            <form action="{{ route('admin.refunds.update', $refund->id) }}" method="post" class="form" enctype="multipart/form-data">
                <div class="box-body">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="put">
                    <input type="hidden" name="order_id" id="order_id"  value="1">
                    
                    <div class="form-group">
                        <label for="alias">Quantity <span class="text-danger">*</span></label>
                        <input type="text" name="quantity" id="quantity" placeholder="Quantity" class="form-control" value="{{ $refund->quantity ?: old('quantity') }}">
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount<span class="text-danger">*</span></label>
                        <input type="text" name="amount" id="amount" placeholder="Amount" class="form-control" value="{{ $refund->amount ?: old('amount') }}">
                    </div>
                    <div class="form-group">
                        <label for="date_refunded">Date Refunded</label>
                        <input type="text" name="date_refunded" id="date_refunded" placeholder="Date Refunded" class="form-control" value="{{ $refund->date_refunded ?: old('date_refunded') }}">
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">--Select Status--</option>
                            <option value="2">Approved</option>
                            <option value="3">Rejected</option>
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

    </section>
    <!-- /.content -->
@endsection
@section('js')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#province_id').change(function () {

                var provinceId = $(this).val();

                $.ajax({
                    url: '/api/v1/country/169/province/' + provinceId + '/city',
                    contentType: 'json',
                    success: function (data) {

                        var html = '<label for="city_id">City </label>';
                            html += '<select name="city_id" id="city_id" class="form-control">';
                            $(data.data).each(function (idx, v) {
                                html += '<option value="'+ v.id+'">'+ v.name +'</option>';
                            });
                            html += '</select>';

                        $('#cities').html(html).show();
                    },
                    errors: function (data) {
                        console.log(data);
                    }
                });
            });
        });
    </script>
@endsection
