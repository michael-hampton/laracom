<style>

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
                        <a style="margin-left: 10px;" href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary Edit"><i class="fa fa-edit"></i> Edit</a>
                        <button style="margin-left: 10px;" onclick="return confirm('Are you sure?')" type="submit" class="btn btn-danger"><i class="fa fa-times"></i> Delete</button>
                    </div>
                </form>

                <div class="product-imitation">
                    @if(isset($product->cover))
                    <img src="{{ asset($product->cover) }}" alt="" class="img-responsive">
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



