
<!-- Main content -->
<section class="content">

    @include('layouts.errors-and-messages')


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
                        @if($item->status === 5):
                        <button class="pick" order-id="{{ $item->order_id }}" line-id="{{ $item->id }}">
                            Pick
                        </button>

                        @elseif($item->status === 16):
                        <button class="dispatch" order-id="{{ $item->order_id }}" line-id="{{ $item->id }}">
                            Dispatch
                        </button>

                        @elseif($item->status === 15):
                        <button class="pack" order-id="{{ $item->order_id }}" line-id="{{ $item->id }}">
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

        $('.pick').on('click', function () {

            var orderId = $(this).attr('order-id');
            var lineId = $(this).attr('line-id');

            $.ajax({
                type: "POST",
                url: '/admin/warehouse/pickOrder',
                data: {
                    orderId: orderId,
                    lineId: lineId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (msg) {
                    alert(msg);
                }
            });
            return false;
        });

        $('.pack').on('click', function () {

            var orderId = $(this).attr('order-id');
            var lineId = $(this).attr('line-id');

            $.ajax({
                type: "POST",
                url: '/admin/warehouse/packOrder',
                data: {
                    orderId: orderId,
                    lineId: lineId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (msg) {
                    alert(msg);
                }
            });
            return false;
        });

        $('.dispatch').on('click', function () {

            var orderId = $(this).attr('order-id');
            var lineId = $(this).attr('line-id');

            $.ajax({
                type: "POST",
                url: '/admin/warehouse/dispatchOrder',
                data: {
                    orderId: orderId,
                    lineId: lineId,
                    _token: '{{ csrf_token() }}'
                },
                success: function (msg) {
                    alert(msg);
                }
            });
            return false;
        });
    });
</script>

