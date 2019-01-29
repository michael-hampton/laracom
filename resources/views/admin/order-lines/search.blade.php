<div class="box">
    <div class="box-body">
        @if(!$items->isEmpty())
        <div class="box-body">
            <h4> <i class="fa fa-gift"></i> Items</h4>

            <a href="#" class="uncheck">Uncheck</a>

            <table class="table table-striped table-hover">
                <thead>
                <th class="col-md-2">Order Id</th>
                <th class="col-md-2">Channel</th>
                <th class="col-md-2">Order Date</th>
                <th class="col-md-2">Customer Name</th>
                <th class="col-md-2">Name</th>
                <th class="col-md-2">Quantity</th>

                <th class="col-md-2">Actions</th>
                </thead>

                <tbody>

                    <?php
                    $customerRef = '';
                    foreach ($items as $item) {

//                        if(!isset($orders[$item->order_id])) {
//                            
//                            continue;
//                        }

                        $arrOrder = $orders[$item->order_id];

                        if ($item->status === 14) {
                            $color = $arrOrder->is_priority == 1 ? 'table-warning' : '';
                        } elseif (strtotime($item->created_at) < strtotime('-30 days')) {
                            $color = 'table-danger';
                        } elseif (strtotime($item->created_at) < strtotime('-15 days')) {
                            $color = 'table-warning';
                        } else {
                            $color = 'table-info';
                        }
                        ?>

                        <tr class="{{ $color }}">
                            @if($customerRef !== $arrOrder->customer_ref)
                            <td>{{$arrOrder->id}}</td>
                            <td>{{$arrOrder->channel->name}}</td>
                            <td>{{$arrOrder->created_at}}</td>
                            <td>{{$arrOrder->customer->name}}</td>
                            @else
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            @endif
                            <td>
                                {{ $item->product_name }}

                            </td>
                            <?php
                            $quantityAvailiable = $products[$item->product_id]['quantity'] - $products[$item->product_id]['reserved_stock'];
                            $reservedStock = $products[$item->product_id]['reserved_stock'];
                            $checked = $item->status === 14 || $quantityAvailiable > 0 ? 'checked="checked"' : '';
                            $disabled = $item->status !== 14 && $quantityAvailiable == 0 ? 'disabled="disabled"' : '';
                            ?>

                            <td>{{ $item->quantity }}
                                <br>Free Stock {{$quantityAvailiable}}
                                <br>Reserved Stock {{$reservedStock}}
                            </td>

                            <td>

                                <input type="checkbox" {{ $checked }} {{ $disabled }} class="cb" name="services[]" order-id="{{ $item->order_id }}" value="{{ $item->id }}">
                                @if($item->status === 11)
                                <i email='{{$arrOrder->customer->email}}'order-id="{{$item->order_id}}" class="fa fa-envelope-open-o open-message" aria-hidden="true"></i>
                                @endif
                            </td>
                        </tr>
                        <?php
                        $customerRef = $arrOrder['customer_ref'];
                    }
                    ?>
                </tbody>
            </table>
        </div>
        @endif
    </div>



</div>
