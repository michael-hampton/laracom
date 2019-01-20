@extends('layouts.admin.app')

<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.css" type="text/css" rel="stylesheet">


@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    
    <form action="{{ route('admin.vouchers.destroy', $voucher->id) }}" method="post" class="form-horizontal">
        {{ csrf_field() }}
        <input type="hidden" name="_method" value="delete">
                              
        <div class="btn-group">
            <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Delete</button>
        </div>
    </form>
                            
    <a href="{{ route('admin.voucher-codes.batch', $voucher->id) }}" class="btn btn-default btn-sm">Show Codes</a>
    <a href="{{ route('admin.voucher-codes.add', $voucher->id) }}" class="btn btn-default btn-sm">Add Codes</a>
            
    <div class="box">
        <form action="{{ route('admin.vouchers.update', $voucher->id) }}" method="post" class="form" enctype="multipart/form-data">
            <div class="box-body">
                {{ csrf_field() }}
                
                <input type="hidden" name="_method" value="put">
                <input type="hidden" name="channel" id="channel" value="{{ $selectedChannel }}">
                <input type="hidden" name="scope_value" id="scope_value" value="{{ $voucher->scope_value ?: old('scope_value') }}">

                <div class="form-group">
                    <label for="amount_type">Amount Type </label>
                    <select name="amount_type" id="amount_type" class="form-control">
                        @if($voucher->amount_type == 'fixed')
                        <option selected="selected" value="fixed">Fixed</option>
                        <option value="percentage">Percentage</option>
                        @else
                        <option selected="selected" value="percentage">Percentage</option>
                        <option value="fixed">Fixed</option>
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">Value </label>
                    <input type="text" name="amount" id="amount" placeholder="Value" class="form-control" value="{{ $voucher->amount ?: old('amount') }}">
                </div>


                <div class="form-group">
                    <label for="expiry_date">Start Date </label>
                    <input type="text" name="start_date" id="start_date" placeholder="Start Date" class="form-control" value="{{ date('m-d-Y', strtotime($voucher->start_date)) ?: old('start_date') }}">
                </div>

                <div class="form-group">
                    <label for="expiry_date">Expiry Date </label>
                    <input type="text" name="expiry_date" id="expiry_date" placeholder="Expiry Date" class="form-control" value="{{ date('m-d-Y', strtotime($voucher->expiry_date)) ?: old('expiry_date') }}">
                </div>

                @if(!empty($scopes))
                <div class="form-group">
                    <label for="channel">Scope</label>
                    <select name="scope_type" id="scope_type" class="form-control select2 scope">
                        <option value="order">Order</option>
                        @foreach($scopes as $scope)
                        <option @if($voucher->scope_type == $scope) selected="selected" @endif value="{{ $scope }}">{{ $scope }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if(!empty($products))
                <div class="form-group products scope-type" style="display:none;">
                    <label for="product">Product</label>
                    <select name="product" id="product" class="form-control select2 scope-select">
                        <option value="">--Select--</option>
                        @foreach($products as $product)
                        <option @if(old('product') == $product->id) selected="selected" @endif value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if(!$brands->isEmpty())
                <div class="form-group brands scope-type" style="display:none;">
                    <label for="brand">Brand</label>
                    <select name="brand" id="brand" class="form-control select2 scope-select">
                        <option value="">--Select--</option>
                        @foreach($brands as $brand)
                        <option @if(old('brand') == $brand->id) selected="selected" @endif value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if(!$categories->isEmpty())
                <div class="form-group categories scope-type" style="display:none;">
                    <label for="category">Category</label>
                    <select name="category" id="category" class="form-control select2 scope-select">
                        <option value="">--Select--</option>
                        @foreach($categories as $category)
                        <option @if(old('category') == $category->id) selected="selected" @endif value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="form-group">
                    @include('admin.shared.status-select', ['status' => $voucher->status])
                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <div class="btn-group">
                    <a href="{{ route('admin.vouchers.index') }}" class="btn btn-default">Back</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
    <!-- /.box -->

</section>
<!-- /.content -->
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript">
$(document).ready(function () {

    $('.scope-select').on('change', function () {
        $('#scope_value').val($(this).val());
    });

    $('.scope').on('change', function () {

        $('.scope-type').hide();

        var type = $(this).val();

        switch (type) {
            case 'Product':

                $('.products').show();
                break;

            case 'Brand':
                $('.brands').show();
                break;

            case 'Category':
                $('.categories').show();
                break;
        }
    });

    $('#start_date').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        format: 'mm/dd/yyyy',
        forceParse: false,
        calendarWeeks: false,
        autoclose: true,
        startDate: new Date()
    });

    $('#expiry_date').datepicker({
        todayBtn: "linked",
        format: 'mm/dd/yyyy',
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: false,
        autoclose: true
    });

});

</script>
@endsection
