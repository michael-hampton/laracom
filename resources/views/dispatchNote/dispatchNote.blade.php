
<!doctype html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Order Invoice</title>
        <link rel="stylesheet" href="{{ asset('css/admin.min.css') }}">
        <style type="text/css">
            table { border-collapse: collapse;}
        </style>
    </head>
    <body>
        <section class="row col-lg-12">
            <div class="pull-left col-lg-6">
                Deliver to: <strong>{{ $address->alias }} <br /></strong>
                {{ $address->address_1 }} {{ $address->address_2 }} <br />
                {{ $address->city }} {{ $address->province }} <br />
                {{ $address->country }} {{ $address->zip }}
            </div>
            <div class="pull-right col-lg-6">
                <img style="width:50px;" src="/storage/{{ $channel->cover }}">
                From: {{$channel->name}}<br>
                Dispatch Date: {{ date('d-m-Y H:i') }}<br>
                Dispatched By: Michael Hampton

            </div>

        </section>
        <section class="row">
            <div class="col-md-12">
                <h2>Details</h2>

                <small>Customer Ref: <strong>{{ $order->customer_ref }}</strong></small>

                <table class="table table-striped" width="100%" border="0" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Quantity Delivered</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        foreach ($items as $item)
                        {

//                            if ($item->status !== $allowed_status) {
//
//                                continue;
//                            }
                            ?>

                            <tr>
                                <td>{{$item->product_sku}}</td>
                                <td>{{$item->product_name}}</td>
                                <td>{{$item->product_description}}</td>
                                <td>{{$item->quantity}}</td>
                                <td></td>
                            </tr>

                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        @if(!empty($terms->description))
        <section class="row col-lg-12">
            <h2>Terms and Conditions</h2>
            {{$terms->description }}
        </section>
        @endif
    </body>
</html>
