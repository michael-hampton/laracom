<style>
    input[type=text], select {
        width:100% !important;
    }
</style> 

<div class="modal-dialog">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title">Add New Voucher</h4>
        </div>

        <div class="modal-body no-padding">
            <!-- Main content -->
            <form id="NewVoucherForm" action="{{ route('admin.vouchers.store') }}" method="post" class="form" enctype="multipart/form-data">
                <div class="box-body">
                    {{ csrf_field() }}
                    <input type="hidden" id="uploadedProductCodes" name="uploadedProductCodes">

                    <input type="hidden" name="scope_value" id="scope_value">

                    <div class="form-inline pull-left" style='margin-bottom:12px;'>

                        @if(empty($selectedChannel) && !$channels->isEmpty())
                        <div class="form-group col-lg-5">
                            <label for="channel">Channel</label>
                            <select name="channel" id="channel" class="form-control select2">
                                <option value="">--Select--</option>
                                @foreach($channels as $channel)
                                <option @if(old('channel') == $channel->id) selected="selected" @endif value="{{ $channel->id }}">{{ $channel->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="channel" id="channel" value="{{ $selectedChannel }}">
                        @endif

                        <div class="form-group col-lg-6">
                            <label for="name">Description<span class="text-danger">*</span></label>
                            <input type="text" name="description" id="description" placeholder="Description" class="form-control" value="{{ old('description') }}">
                        </div>
                    </div>


                    <div class="form-inline pull-left" style='margin-bottom:12px;'>
                        <div class="form-group col-lg-3" style="margin-right: 4px;">
                            <label for="alias">Qty to create<span class="text-danger">*</span></label>
                            <input type="text" name="quantity" id="quantity" placeholder="Quantity" class="form-control" value="{{ old('quantity') }}">
                        </div>
                        <div class="form-group col-lg-4" style="margin-right: 4px;">
                            <label for="address_1">Value <span class="text-danger">*</span></label>
                            <input type="text" name="amount" id="amount" placeholder="Value" class="form-control" value="{{ old('amount') }}">
                        </div>

                        <div class="form-group col-lg-4" style="margin-right: 4px;">
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

                        <div class="form-group col-lg-4" style="margin-right: 10px;">
                            <label for="address_2">Start Date </label>
                            <input type="text" name="start_date" id="start_date" placeholder="Start Date" class="form-control" value="{{ old('start_date') }}">
                        </div>

                        <div class="form-group col-lg-4" style="margin-right: 10px;">
                            <label for="address_2">Expiry Date </label>
                            <input type="text" name="expiry_date" id="expiry_date" placeholder="Expiry Date" class="form-control" value="{{ old('expiry_date') }}">
                        </div>
                    </div>

                    <div class="form-inline pull-left col-lg-12 no-padding" style='margin-bottom:12px;'>

                        @if(!empty($scopes))
                        <div class="form-group col-lg-5 no-padding" style='margin-right:10px;'>
                            <label for="channel">Scope</label>
                            <select name="scope_type" id="scope_type" class="form-control select2 scope">
                                <option value="order">Order</option>
                                @foreach($scopes as $scope)
                                <option @if(old('scope_type') == $scope) selected="selected" @endif value="{{ $scope }}">{{ $scope }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif



                        <div class="form-group col-lg-5">
                            <label for="status">Status </label>
                            <select name="status" id="status" class="form-control">
                                <option value="0">Disable</option>
                                <option value="1">Enable</option>
                            </select>
                        </div>
                    </div>

                    <ul class="form-inline uploaded-products">

                    </ul>

                    <div class="form-inline">

                        <div class="form-group">
                            <label for="cover">Upload Voucher Codes </label>
                            <input type="file" name="csv_file" id="csv_file" class="form-control">


                        </div>

                        @if(!empty($products))
                        <div class="form-group products scope-type col-lg-12" style="display:none;">

                            <div class="row col-lg-12">
                                <input class="pull-lrft" type="file" id="fileUpload" />
                                <input type="button" id="uploadProducts" value="Add Product Codes" class="btn btn-primary pull-right" />
                            </div>



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
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary saveNewVoucher">Save changes</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('.scope-select').on('change', function () {
        $('#scope_value').val($(this).val());
    });

    $("#uploadProducts").on("click", function () {
        var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.csv|.txt)$/;
        if (regex.test($("#fileUpload").val().toLowerCase())) {
            if (typeof (FileReader) != "undefined") {

                var reader = new FileReader();
                reader.onload = function (e) {
                    var products = new Array();
                    var rows = e.target.result.split("\r\n");

                    var firstLine = null;

                    for (var i = 0; i < rows.length; i++) {

                        if (firstLine === null) {
                            firstLine = true;
                            continue;
                        }

                        var product = rows[i].split(",")[0];
                        products.push($.trim(product));

                        $('.uploaded-products').append('<li product-code="' + product + '" style="margin:8px;" class="uploaded-product label label-success">' + product + '<a class="removeProductCode">X</a></li>');
                    }
                    var products = products.join(',');
                    $('#uploadedProductCodes').val(products);
                    console.log(products);
                }
                reader.readAsText($("#fileUpload")[0].files[0]);
            } else {
                alert("This browser does not support HTML5.");
            }
        } else {
            alert("Please upload a valid CSV file.");
        }
    });

    $('.saveNewVoucher').on('click', function (e) {

        e.preventDefault();
        $('.modal-body .alert-danger').remove();
        $('.saveNewVoucher').prop('disabled', true);

        //var formdata = $('#NewVoucherForm').serialize();
        var formdata = new FormData($('#NewVoucherForm')[0]);
        var href = $('#NewVoucherForm').attr('action');

        $.ajax({
            type: "POST",
            url: href,
            processData: false,
            contentType: false,
            data: formdata,
            success: function (response) {
                if (response.http_code == 400) {
                    $('.modal-body').prepend("<div class='alert alert-danger'></div>");
                    $.each(response.errors, function (key, value) {
                        $('.modal-body .alert-danger').append("<p>" + value + "</p>");
                    });
                } else {

                    $('.modal-body').prepend("<div class='alert alert-success'>Voucher has been created successfully</div>");

                    $('.modal-body .alert-success').append('<a href="' + response.filename + '">Download</a>');

                    if (response.import_result != undefined) {

                        $('.modal-body .alert-success').append('<p>' + response.import_result.added + ' voucher codes were imported</p>');
                        if (response.import_result.duplicates != undefined) {

                            $('.modal-body .alert-success').append('<p>The following voucher codes were duplicates and could not be added</p><ul>');
                            $.each(response.import_result.duplicates, function (key, value) {

                                $('.modal-body .alert-success').append('<li>' + value + '</li>');
                            });

                            $('.modal-body .alert-success').append('</ul>');
                        }
                    }

                    if (response.product_result != undefined) {

                        if (response.product_result.not_found != undefined) {
                            $('.modal-body .alert-success').append('<p>The following product codes could not be found</p><ul>');

                            $.each(response.product_result.not_found, function (key, value) {

                                $('.modal-body .alert-success').append('<li>' + value + '</li>');
                            });
                        }

                        if (response.product_result.product_ids != undefined) {

                            $('.modal-body .alert-success').append('<p>The following product codes have been added</p><ul>');

                            $.each(response.product_result.product_ids, function (key, value) {

                                $('.modal-body .alert-success').append('<li>' + value + '</li>');
                            });
                        }
                    }



                }

                $('.saveNewVoucher').prop('disabled', false);
            }
        });
    });

    $('#csv_file').on('change', function () {
        $('#quantity').prop('disabled', true);
    });

    $(document).off('.removeProductCode');
    $(document).on("click", ".removeProductCode", function () {

        $(this).parent().remove();

        var products = new Array();

        $('.uploaded-product').each(function () {
            products.push($.trim($(this).attr('product-code')));
        });

        $('#uploadedProductCodes').val(products.join(','));




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
