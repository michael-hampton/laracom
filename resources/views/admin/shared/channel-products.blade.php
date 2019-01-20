<style>
    .product-box {
        padding: 0;
        border: 1px solid #e7eaec;
    }
    .product-box:hover,
    .product-box.active {
        border: 1px solid transparent;
        -webkit-box-shadow: 0 3px 7px 0 #a8a8a8;
        -moz-box-shadow: 0 3px 7px 0 #a8a8a8;
        box-shadow: 0 3px 7px 0 #a8a8a8;
    }
    .product-imitation {
        text-align: center;
        padding: 90px 0;
        background-color: #f8f8f9;
        color: #bebec3;
        font-weight: 600;
    }
    .cart-product-imitation {
        text-align: center;
        padding-top: 30px;
        height: 80px;
        width: 80px;
        background-color: #f8f8f9;
    }
    .product-imitation.xl {
        padding: 120px 0;
    }
    .product-desc {
        padding: 20px;
        position: relative;
    }

    .product-name {
        font-size: 16px;
        font-weight: 600;
        color: #676a6c;
        display: block;
        margin: 2px 0 5px 0;
    }
    .product-name:hover,
    .product-name:focus {
        color: #1ab394;
    }
    .product-price {
        font-size: 14px;
        font-weight: 600;
        color: #ffffff;
        background-color: #1ab394;
        padding: 6px 12px;
        position: absolute;
        top: -32px;
        right: 0;
    }
    .product-detail .ibox-content {
        padding: 30px 30px 50px 30px;
    }
    .image-imitation {
        background-color: #f8f8f9;
        text-align: center;
        padding: 200px 0;
    }
    .product-main-price small {
        font-size: 10px;
    }
    .product-images {
        margin: 0 20px;
    }
    
      .btn-group {
        display:none;
    }
</style>

<div class="col-lg-12">
    @if(isset($products))
    @foreach ($products as $product)
    <div class="col-md-3 product-div" style="margin-top:10px;">
        <div class="">
            <div class="product-box">

                <form action="{{ route('admin.products.destroy', $product->id) }}" method="post" class="form-horizontal">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="delete">
                    <div class="btn-group" style="margin-top:10px;">
                        <a style="margin-left: 10px;" href="{{ route('admin.channel-prices.edit', $product->id) }}" class="btn btn-primary btn-xs Edit"><i class="fa fa-edit"></i> Edit</a>
                        <button style="margin-left: 10px;" onclick="return confirm('Are you sure?')" type="submit" class="btn btn-danger btn-xs"><i class="fa fa-times"></i> Delete</button>
                    </div>
                </form>

                <div class="product-imitation">
                    @if(isset($product->cover))
                    <img src="{{ asset("storage/$product->cover") }}" alt="" class="img-responsive">
                    @endif
                    @include('layouts.status', ['status' => $product->status])
                </div>
                <div class="product-desc" style="max-height:200px">
                    <span class="product-price">
                        £{{ $product->price }}
                    </span>
                    <small class="text-muted"> {{$product->category}}</small>
                    <a href="{{ route('admin.products.show', $product->id) }}" class="product-name"> {{ mb_substr($product->name, 0, 20) }}</a>

                   <div class="small m-t-xs">
                        Brand: {{$product->brand_name}}
                    </div>

                    <div class="small m-t-xs">
                        Sku: {{$product->sku}}
                    </div>

                    <div class="small m-t-xs">
                        {{ mb_substr($product->description, 0, 30) }}
                    </div>

                    <div class="small m-t-xs">
                        <!--                    {{$product->brand}}-->
                    </div>



                    <div class="small m-t-xs">
                        Quantity Available: {{ $product->quantity }}
                    </div>


                    <div class="m-t text-righ">

                        <a href="#" class="btn btn-xs btn-outline btn-primary">Info <i class="fa fa-long-arrow-right"></i> </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach;

    @endif;
</div>



