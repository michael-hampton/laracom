@extends('layouts.admin.app')

@section('content')
    <!-- Main content -->
    <section class="content">
        @include('layouts.errors-and-messages')
        <div class="box">
            <form action="{{ route('admin.returns.update', $refund->id) }}" method="post" class="form" enctype="multipart/form-data">
                <div class="box-body">
                    {{ csrf_field() }}
                    
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
