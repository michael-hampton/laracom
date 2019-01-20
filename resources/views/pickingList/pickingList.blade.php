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

    <section class="row">
        <div class="col-md-12">
            <h2>Details</h2>
                        
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
                @foreach($items as $item)
                    <tr>
                        <td>{{$item->product_sku}}</td>
                        <td>{{$item->product_name}}</td>
                        <td>{{$item->product_description}}</td>
                        <td>{{$item->quantity}}</td>
                        <td>{{$item->product_weight}}</td>
                        <td>abc</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
</body>
</html>p
