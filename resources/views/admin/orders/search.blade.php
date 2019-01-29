       <div class="box">
       
       <div class="box-body">
            @if($orders)
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <td class="col-md-1">#</td>
                        <td class="col-md-3">Date</td>
                        <td class="col-md-3">Customer</td>
                        <td class="col-md-2">Courier</td>
                        <td class="col-md-2">Total</td>
                        <td class="col-md-1">Status</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                    <tr style="background-color: {{ $order->is_priority == 1 ? '#fffb9d' : '' }}">
                        <td>{{$order->id}}</td>
                        <td><a title="Show order" href="{{ route('admin.orders.show', $order->id) }}">{{ date('M d, Y h:i a', strtotime($order->created_at)) }}</a></td>
                        <td>{{$order->customer->name}}</td>
                        <td>{{ $order->courier->name }}</td>
                        <td>
                            <span class="label @if($order->total != $order->total_paid) label-danger @else label-success @endif">Php {{ $order->total }}</span>
                        </td>
                        <td><p class="text-center" style="color: #ffffff; background-color: {{ $order->status->color }}">{{ $order->status->name }}</p></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif;
        </div>
    </div>
