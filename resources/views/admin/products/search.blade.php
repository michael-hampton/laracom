      <div class="box">
	            <div class="box-body">
	                <h2>Products</h2>
	                @if($products)
	                @include('admin.shared.products')
	                {{ $products->links() }}
	                @endif
	
	            </div>
	
	
	
	        </div>
