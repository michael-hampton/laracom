<div class="box">
    <div class="box-body">
        <h2>Products</h2>
        @if($products)
        @include('admin.shared.products')
        @endif

    </div>
</div>

<div class="box-footer col-lg-12">
    {{ $products->links() }}
</div>
