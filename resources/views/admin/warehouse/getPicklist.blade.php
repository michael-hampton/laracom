
<!-- Main content -->
<section class="content">

    @include('layouts.errors-and-messages')

    <a class='btn btn-primary' href="{{route('admin.warehouse.generatePicklist', $picklist_ref)}}">Download Picking List</a>


    <div class="box">
        @if(!$items->isEmpty())

        <table class="table">
            <thead>
            <th class="col-md-2">SKU</th>
            <th class="col-md-2">Name</th>
            <th class="col-md-2">Quantity</th>
            <th class="col-md-2">Picklist Ref</th>
            <th class="col-md-2">Bin</th>
            <th class="col-md-2">Tote</th>
            <th class="col-md-2">Price</th>
            <th class="col-md-2">Status</th>
            <th class="col-md-2">Actions</th>
            </thead>
            <tbody>

                @foreach($items as $item)

                <tr>
                    <td>{{ $item->product_sku }}</td>
                    <td>
                        {{ $item->product_name }}

                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{$item->picklist_ref}}</td>
                    <td>A</td>
                    <td>{{$item->tote}}</td>
                    <td>{{ $item->product_price }}</td>
                    <td>{{ $item->status }}</td>

                    <td>
                        @if($status === 5):
                        
                        <input type="text" class="form-control" id="picked_quantity" line-id="{{ $item->id }}" value="{{ $item->quantity }}">
                        <button class="pick btn btn-primary btn-sm" order-id="{{ $item->order_id }}" line-id="{{ $item->id }}">
                            Pick
                        </button>

                        <button class="removeFromPicklist btn btn-danger btn-sm" order-id="{{ $item->order_id }}" line-id="{{ $item->id }}">
                            Remove From Picklist
                        </button>

                        @elseif($status === 16):
                        <button class="dispatch btn btn-primary btn-sm" order-id="{{ $item->order_id }}" line-id="{{ $item->id }}">
                            Dispatch
                        </button>

                        @elseif($status === 15):
                        <button class="pack btn btn-primary btn-sm" order-id="{{ $item->order_id }}" line-id="{{ $item->id }}">
                            Pack
                        </button>
                        @endif;
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif


</section>
<!-- /.content -->

<script type="text/javascript">
    $(document).ready(function () {

        $('.removeFromPicklist').off();
        $('.removeFromPicklist').on('click', function () {
            var $this = $(this);

            var orderId = $(this).attr('order-id');
            var lineId = $(this).attr('line-id');
            $('.content .alert-danger').remove();
            $('.content .alert-success').remove();

            $.ajax({
                type: "POST",
                url: '/admin/warehouse/removeOrderFromPicklist',
                data: {
                    orderId: orderId,
                    lineId: lineId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {

                    if (response.http_code === 400) {

                        $('.content').prepend("<div class='alert alert-danger'></div>");

                        $.each(response.FAILURES, function (lineId, val) {

                            $('.content .alert-danger').append("<p> Line Id: " + lineId + " " + val + "</p>");

                        });
                    } else {
                        $('.modal-body').prepend("<div class='alert alert-success'></div>");

                        $.each(response.SUCCESS, function (lineId, val) {

                            $('.modal-body .alert-success').append("<p>" + val + "</p>");

                        });

                        $this.parent().parent().remove();
                    }
                }
            });

            return false;
        });

        $(document).off('.pick');
        $(document).on("click", ".pick", function () {

            var $this = $(this);

            var orderId = $(this).attr('order-id');
            var lineId = $(this).attr('line-id');
            var picked_quantity = $('#picked_quantity[line-id='+lineId+']').val();
            $('.content .alert-danger').remove();
            $('.content .alert-success').remove();

            $.ajax({
                type: "POST",
                url: '/admin/warehouse/pickOrder',
                data: {
                    orderId: orderId,
                    picked_quantity:picked_quantity,
                    lineId: lineId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {

                    if (response.http_code === 400) {

                        $('.content').prepend("<div class='alert alert-danger'></div>");

                        $.each(response.FAILURES, function (lineId, val) {

                            $('.content .alert-danger').append("<p> Line Id: " + lineId + " " + val + "</p>");

                        });
                    } else {
                        $('.modal-body').prepend("<div class='alert alert-success'></div>");

                        $.each(response.SUCCESS, function (lineId, val) {

                            $('.modal-body .alert-success').append("<p>" + val + "</p>");

                        });

                        $this.replaceWith('<button class="pack" order-id="' + orderId + '" line-id="' + lineId + '">Pack</button>');
                    }
                }
            });
            return false;
        });

        $(document).off('.pack');
        $(document).on("click", ".pack", function () {

            var orderId = $(this).attr('order-id');
            var lineId = $(this).attr('line-id');
            var $this = $(this);
            $('.content .alert-danger').remove();
            $('.content .alert-success').remove();

            $.ajax({
                type: "POST",
                url: '/admin/warehouse/packOrder',
                data: {
                    orderId: orderId,
                    lineId: lineId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {

                    if (response.http_code === 400) {

                        $('.content').prepend("<div class='alert alert-danger'></div>");

                        $.each(response.FAILURES, function (lineId, val) {

                            $('.content .alert-danger').append("<p> Line Id: " + lineId + " " + val + "</p>");

                        });
                    } else {
                        $('.modal-body').prepend("<div class='alert alert-success'></div>");

                        $.each(response.SUCCESS, function (lineId, val) {

                            $('.modal-body .alert-success').append("<p>" + val + "</p>");

                        });

                        $this.replaceWith('<button class="dispatch" order-id="' + orderId + '" line-id="' + lineId + '">Dispatch</button>');
                    }
                }
            });
            return false;
        });

        $(document).off('.dispatch');
        $(document).on("click", ".dispatch", function () {

            var orderId = $(this).attr('order-id');
            var lineId = $(this).attr('line-id');
            var $this = $(this);
            $('.content .alert-danger').remove();
            $('.content .alert-success').remove();

            $.ajax({
                type: "POST",
                url: '/admin/warehouse/dispatchOrder',
                data: {
                    orderId: orderId,
                    lineId: lineId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.http_code === 400) {

                        $('.content').prepend("<div class='alert alert-danger'></div>");

                        $.each(response.FAILURES, function (lineId, val) {

                            $('.content .alert-danger').append("<p> Line Id: " + lineId + " " + val + "</p>");

                        });
                    } else {
                        $('.modal-body').prepend("<div class='alert alert-success'></div>");

                        $.each(response.SUCCESS, function (lineId, val) {

                            $('.modal-body .alert-success').append("<p>" + val + "</p>");

                        });

                        $this.remove();
                    }
                }
            });
            return false;
        });
    });
</script>

