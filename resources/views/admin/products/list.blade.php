@extends('layouts.admin.app')

@section('content')

@include('layouts.errors-and-messages')
<!-- Main content -->
<section class="content">
    <div class="col-lg-3">
        <div class="box">
            <div class="box-body">
                <h2>Products</h2>

                <!-- search form -->
                <div class="col-lg-12">
                    <form action="{{ route('admin.products.search') }}" method="post" id="admin-search">
                        <input type="hidden" name="page" id="page" value="1">
                        {{ csrf_field() }}

                        <div style="margin-bottom: 10px;">
                            <label for="category">Category </label>
                            <select name="category" id="category" class="form-control">
                                <option value="">--Select--</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <label for="country">Brand </label>
                            <select name="brand" id="brand" class="form-control">
                                <option value="">--Select--</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 10px;">
                            <label for="status">Status </label>
                            <select name="product_status" id="status" class="form-control">
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <label for="product_name">Product Name</label>
                            <input name="product_name" id="product_name" class="form-control">
                        </div>

                        <button style="margin-top:26px;" type="button" class="btn btn-primary Search">Search</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-9 search-results">
        
    </div>

</section>
<!-- /.content -->
@endsection

<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Edit Product</h4>
            </div>

            <div class="modal-body">

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary UpdateProduct">Save changes</button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script type="text/javascript">
    $(document).ready(function () {
    
        $(document).on('click', '.Edit', function (e) {
            e.preventDefault();
            var href = $(this).attr("href");
                        
            $.ajax({
                type: "GET",
                url: href,
                success: function (response) {
                   $('#myModal').find('.modal-body').html(response);
                   $('#myModal').modal('show');
                }
            });
        });
    
        loadPagination();
         
         $('.Search').on('click', function (e) {
            href = $('#admin-search').attr('action');
            $('.search-results').html('<img class="loader" src="{{url(' / images / loading.gif')}}" alt="Loading"/>');
            $('.Search').text('Loading...');
            $('.Search').prop('disabled', true);
            var formdata = $('#admin-search').serialize();
            $.ajax({
                type: "POST",
                url: href,
                data: formdata,
                success: function (response) {
                    $('.Search').html('<i class="fa fa-search"></i> Search');
                    $('.Search').prop('disabled', false);
                    $('.search-results').html(response);
                }
            });
        });
        $('.Search').click();

        $(document).on('mouseenter', '.product-div', function () {

            $(this).find(".btn-group").show();
        }).on('mouseleave', '.product-div', function () {
            $(this).find(".btn-group").hide();
        });
    });
</script>
@endsection;
