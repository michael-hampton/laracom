<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Picking List</title>
    <link rel="stylesheet" href="{{ asset('css/admin.min.css') }}">
    <style type="text/css">
        table { border-collapse: collapse;}
    </style>
</head>
<body>
    <section class="row col-lg-12">
        <div class="pull-right col-lg-6">
           <img style="width:50px;" src="/storage/{{ $channel->cover }}">
            From: {{$channel->name}}<br>
            Date: {{ date('d-m-Y H:i') }}<br>
            
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
                        <th>Quantity</th>
                        <th>Weight</th>
                        <th>Location</th>
                        
                    </tr>
                </thead>
                <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{$product->sku}}</td>
                        <td>{{$product->name}}</td>
                        <td>{{$product->description}}</td>
                        <td>{{$product->pivot->quantity}}</td>
                        <td>{{$product->weight}}</td>
                        <td>abc</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
</body>
</html>p
