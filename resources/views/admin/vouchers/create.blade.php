
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.css" type="text/css" rel="stylesheet">

<style>
    input[type=text] {
        width:100% !important;
    }
</style>

<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <div class="box">
        <form id="NewVoucherForm" action="{{ route('admin.vouchers.store') }}" method="post" class="form" enctype="multipart/form-data">
            <div class="box-body">
                {{ csrf_field() }}

                @if(empty($selectedChannel) && !$channels->isEmpty())
                <div class="form-group">
                    <label for="channel">Channel</label>
                    <select name="channel" id="channel" class="form-control select2">
                        <option value="">--Select--</option>
                        @foreach($channels as $channel)
                        <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>
                @else;
                <input type="hidden" name="channel" id="channel" value="{{ $selectedChannel }}">
                @endif;

                <input type="hidden" name="scope_value" id="scope_value">



                <div class="form-inline pull-left" style='margin-bottom:12px;'>
                    <div class="form-group col-lg-3" style="margin-right: 4px;">
                        <label for="alias">Qty to create<span class="text-danger">*</span></label>
                        <input type="text" name="quantity" id="alias" placeholder="Quantity" class="form-control" value="{{ old('quantity') }}">
                    </div>
                    <div class="form-group col-lg-3" style="margin-right: 4px;">
                        <label for="address_1">Value <span class="text-danger">*</span></label>
                        <input type="text" name="amount" id="amount" placeholder="Value" class="form-control" value="{{ old('amount') }}">
                    </div>

                    <div class="form-group col-lg-3" style="margin-right: 4px;">
                        <label for="alias">Use Count<span class="text-danger">*</span></label>
                        <input type="text" name="use_count" id="alias" placeholder="Use Count" class="form-control" value="{{ old('use_count') }}">
                    </div>
                </div>

                <div class="form-inline pull-left" style='margin-bottom:12px;'>

                    <div class="form-group col-lg-3" style="margin-right: 10px;">
                        <label for="amount type">Amount Type </label>
                        <select name="amount_type" id="amount_type" class="form-control">
                            <option value="fixed">Fixed</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>

                    <div class="form-group col-lg-3" style="margin-right: 10px;">
                        <label for="address_2">Start Date </label>
                        <input type="text" name="start_date" id="start_date" placeholder="Start Date" class="form-control" value="{{ old('start_date') }}">
                    </div>

                    <div class="form-group col-lg-3" style="margin-right: 10px;">
                        <label for="address_2">Expiry Date </label>
                        <input type="text" name="expiry_date" id="expiry_date" placeholder="Expiry Date" class="form-control" value="{{ old('expiry_date') }}">
                    </div>
                </div>

                <div class="form-inline pull-left" style='margin-bottom:12px;'>

                    @if(!empty($scopes))
                    <div class="form-group col-lg-6" style='margin-right:10px;'>
                        <label for="channel">Scope</label>
                        <select name="scope_type" id="scope_type" class="form-control select2 scope">
                            <option value="order">Order</option>
                            @foreach($scopes as $scope)
                            <option @if(old('scope_type') == $scope) selected="selected" @endif value="{{ $scope }}">{{ $scope }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif



                    <div class="form-group col-lg-6">
                        <label for="status">Status </label>
                        <select name="status" id="status" class="form-control">
                            <option value="0">Disable</option>
                            <option value="1">Enable</option>
                        </select>
                    </div>
                </div>
                <div class="form-inline">
                
                     <div class="form-group">
                         <label for="cover">Cover </label>
                         <input type="file" name="csv_file" id="csv_file" class="form-control">
                     </div>

                    @if(!empty($products))
                    <div class="form-group products scope-type col-lg-6" style="display:none;">
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
                    <div class="form-group brands scope-type col-lg-6" style="display:none;">
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
                    <div class="form-group categories scope-type col-lg-6" style="display:none;">
                        <label for="category">Category</label>
                        <select name="category" id="category" class="form-control select2 scope-select">
                            <option value="">--Select--</option>
                            @foreach($categories as $category)
                            <option @if(old('category') == $category->id) selected="selected" @endif value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

            </div>
    </div>
</form>
</div>
<!-- /.box -->

</section>

<script type="text/javascript">
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
        forceParse: false,
        calendarWeeks: false,
        autoclose: true,
        startDate: new Date()
    });

    $('#expiry_date').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: false,
        autoclose: true
    });
</script>
